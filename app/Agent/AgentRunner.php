<?php

namespace App\Agent;

use Closure;

class AgentRunner
{
    private int $iteration = 0;

    private int $promptSwitches = 0;

    private int $toolsCreated = 0;

    private string $activePromptId = '';

    private ?array $pendingPrompt = null;

    /**
     * @param array{allow_self_modify_system_prompt: bool, require_human_approval_for_prompt_switch: bool, allow_make_tool: bool, require_human_approval_for_new_tools: bool} $modeConfig
     * @param array{max_prompt_switches_per_run: int, max_tools_created_per_run: int, autonomous_continue_message?: string} $limits
     * @param Closure(string): bool $approve Ask the human a yes/no question.
     * @param Closure(string, string): void $output Report progress to the human as (type, message). Types: iteration, thought, tool_call, tool_result, proposal, switch, system.
     */
    public function __construct(
        private LlmClient $llm,
        private PromptRepository $prompts,
        private ToolRegistry $registry,
        private ToolMaker $toolMaker,
        private AgentLogger $logger,
        private string $mode,
        private array $modeConfig,
        private array $limits,
        private Closure $approve,
        private Closure $output,
    ) {
    }

    public function activePromptId(): string
    {
        return $this->activePromptId;
    }

    /**
     * Run the agent loop. In normal mode, stops when the model answers without
     * tool calls or the iteration budget runs out. In open-ended mode, a normal
     * answer is treated as a journal entry and the host asks the agent to choose
     * its next move.
     */
    public function run(string $task, string $promptId, ?int $maxIterations, bool $openEnded = false): ?string
    {
        $prompt = $this->prompts->find($promptId);

        if ($prompt === null) {
            throw new \InvalidArgumentException("Unknown system prompt [{$promptId}].");
        }

        $this->activePromptId = $prompt['id'];

        $messages = [
            ['role' => 'system', 'content' => $prompt['body']],
            ['role' => 'user', 'content' => $task],
        ];

        $finalAnswer = null;

        for ($this->iteration = 1; $maxIterations === null || $this->iteration <= $maxIterations; $this->iteration++) {
            // Apply an approved prompt switch at the start of the iteration.
            if ($this->pendingPrompt !== null) {
                $messages[0]['content'] = $this->pendingPrompt['body'];
                $this->activePromptId = $this->pendingPrompt['id'];
                ($this->output)('switch', "System prompt is now [{$this->activePromptId}].");
                $this->pendingPrompt = null;
            }

            // Reload generated tools from disk so tools made last iteration appear.
            $this->registry->refreshGenerated();

            ($this->output)('iteration', "Iteration {$this->iteration} · prompt: {$this->activePromptId} · provider: {$this->llm->activeProvider()} · tools created: {$this->toolsCreated}");

            $assistant = $this->llm->chat(
                $messages,
                $this->registry->allDefinitions($this->modeConfig['allow_make_tool'])
            );

            $messages[] = $assistant;

            $content = trim((string) ($assistant['content'] ?? ''));

            if ($content !== '') {
                ($this->output)('thought', $content);
            }

            $toolCalls = $assistant['tool_calls'] ?? [];

            if ($toolCalls === []) {
                $finalAnswer = $content;

                if (! $openEnded) {
                    break;
                }

                ($this->output)('system', 'Open-ended mode: nudging the agent to pick its next move.');

                $messages[] = [
                    'role' => 'user',
                    'content' => $this->limits['autonomous_continue_message']
                        ?? 'Continue the open-ended experiment. Decide your next useful or surprising step, then either call a tool, create a tool, inspect your resources, or report what you discovered.',
                ];

                continue;
            }

            foreach ($toolCalls as $call) {
                $name = $call['function']['name'] ?? '';
                $arguments = json_decode($call['function']['arguments'] ?? '{}', true) ?: [];

                ($this->output)('tool_call', "{$name}(".json_encode($arguments, JSON_UNESCAPED_SLASHES).')');

                $result = $this->dispatch($name, $arguments);
                $resultJson = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_SLASHES);

                ($this->output)('tool_result', mb_strlen($resultJson) > 400 ? mb_substr($resultJson, 0, 400).'…' : $resultJson);

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'] ?? '',
                    'content' => $resultJson,
                ];
            }
        }

        return $finalAnswer;
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
                'suggest_system_prompt' => $this->handleSuggestSystemPrompt($arguments),
                'make_tool' => $this->handleMakeTool($arguments),
                default => $this->registry->isGenerated($name)
                    ? $this->registry->executeGenerated($name, $arguments)
                    : ['error' => "Unknown tool [{$name}]."],
            };
        } catch (\Throwable $e) {
            return ['error' => "Tool [{$name}] threw: {$e->getMessage()}"];
        }
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function handleSuggestSystemPrompt(array $arguments): array
    {
        $promptId = (string) ($arguments['prompt_id'] ?? '');
        $reason = (string) ($arguments['reason'] ?? '');

        $target = $this->prompts->find($promptId);

        if ($target === null) {
            return ['switched' => false, 'reason' => "No prompt with id [{$promptId}] exists."];
        }

        $logEntry = [
            'iteration' => $this->iteration,
            'from' => $this->activePromptId,
            'to' => $promptId,
            'reason' => $reason,
            'approved' => false,
            'mode' => $this->mode,
        ];

        if (! $this->modeConfig['allow_self_modify_system_prompt']) {
            $this->logger->promptSwitch($logEntry);

            return ['switched' => false, 'reason' => "Self-modification of the system prompt is disabled in [{$this->mode}] mode."];
        }

        if ($this->promptSwitches >= $this->limits['max_prompt_switches_per_run']) {
            $this->logger->promptSwitch($logEntry);

            return ['switched' => false, 'reason' => 'The prompt switch limit for this run has been reached.'];
        }

        if ($this->modeConfig['require_human_approval_for_prompt_switch']) {
            $approved = ($this->approve)("Agent wants to switch system prompt from [{$this->activePromptId}] to [{$promptId}]. Reason: {$reason}. Allow?");

            if (! $approved) {
                $this->logger->promptSwitch($logEntry);

                return ['switched' => false, 'reason' => 'The human declined the prompt switch.'];
            }
        }

        $this->pendingPrompt = $target;
        $this->promptSwitches++;

        $logEntry['approved'] = true;
        $this->logger->promptSwitch($logEntry);

        return ['switched' => true, 'reason' => "Approved. The [{$promptId}] prompt takes effect on the next iteration."];
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function handleMakeTool(array $arguments): array
    {
        $name = (string) ($arguments['name'] ?? '');
        $description = (string) ($arguments['description'] ?? '');
        $schema = is_array($arguments['parameters_schema'] ?? null) ? $arguments['parameters_schema'] : [];
        $code = (string) ($arguments['code'] ?? '');

        $logEntry = [
            'iteration' => $this->iteration,
            'tool' => $name,
            'description' => $description,
            'approved' => false,
            'mode' => $this->mode,
        ];

        if (! $this->modeConfig['allow_make_tool']) {
            $this->logger->toolChange($logEntry);

            return ['created' => false, 'errors' => ["Tool creation is disabled in [{$this->mode}] mode."]];
        }

        if ($this->toolsCreated >= $this->limits['max_tools_created_per_run']) {
            $this->logger->toolChange($logEntry);

            return ['created' => false, 'errors' => ['The tool creation limit for this run has been reached.']];
        }

        $errors = $this->toolMaker->validate($name, $schema, $code);

        if ($errors !== []) {
            $this->logger->toolChange($logEntry + ['errors' => $errors]);

            return ['created' => false, 'errors' => $errors];
        }

        if ($this->modeConfig['require_human_approval_for_new_tools']) {
            ($this->output)('proposal', "Proposed tool [{$name}]: {$description}\n---\n{$code}\n---");

            if (! ($this->approve)("Save this new tool [{$name}]?")) {
                $this->logger->toolChange($logEntry);

                return ['created' => false, 'errors' => ['The human declined the new tool.']];
            }
        }

        $result = $this->toolMaker->make($name, $description, $schema, $code);

        if (! $result['ok']) {
            $this->logger->toolChange($logEntry + ['errors' => $result['errors']]);

            return ['created' => false, 'errors' => $result['errors']];
        }

        $this->toolsCreated++;
        $logEntry['approved'] = true;
        $this->logger->toolChange($logEntry);

        return ['created' => true, 'note' => "Tool [{$name}] saved. It becomes available on the next iteration."];
    }
}
