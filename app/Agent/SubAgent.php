<?php

namespace App\Agent;

/**
 * A short-lived agent that runs in its own process to handle one delegated
 * task and return only its final answer. It can read prompt resources and use
 * the parent's generated tools, but cannot create tools, switch prompts, or
 * spawn further subagents — so the bulk of whatever it reads stays in its
 * throwaway context and never touches the parent's history.
 */
class SubAgent
{
    private const DEFAULT_PROMPT = 'You are a focused subagent. Complete the given task using your tools, then reply with a concise, complete answer and no tool calls.';

    /**
     * @param array{max_tool_result_chars?: int} $limits
     */
    public function __construct(
        private LlmClient $llm,
        private ToolRegistry $registry,
        private PromptRepository $prompts,
        private array $limits = [],
    ) {
    }

    public function run(string $task, string $systemPromptId, int $maxIterations): string
    {
        $prompt = $this->prompts->find($systemPromptId);

        $messages = [
            ['role' => 'system', 'content' => $prompt['body'] ?? self::DEFAULT_PROMPT],
            ['role' => 'user', 'content' => $task],
        ];

        $last = '';

        for ($i = 1; $i <= $maxIterations; $i++) {
            $this->registry->refreshGenerated();

            $assistant = $this->llm->chat($messages, $this->registry->subagentDefinitions());
            $messages[] = $assistant;

            $content = trim((string) ($assistant['content'] ?? ''));

            if ($content !== '') {
                $last = $content;
            }

            $toolCalls = $assistant['tool_calls'] ?? [];

            if ($toolCalls === []) {
                return $content;
            }

            foreach ($toolCalls as $call) {
                $name = $call['function']['name'] ?? '';
                $arguments = json_decode($call['function']['arguments'] ?? '{}', true) ?: [];

                $result = $this->dispatch($name, $arguments);
                $resultJson = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_SLASHES);

                $max = $this->limits['max_tool_result_chars'] ?? 8000;

                if ($max > 0 && mb_strlen($resultJson) > $max) {
                    $resultJson = mb_substr($resultJson, 0, $max).'…[truncated]';
                }

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'] ?? '',
                    'content' => $resultJson,
                ];
            }
        }

        return $last !== '' ? $last : '(The subagent reached its iteration limit without a final answer.)';
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function dispatch(string $name, array $arguments): mixed
    {
        try {
            return match ($name) {
                'list_prompt_resources' => $this->prompts->all(),
                'read_prompt_resource' => $this->prompts->find((string) ($arguments['id'] ?? ''))
                    ?? ['error' => 'No prompt with that id.'],
                'search_prompt_resources' => $this->prompts->search((string) ($arguments['query'] ?? '')),
                default => $this->registry->isGenerated($name)
                    ? $this->registry->executeGenerated($name, $arguments)
                    : ['error' => "Subagents cannot use tool [{$name}]."],
            };
        } catch (\Throwable $e) {
            return ['error' => "Tool [{$name}] threw: {$e->getMessage()}"];
        }
    }
}
