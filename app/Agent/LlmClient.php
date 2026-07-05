<?php

namespace App\Agent;

use Closure;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LlmClient
{
    private int $active = 0;

    private Closure $onFailover;

    /**
     * @param array<string, array{base_url: string, model: string, api_key: ?string}> $providers
     * @param array<int, string> $order
     */
    public function __construct(
        private array $providers,
        private array $order,
        ?Closure $onFailover = null,
    ) {
        $this->onFailover = $onFailover ?? fn (string $message) => null;
    }

    public function activeProvider(): ?string
    {
        return $this->order[$this->active] ?? null;
    }

    /**
     * Send a chat completion request, failing over to the next provider on error.
     * A provider that fails is abandoned for the rest of the run.
     *
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array<string, mixed>> $tools
     * @return array<string, mixed> The assistant message.
     */
    public function chat(array $messages, array $tools): array
    {
        $lastError = null;

        while ($this->active < count($this->order)) {
            $name = $this->order[$this->active];
            $config = $this->providers[$name] ?? [];

            if (empty($config['api_key'])) {
                ($this->onFailover)("Provider [{$name}] has no API key configured, skipping.");
                $this->active++;

                continue;
            }

            try {
                $payload = [
                    'model' => $config['model'],
                    'messages' => $messages,
                ];

                if ($tools !== []) {
                    $payload['tools'] = $tools;
                }

                $response = Http::withToken($config['api_key'])
                    ->timeout(180)
                    ->post(rtrim($config['base_url'], '/').'/chat/completions', $payload);

                if ($response->failed()) {
                    throw new RuntimeException("HTTP {$response->status()}: ".mb_substr($response->body(), 0, 500));
                }

                $message = $response->json('choices.0.message');

                if (! is_array($message)) {
                    throw new RuntimeException('Response had no assistant message.');
                }

                return $message;
            } catch (\Throwable $e) {
                $lastError = $e;
                ($this->onFailover)("Provider [{$name}] failed ({$e->getMessage()}), trying next provider.");
                $this->active++;
            }
        }

        throw new RuntimeException('All LLM providers failed.', previous: $lastError);
    }
}
