<?php

namespace App\Agent;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use RuntimeException;

class LlmClient
{
    private Closure $onFailover;

    private ?string $lastProvider = null;

    /**
     * @param array<string, array{base_url: string, model: string, api_key: ?string, context_window_tokens?: int, history_compress_ratio?: float, token_char_estimate?: float}> $providers
     * @param array<int, string> $order
     * @param array{attempts_per_provider?: int, backoff_seconds?: array<int, int>, rounds?: int, round_backoff_seconds?: int} $retry
     */
    public function __construct(
        private array $providers,
        private array $order,
        ?Closure $onFailover = null,
        private array $retry = [],
    ) {
        $this->onFailover = $onFailover ?? fn (string $message) => null;
    }

    public function activeProvider(): ?string
    {
        return $this->lastProvider ?? $this->usableProviders()[0] ?? null;
    }

    public function contextCharBudget(): ?int
    {
        $provider = $this->activeProvider();

        if ($provider === null || empty($this->providers[$provider]['context_window_tokens'])) {
            return null;
        }

        $tokens = (int) $this->providers[$provider]['context_window_tokens'];
        $ratio = (float) ($this->providers[$provider]['history_compress_ratio'] ?? 0.75);
        $charsPerToken = (float) ($this->providers[$provider]['token_char_estimate'] ?? 4.0);

        return max(1, (int) floor($tokens * $charsPerToken * $ratio));
    }

    /**
     * Send a chat completion request. Transient failures (5xx, 429, network)
     * are retried with backoff; client errors skip straight to the next
     * provider. When every provider fails, wait and sweep them all again,
     * so a long run survives outages instead of dying on the first bad hour.
     *
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array<string, mixed>> $tools
     * @return array<string, mixed> The assistant message.
     */
    public function chat(array $messages, array $tools): array
    {
        $usable = $this->usableProviders();

        if ($usable === []) {
            throw new RuntimeException('No LLM provider has an API key configured.');
        }

        $rounds = max(1, (int) ($this->retry['rounds'] ?? 1));
        $attempts = max(1, (int) ($this->retry['attempts_per_provider'] ?? 1));
        $backoffs = $this->retry['backoff_seconds'] ?? [5, 15, 45];
        $roundBackoff = (int) ($this->retry['round_backoff_seconds'] ?? 60);

        $lastError = null;

        for ($round = 1; $round <= $rounds; $round++) {
            foreach ($usable as $name) {
                for ($attempt = 1; $attempt <= $attempts; $attempt++) {
                    try {
                        return $this->request($name, $this->providers[$name], $messages, $tools);
                    } catch (LlmRequestException $e) {
                        $lastError = $e;

                        if (! $e->retryable) {
                            ($this->onFailover)("Provider [{$name}] rejected the request ({$e->getMessage()}); moving to the next provider.");

                            continue 2;
                        }

                        ($this->onFailover)("Provider [{$name}] attempt {$attempt}/{$attempts} failed ({$e->getMessage()}).");

                        if ($attempt < $attempts) {
                            Sleep::for($backoffs[$attempt - 1] ?? (end($backoffs) ?: 5))->seconds();
                        }
                    }
                }
            }

            if ($round < $rounds) {
                ($this->onFailover)("All providers failed in round {$round}/{$rounds}; waiting {$roundBackoff}s before sweeping again.");
                Sleep::for($roundBackoff)->seconds();
            }
        }

        throw new RuntimeException('All LLM providers failed after retries.', previous: $lastError);
    }

    /**
     * @return array<int, string>
     */
    private function usableProviders(): array
    {
        return array_values(array_filter(
            $this->order,
            fn (string $name) => ! empty($this->providers[$name]['api_key'])
        ));
    }

    /**
     * @param array{base_url: string, model: string, api_key: ?string, context_window_tokens?: int, history_compress_ratio?: float, token_char_estimate?: float} $config
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array<string, mixed>> $tools
     * @return array<string, mixed>
     */
    private function request(string $name, array $config, array $messages, array $tools): array
    {
        $payload = [
            'model' => $config['model'],
            'messages' => $messages,
        ];

        if ($tools !== []) {
            $payload['tools'] = $tools;
        }

        try {
            $response = Http::withToken($config['api_key'])
                ->timeout(180)
                ->post(rtrim($config['base_url'], '/').'/chat/completions', $payload);
        } catch (\Throwable $e) {
            throw new LlmRequestException($e->getMessage(), retryable: true, previous: $e);
        }

        if ($response->failed()) {
            $status = $response->status();

            throw new LlmRequestException(
                "HTTP {$status}: ".mb_substr($response->body(), 0, 500),
                retryable: $status >= 500 || $status === 429 || $status === 408,
            );
        }

        $message = $response->json('choices.0.message');

        if (! is_array($message)) {
            throw new LlmRequestException('Response had no assistant message.', retryable: true);
        }

        $this->lastProvider = $name;

        return $message;
    }
}
