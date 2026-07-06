<?php

namespace App\Agent;

use Closure;

class AgentRunner
{
    private int $iteration = 0;

    private int $promptSwitches = 0;

    private int $toolsCreated = 0;

    private int $subagentsSpawned = 0;

    private string $activePromptId = '';

    private ?array $pendingPrompt = null;

    private bool $openEnded = false;

    /**
     * Learned after a provider context overflow: compress well before the size
     * that actually failed, since the configured window estimate proved wrong.
     */
    private ?int $learnedCompressChars = null;

    /**
     * @param array{allow_self_modify_system_prompt: bool, require_human_approval_for_prompt_switch: bool, allow_make_tool: bool, require_human_approval_for_new_tools: bool, allow_spawn_subagent?: bool} $modeConfig
     * @param array{max_prompt_switches_per_run: int, max_tools_created_per_run: int, autonomous_continue_message?: string, history_compress_chars?: ?int, max_tool_result_chars?: int, max_subagents_per_run?: int, max_generated_tools_per_request?: int} $limits
     * @param Closure(string): bool $approve Ask the human a yes/no question.
     * @param Closure(string, string): void $output Report progress to the human as (type, message). Types: iteration, thought, tool_call, tool_result, proposal, switch, system.
     * @param Closure(string): array{answer?: string, error?: string}|null $spawnSubagent Run a subtask in a separate process and return its answer.
     * @param Closure(int): ?string $checkpoint Snapshot the work so far (e.g. a git commit). Called at each open-ended nudge with the current iteration; may return a note for the agent (e.g. a stagnation warning).
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
        private ?Closure $spawnSubagent = null,
        private ?Closure $checkpoint = null,
    ) {
    }

    private function subagentsEnabled(): bool
    {
        return $this->spawnSubagent !== null && ($this->modeConfig['allow_spawn_subagent'] ?? false);
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
        $this->openEnded = $openEnded;

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

            $this->compressHistoryIfNeeded($messages, $task);

            // Reload generated tools from disk so tools made last iteration appear.
            $this->registry->refreshGenerated();

            ($this->output)('iteration', "Iteration {$this->iteration} · prompt: {$this->activePromptId} · provider: {$this->llm->activeProvider()} · tools created: {$this->toolsCreated}");

            $tools = $this->registry->allDefinitions(
                $this->modeConfig['allow_make_tool'],
                $this->subagentsEnabled(),
                $this->limits['max_generated_tools_per_request'] ?? null,
                $this->toolFocusNames($messages),
            );

            try {
                $assistant = $this->llm->chat($messages, $tools);
            } catch (ContextOverflowException) {
                // The configured window estimate was too optimistic: the
                // provider rejected the conversation outright, so a normal
                // summarization round-trip would be rejected too. Drop old
                // history hard, remember the size that failed, and retry once.
                $failedSize = strlen(json_encode($messages, JSON_UNESCAPED_SLASHES) ?: '');
                $this->learnedCompressChars = max(50_000, intdiv($failedSize, 2));

                ($this->output)('system', "Provider reported a context overflow at ~{$failedSize} chars; emergency-truncating history and compressing earlier from now on.");

                $this->emergencyTruncate($messages, $task);

                $assistant = $this->llm->chat($messages, $tools);
            }

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

                // A journal answer with no tool call is a natural resting point:
                // tools from prior iterations are on disk and the roadmap is
                // current. Snapshot here before nudging the agent onward.
                $checkpointNote = null;

                if ($this->checkpoint !== null) {
                    $checkpointNote = ($this->checkpoint)($this->iteration);
                }

                // Each checkpoint starts a fresh session segment: the safety
                // fuses bound how much can happen per segment, not per run.
                // Without this, a --forever run permanently loses make_tool
                // after max_tools_created_per_run and degenerates into
                // journal-editing loops (as observed in the wild).
                $this->refreshSegmentBudgets();

                ($this->output)('system', 'Open-ended mode: nudging the agent to pick its next move.');

                $continueMessage = $this->limits['autonomous_continue_message']
                    ?? 'Continue the open-ended experiment. Decide your next useful or surprising step, then either call a tool, create a tool, inspect your resources, or report what you discovered.';

                if ($checkpointNote !== null && $checkpointNote !== '') {
                    $continueMessage .= "\n\n[Host] {$checkpointNote}";
                }

                $messages[] = [
                    'role' => 'user',
                    'content' => $continueMessage,
                ];

                continue;
            }

            foreach ($toolCalls as $call) {
                $name = $call['function']['name'] ?? '';
                $arguments = json_decode($call['function']['arguments'] ?? '{}', true) ?: [];

                ($this->output)('tool_call', "{$name}(".json_encode($arguments, JSON_UNESCAPED_SLASHES).')');

                $result = $this->dispatch($name, $arguments);
                $resultJson = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_SLASHES);

                // Cap what goes into the model's history, so one giant tool
                // result cannot blow the context window in a single call.
                $maxChars = $this->limits['max_tool_result_chars'] ?? 8000;

                if ($maxChars > 0 && mb_strlen($resultJson) > $maxChars) {
                    $fullLength = mb_strlen($resultJson);
                    $resultJson = mb_substr($resultJson, 0, $maxChars)."…[truncated by host; the full result was {$fullLength} characters]";
                }

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
     * Reset the per-segment safety fuses at an open-ended resting point. The
     * limits still bound how much damage one segment can do between human-
     * reviewable checkpoints, but a long run no longer starves permanently.
     */
    private function refreshSegmentBudgets(): void
    {
        $exhausted = [];

        if ($this->toolsCreated >= ($this->limits['max_tools_created_per_run'] ?? PHP_INT_MAX)) {
            $exhausted[] = 'tool creation';
        }

        if ($this->subagentsSpawned >= ($this->limits['max_subagents_per_run'] ?? PHP_INT_MAX)) {
            $exhausted[] = 'subagents';
        }

        if ($this->promptSwitches >= ($this->limits['max_prompt_switches_per_run'] ?? PHP_INT_MAX)) {
            $exhausted[] = 'prompt switches';
        }

        $this->toolsCreated = 0;
        $this->subagentsSpawned = 0;
        $this->promptSwitches = 0;

        if ($exhausted !== []) {
            ($this->output)('system', 'Checkpoint refreshed exhausted budgets: '.implode(', ', $exhausted).'.');
        }
    }

    /**
     * Tell the model how a limit behaves: in an open-ended run the fuse
     * refreshes at the next checkpoint, so the agent should rest (answer
     * without tool calls) instead of abandoning the capability forever.
     */
    private function limitReachedMessage(string $subject): string
    {
        return $this->openEnded
            ? "The {$subject} limit for this segment has been reached. It refreshes at the next checkpoint: finish this segment with a journal answer (no tool calls), then continue."
            : "The {$subject} limit for this run has been reached.";
    }

    /**
     * Last-resort history shrink after a provider context overflow. Unlike
     * normal compression there is no LLM round-trip (the history is already
     * unsendable), so old messages are dropped, not summarized: keep the
     * system prompt, a bridge message pointing at the persistent workspace,
     * and a recent tail small enough to fit any plausible real window.
     *
     * @param array<int, array<string, mixed>> $messages
     */
    private function emergencyTruncate(array &$messages, string $task): void
    {
        $system = $messages[0];
        $budget = 200_000; // chars, ~50K tokens: fits every model in use here.

        $tail = [];
        $size = 0;

        for ($i = count($messages) - 1; $i >= 1; $i--) {
            $size += strlen(json_encode($messages[$i], JSON_UNESCAPED_SLASHES) ?: '');

            if ($size > $budget && $tail !== []) {
                break;
            }

            array_unshift($tail, $messages[$i]);
        }

        // A tool result whose assistant tool_call partner was dropped would be
        // rejected by the API; trim orphaned tool messages off the front.
        while ($tail !== [] && ($tail[0]['role'] ?? '') === 'tool') {
            array_shift($tail);
        }

        $messages = array_merge([
            $system,
            [
                'role' => 'user',
                'content' => "Original task: {$task}\n\n"
                    .'[Host] Emergency context truncation: the provider rejected the conversation as too large, '
                    .'so your older history was dropped without a summary. Rebuild your bearings from ROADMAP.md '
                    .'and your workspace files, then continue the mission.',
            ],
        ], $tail);
    }

    /**
     * When the conversation history nears the model's context limit, ask the
     * model to write a memory summary of the session, then replace the old
     * messages with it. The agent experiences this as consolidated memory;
     * without it, a long run dies of context overflow.
     *
     * @param array<int, array<string, mixed>> $messages
     */
    private function compressHistoryIfNeeded(array &$messages, string $task): void
    {
        $threshold = $this->limits['history_compress_chars'] ?? $this->llm->contextCharBudget() ?? 150_000;

        if ($this->learnedCompressChars !== null) {
            $threshold = $threshold > 0 ? min($threshold, $this->learnedCompressChars) : $this->learnedCompressChars;
        }

        if ($threshold <= 0) {
            return;
        }

        $size = strlen(json_encode($messages, JSON_UNESCAPED_SLASHES) ?: '');

        if ($size <= $threshold) {
            return;
        }

        ($this->output)('system', "History is ~{$size} chars (threshold {$threshold}); compressing it into a memory summary.");

        $request = $messages;
        $request[] = [
            'role' => 'user',
            'content' => 'HOST NOTICE: This conversation is close to the model context limit, so the host is about to compress your history. '
                .'Write a thorough memory summary of this session, addressed to your future self. Include: the original task and your current goals; '
                .'every tool you created or changed (name and purpose); key discoveries and unresolved bugs; which workspace or journal files you rely on; '
                .'and exactly what you planned to do next. Reply with only the summary text.',
        ];

        $summary = trim((string) ($this->llm->chat($request, [])['content'] ?? ''));

        if ($summary === '') {
            ($this->output)('system', 'Compression produced an empty summary; keeping the full history for now.');

            return;
        }

        $messages = [
            $messages[0],
            [
                'role' => 'user',
                'content' => "Original task: {$task}\n\n"
                    ."[Host] Your earlier conversation history was compressed to keep this long run alive. "
                    ."Here is the memory summary you wrote:\n\n{$summary}\n\n"
                    .'Continue from where you left off.',
            ],
        ];

        ($this->output)('system', 'History compressed; continuing with consolidated memory.');
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
                'list_generated_tools' => $this->handleListGeneratedTools($arguments),
                'search_generated_tools' => $this->handleSearchGeneratedTools($arguments),
                'suggest_system_prompt' => $this->handleSuggestSystemPrompt($arguments),
                'make_tool' => $this->handleMakeTool($arguments),
                'spawn_subagent' => $this->handleSpawnSubagent($arguments),
                default => $this->registry->isGenerated($name)
                    ? $this->registry->executeGenerated($name, $arguments)
                    : ['error' => "Unknown tool [{$name}]."],
            };
        } catch (\Throwable $e) {
            return ['error' => "Tool [{$name}] threw: {$e->getMessage()}"];
        }
    }

    /**
     * Find generated tool names that appear in recent conversation text. This
     * lets catalog search results pin matching executable schemas into the
     * next request without shipping every generated tool on every turn.
     *
     * @param array<int, array<string, mixed>> $messages
     * @return array<int, string>
     */
    private function toolFocusNames(array $messages): array
    {
        $haystackParts = [];

        foreach (array_slice($messages, -12) as $message) {
            $haystackParts[] = is_string($message['content'] ?? null) ? $message['content'] : json_encode($message, JSON_UNESCAPED_SLASHES);
        }

        $haystack = implode("\n", array_filter($haystackParts));
        $focus = [];

        foreach ($this->registry->generatedNames() as $name) {
            if (str_contains($haystack, $name)) {
                $focus[] = $name;
            }
        }

        return $focus;
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function handleListGeneratedTools(array $arguments): array
    {
        $limit = max(1, min(200, (int) ($arguments['limit'] ?? 80)));

        return [
            'total' => count($this->registry->generatedNames()),
            'tools' => $this->registry->generatedCatalog($limit),
            'note' => 'Use search_generated_tools for focused discovery. Mentioned tool names are made callable on the next request.',
        ];
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function handleSearchGeneratedTools(array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $limit = max(1, min(50, (int) ($arguments['limit'] ?? 20)));

        if ($query === '') {
            return ['error' => 'search_generated_tools requires a non-empty query.'];
        }

        return [
            'query' => $query,
            'matches' => $this->registry->searchGenerated($query, $limit),
            'note' => 'These matching tool names are now in recent history, so the host will prioritize making them callable on the next request.',
        ];
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function handleSpawnSubagent(array $arguments): array
    {
        $task = trim((string) ($arguments['task'] ?? ''));

        if ($task === '') {
            return ['error' => 'spawn_subagent requires a non-empty task.'];
        }

        if (! $this->subagentsEnabled()) {
            return ['error' => 'Subagents are not available in this run.'];
        }

        $max = $this->limits['max_subagents_per_run'] ?? 0;

        if ($this->subagentsSpawned >= $max) {
            return ['error' => $this->limitReachedMessage('subagent')];
        }

        $this->subagentsSpawned++;
        ($this->output)('system', "Spawning subagent #{$this->subagentsSpawned}: ".mb_substr($task, 0, 120));

        $result = ($this->spawnSubagent)($task);
        $ok = ! isset($result['error']);

        $this->logger->subagent([
            'iteration' => $this->iteration,
            'task' => mb_substr($task, 0, 500),
            'ok' => $ok,
            'mode' => $this->mode,
        ]);

        return $ok ? ['answer' => $result['answer'] ?? ''] : ['error' => $result['error']];
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

            return ['switched' => false, 'reason' => $this->limitReachedMessage('prompt switch')];
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
        $overwrite = (bool) ($arguments['overwrite'] ?? false);

        $logEntry = [
            'iteration' => $this->iteration,
            'tool' => $name,
            'description' => $description,
            'approved' => false,
            'overwrite' => $overwrite,
            'mode' => $this->mode,
        ];

        if (! $this->modeConfig['allow_make_tool']) {
            $this->logger->toolChange($logEntry);

            return ['created' => false, 'errors' => ["Tool creation is disabled in [{$this->mode}] mode."]];
        }

        if ($this->toolsCreated >= $this->limits['max_tools_created_per_run']) {
            $this->logger->toolChange($logEntry + ['limit' => true]);

            return ['created' => false, 'errors' => [$this->limitReachedMessage('tool creation')]];
        }

        $errors = $this->toolMaker->validate($name, $schema, $code, $overwrite);

        if ($errors !== []) {
            $this->logger->toolChange($logEntry + ['errors' => $errors]);

            return ['created' => false, 'errors' => $errors];
        }

        if ($this->modeConfig['require_human_approval_for_new_tools']) {
            $verb = $overwrite ? 'replacement for tool' : 'tool';
            ($this->output)('proposal', "Proposed {$verb} [{$name}]: {$description}\n---\n{$code}\n---");

            if (! ($this->approve)("Save this {$verb} [{$name}]?")) {
                $this->logger->toolChange($logEntry);

                return ['created' => false, 'errors' => ['The human declined the new tool.']];
            }
        }

        $result = $this->toolMaker->make($name, $description, $schema, $code, $overwrite);

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
