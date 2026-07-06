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
     * @param array{max_tool_result_chars?: int, max_generated_tools_per_request?: int} $limits
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

            $assistant = $this->llm->chat(
                $messages,
                $this->registry->subagentDefinitions(
                    $this->limits['max_generated_tools_per_request'] ?? null,
                    $this->toolFocusNames($messages),
                ),
            );
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

                if ($name === 'end_turn' && is_array($result) && ($result['finished'] ?? false)) {
                    return (string) ($result['answer'] ?? '');
                }

                $resultJson = is_string($result) ? $result : $this->encodeJson($result);

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
                'find_project_files' => $this->handleFindProjectFiles($arguments),
                'end_turn' => $this->handleEndTurn($arguments),
                'list_generated_tools' => $this->handleListGeneratedTools($arguments),
                'search_generated_tools' => $this->handleSearchGeneratedTools($arguments),
                default => $this->registry->isGenerated($name)
                    ? $this->registry->executeGenerated($name, $arguments)
                    : ['error' => "Subagents cannot use tool [{$name}]."],
            };
        } catch (\Throwable $e) {
            return ['error' => "Tool [{$name}] threw: {$e->getMessage()}"];
        }
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function handleEndTurn(array $arguments): array
    {
        $summary = trim((string) ($arguments['summary'] ?? ''));
        $verification = trim((string) ($arguments['verification'] ?? ''));

        if ($summary === '') {
            return ['finished' => false, 'error' => 'end_turn requires a non-empty summary.'];
        }

        if ($this->containsQuestion($summary) || $this->containsQuestion($verification)) {
            return [
                'finished' => false,
                'error' => 'end_turn is terminal and cannot ask follow-up questions. Return the needed question to the parent instead.',
            ];
        }

        $answer = $summary;

        if ($verification !== '') {
            $answer .= "\n\nVerification: {$verification}";
        }

        return ['finished' => true, 'answer' => $answer];
    }

    private function containsQuestion(string $text): bool
    {
        if ($text === '') {
            return false;
        }

        if (str_contains($text, '?')) {
            return true;
        }

        return (bool) preg_match('/\b(should I|shall I|do you want|want me to|would you like|can I|may I)\b/i', $text);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function handleFindProjectFiles(array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $path = trim((string) ($arguments['path'] ?? '.'));
        $limit = max(1, min(200, (int) ($arguments['max_results'] ?? 40)));

        if ($query === '') {
            return ['error' => 'find_project_files requires a non-empty query.'];
        }

        return $this->findProjectFiles($query, $path, $limit);
    }

    /**
     * @return array{query: string, root: string, total_matches: int, truncated: bool, files: array<int, string>}
     */
    private function findProjectFiles(string $query, string $path, int $limit): array
    {
        $projectRoot = realpath(base_path()) ?: base_path();
        $path = ($path === '' || $path === '/' || preg_match('/^[A-Za-z]:[\\\\\/]?$/', $path)) ? '.' : $path;
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $searchRoot = realpath($projectRoot.DIRECTORY_SEPARATOR.$path);

        if ($searchRoot === false || ! str_starts_with(str_replace('\\', '/', $searchRoot), str_replace('\\', '/', $projectRoot))) {
            $searchRoot = $projectRoot;
            $path = '.';
        }

        $excludedDirs = ['.git', 'vendor', 'node_modules', '.idea', '.vscode'];
        $matches = [];
        $total = 0;
        $hasWildcard = str_contains($query, '*') || str_contains($query, '?');
        $needle = mb_strtolower(str_replace('\\', '/', $query));

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($searchRoot, \FilesystemIterator::SKIP_DOTS),
                function (\SplFileInfo $file) use ($excludedDirs): bool {
                    return ! ($file->isDir() && in_array($file->getFilename(), $excludedDirs, true));
                },
            ),
        );

        foreach ($iterator as $file) {
            if (! $file instanceof \SplFileInfo || ! $file->isFile()) {
                continue;
            }

            $absolute = str_replace('\\', '/', $file->getPathname());
            $relative = ltrim(substr($absolute, strlen(str_replace('\\', '/', $projectRoot))), '/');
            $candidate = mb_strtolower($relative);
            $basename = mb_strtolower($file->getFilename());
            $matched = $hasWildcard
                ? fnmatch($needle, $candidate) || fnmatch($needle, $basename)
                : str_contains($candidate, $needle) || str_contains($basename, $needle);

            if (! $matched) {
                continue;
            }

            $total++;

            if (count($matches) < $limit) {
                $matches[] = $relative;
            }
        }

        sort($matches);

        return [
            'query' => $query,
            'root' => $path === '.' ? '.' : $path,
            'total_matches' => $total,
            'truncated' => $total > count($matches),
            'files' => $matches,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $messages
     * @return array<int, string>
     */
    private function toolFocusNames(array $messages): array
    {
        $haystackParts = [];

        foreach (array_slice($messages, -12) as $message) {
            $haystackParts[] = is_string($message['content'] ?? null) ? $message['content'] : $this->encodeJson($message);
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
        ];
    }

    private function encodeJson(mixed $value): string
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json !== false) {
            return $json;
        }

        return json_encode([
            'error' => 'Value could not be JSON-encoded: '.json_last_error_msg(),
        ], JSON_UNESCAPED_SLASHES) ?: '{"error":"Value could not be JSON-encoded."}';
    }
}
