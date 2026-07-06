<?php

namespace App\Agent;

use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class ToolRegistry
{
    /** @var array<string, array> Generated tool definitions, keyed by tool name. */
    private array $generated = [];

    /** @var array<string, int> Generated tool mtimes, keyed by tool name. */
    private array $generatedMtimes = [];

    public function __construct(
        private string $generatedToolsPath,
        private string $toolMemoryLimit = '64M',
        private int|float $toolTimeoutSeconds = 10,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function builtInNames(): array
    {
        return array_map(
            fn (array $definition) => $definition['function']['name'],
            $this->builtInDefinitions()
        );
    }

    /**
     * @return array<int, array>
     */
    public function builtInDefinitions(bool $includeMakeTool = true, bool $includeSubagent = true, bool $includeAgentControl = true): array
    {
        $definitions = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_prompt_resources',
                    'description' => 'List the available system prompt resources (id, title, tags).',
                    'parameters' => ['type' => 'object', 'properties' => (object) []],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'read_prompt_resource',
                    'description' => 'Read the full content of one system prompt resource by id.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'string', 'description' => 'The prompt id, e.g. "toolmaker".'],
                        ],
                        'required' => ['id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_prompt_resources',
                    'description' => 'Search prompt resources by id, title, tags, and content.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Case-insensitive search term.'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'find_project_files',
                    'description' => 'Find files by filename or relative path inside this project. Fast, bounded, and safer than content indexing; use this first when locating a file like AgentRunner.php.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Filename or path fragment to find, e.g. "AgentRunner.php" or "app/Agent". Supports * wildcards.'],
                            'path' => ['type' => 'string', 'description' => 'Optional relative directory to search from. Defaults to project root. Absolute paths and "/" are treated as project root.'],
                            'max_results' => ['type' => 'integer', 'description' => 'Maximum matches to return, 1-200. Defaults to 40.'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'end_turn',
                    'description' => 'Finish the current non-forever agent run immediately with a final answer. Use this only after the requested task is complete or blocked. Do not ask follow-up questions here; use ask_human first.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'summary' => ['type' => 'string', 'description' => 'Concise final answer for the human: what changed or what was found. Must be a statement, not a question.'],
                            'verification' => ['type' => 'string', 'description' => 'Tests, linters, or checks run. If none ran, explain why.'],
                        ],
                        'required' => ['summary'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'ask_human',
                    'description' => 'Ask the human one concise question, wait for their answer, then continue the run using that answer. Use this only when a choice or missing detail blocks progress.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'question' => ['type' => 'string', 'description' => 'The exact question to show the human. Keep it concise and include the default assumption if useful.'],
                            'context' => ['type' => 'string', 'description' => 'Optional short context explaining why the answer is needed.'],
                        ],
                        'required' => ['question'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_generated_tools',
                    'description' => 'List the generated tool catalog without loading every tool schema into the model context. Use this when you need to discover available tools.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'description' => 'Maximum tools to return, 1-200. Defaults to 80.'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_generated_tools',
                    'description' => 'Search generated tools by name and description. Matching tools are prioritized in the next request so you can call them.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Words to match against generated tool names and descriptions.'],
                            'limit' => ['type' => 'integer', 'description' => 'Maximum matches to return, 1-50. Defaults to 20.'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
        ];

        if ($includeAgentControl) {
            $definitions[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'suggest_system_prompt',
                    'description' => 'Ask the host program to switch your system prompt to another prompt resource. The host decides whether the switch happens; if approved it takes effect on the next iteration.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'prompt_id' => ['type' => 'string', 'description' => 'The id of the prompt to switch to.'],
                            'reason' => ['type' => 'string', 'description' => 'Why this prompt fits the current work better.'],
                        ],
                        'required' => ['prompt_id', 'reason'],
                    ],
                ],
            ];
        }

        if ($includeSubagent) {
            $definitions[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'spawn_subagent',
                    'description' => 'Delegate a focused subtask to a fresh subagent that runs in a separate process with its own context. The subagent can read files and use your existing generated tools, but cannot create tools or change prompts. Only its final answer returns to you — use this to offload heavy reading or analysis (e.g. "read tool X and summarize its action handlers") so large content never fills your own context.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'task' => ['type' => 'string', 'description' => 'A self-contained instruction for the subagent. Include any file paths or tool names it needs — it does not share your conversation.'],
                        ],
                        'required' => ['task'],
                    ],
                ],
            ];
        }

        if ($includeMakeTool) {
            $definitions[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'make_tool',
                    'description' => 'Create a new PHP function tool and save it to disk, or replace an existing one with overwrite: true. The tool becomes available on the next iteration. The host may require human approval.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'snake_case tool name.'],
                            'description' => ['type' => 'string', 'description' => 'What the tool does.'],
                            'parameters_schema' => [
                                'type' => 'object',
                                'description' => 'JSON schema object for the tool arguments ({"type":"object","properties":{...},"required":[...]}).',
                            ],
                            'code' => [
                                'type' => 'string',
                                'description' => 'PHP function body only: no <?php tag and no function wrapper. Parameters from the schema are available as PHP variables (e.g. $sides). Must return a value.',
                            ],
                            'overwrite' => [
                                'type' => 'boolean',
                                'description' => 'Replace the existing generated tool of the same name with this improved version. Prefer this over creating _v2/_fixed variants.',
                            ],
                        ],
                        'required' => ['name', 'description', 'parameters_schema', 'code'],
                    ],
                ],
            ];
        }

        return $definitions;
    }

    /**
     * Reload generated tool definitions from disk.
     */
    public function refreshGenerated(): void
    {
        $this->generated = [];
        $this->generatedMtimes = [];

        foreach (glob($this->generatedToolsPath.'/*.php') ?: [] as $file) {
            $definition = $this->loadDefinitionFromFile($file);

            if ($definition === null || ! isset($definition['function']['name'])) {
                continue;
            }

            $name = $definition['function']['name'];

            // Normalize schemas from disk too, so a tool generated before
            // normalization existed cannot poison the API request.
            if (is_array($definition['function']['parameters'] ?? null)) {
                $definition['function']['parameters'] = SchemaNormalizer::normalize($definition['function']['parameters']);
            }

            if (! in_array($name, $this->builtInNames(), true)) {
                $this->generated[$name] = $definition;
                $this->generatedMtimes[$name] = filemtime($file) ?: 0;
            }
        }
    }

    /**
     * @return array<int, array>
     */
    public function allDefinitions(
        bool $includeMakeTool = true,
        bool $includeSubagent = true,
        ?int $maxGeneratedTools = null,
        array $focusNames = [],
    ): array
    {
        return array_merge(
            $this->builtInDefinitions($includeMakeTool, $includeSubagent),
            array_values($this->selectedGeneratedDefinitions($maxGeneratedTools, $focusNames)),
        );
    }

    /**
     * Tools offered to a subagent: read-only prompt access plus the generated
     * tools, but no self-modification (make_tool, suggest_system_prompt) and no
     * further spawning. Recursion is prevented here, by construction.
     *
     * @return array<int, array>
     */
    public function subagentDefinitions(?int $maxGeneratedTools = null, array $focusNames = []): array
    {
        return array_merge(
            $this->builtInDefinitions(includeMakeTool: false, includeSubagent: false, includeAgentControl: false),
            array_values($this->selectedGeneratedDefinitions($maxGeneratedTools, $focusNames)),
        );
    }

    /**
     * @return array<int, array{name: string, description: string, modified_at: int}>
     */
    public function generatedCatalog(?int $limit = null): array
    {
        $entries = [];

        foreach ($this->generated as $name => $definition) {
            $entries[] = [
                'name' => $name,
                'description' => (string) ($definition['function']['description'] ?? ''),
                'modified_at' => $this->generatedMtimes[$name] ?? 0,
            ];
        }

        usort($entries, fn (array $a, array $b) => [$b['modified_at'], $a['name']] <=> [$a['modified_at'], $b['name']]);

        return $limit === null ? $entries : array_slice($entries, 0, max(0, $limit));
    }

    /**
     * @return array<int, array{name: string, description: string, score: int}>
     */
    public function searchGenerated(string $query, int $limit = 20): array
    {
        $terms = array_values(array_filter(preg_split('/\s+/', mb_strtolower($query)) ?: []));
        $matches = [];

        foreach ($this->generated as $name => $definition) {
            $description = (string) ($definition['function']['description'] ?? '');
            $haystack = mb_strtolower($name.' '.$description);
            $score = 0;

            foreach ($terms as $term) {
                if ($term !== '' && str_contains($haystack, $term)) {
                    $score += str_contains(mb_strtolower($name), $term) ? 3 : 1;
                }
            }

            if ($score > 0 || $terms === []) {
                $matches[] = [
                    'name' => $name,
                    'description' => $description,
                    'score' => $score,
                ];
            }
        }

        usort($matches, fn (array $a, array $b) => [$b['score'], $a['name']] <=> [$a['score'], $b['name']]);

        return array_slice($matches, 0, max(0, $limit));
    }

    public function isGenerated(string $name): bool
    {
        return array_key_exists($name, $this->generated);
    }

    public function generatedToolExists(string $name): bool
    {
        return is_file($this->generatedToolsPath.'/'.$name.'.php');
    }

    /**
     * @return array<int, string>
     */
    public function generatedNames(): array
    {
        return array_keys($this->generated);
    }

    /**
     * @param array<int, string> $focusNames
     * @return array<string, array>
     */
    private function selectedGeneratedDefinitions(?int $maxGeneratedTools, array $focusNames): array
    {
        if ($maxGeneratedTools === null || $maxGeneratedTools <= 0 || count($this->generated) <= $maxGeneratedTools) {
            return $this->generated;
        }

        $selected = [];

        foreach ($focusNames as $name) {
            if (isset($this->generated[$name])) {
                $selected[$name] = $this->generated[$name];
            }
        }

        $remaining = array_diff_key($this->generated, $selected);
        uksort($remaining, fn (string $a, string $b) => [$this->generatedMtimes[$b] ?? 0, $a] <=> [$this->generatedMtimes[$a] ?? 0, $b]);

        foreach ($remaining as $name => $definition) {
            if (count($selected) >= $maxGeneratedTools) {
                break;
            }

            $selected[$name] = $definition;
        }

        return $selected;
    }

    /**
     * Run a generated tool in an isolated child PHP process with its own
     * memory limit and timeout, so a runaway tool cannot kill the agent loop.
     * Failures come back as an ['error' => ...] result for the model.
     *
     * @param array<string, mixed> $arguments
     */
    public function executeGenerated(string $name, array $arguments): mixed
    {
        if (! $this->isGenerated($name)) {
            throw new RuntimeException("Generated tool [{$name}] is not loaded.");
        }

        $process = new Process([
            PHP_BINARY,
            '-d', 'memory_limit='.$this->toolMemoryLimit,
            __DIR__.'/scripts/run-tool.php',
            $this->generatedToolsPath.'/'.$name.'.php',
            $name,
            base64_encode(json_encode($arguments)),
        ]);

        $process->setTimeout($this->toolTimeoutSeconds);

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            return ['error' => "Tool [{$name}] was killed after running longer than {$this->toolTimeoutSeconds} seconds."];
        }

        if (! $process->isSuccessful()) {
            $detail = trim($process->getErrorOutput()."\n".$process->getOutput());

            return ['error' => "Tool [{$name}] crashed: ".mb_substr($detail, 0, 500)];
        }

        $decoded = json_decode($process->getOutput(), true);

        if (! is_array($decoded) || ! array_key_exists('ok', $decoded)) {
            return ['error' => "Tool [{$name}] produced unreadable output: ".mb_substr($process->getOutput(), 0, 500)];
        }

        return $decoded['ok'] ? $decoded['result'] : ['error' => (string) $decoded['error']];
    }

    /**
     * Include a generated tool file and pull out its $toolDefinition_* variable.
     * Files guard their function with function_exists(), so requiring them
     * repeatedly across refreshes is safe.
     */
    private function loadDefinitionFromFile(string $file): ?array
    {
        return (static function () use ($file): ?array {
            try {
                require $file;
            } catch (\Throwable) {
                return null;
            }

            foreach (get_defined_vars() as $variable => $value) {
                if (str_starts_with($variable, 'toolDefinition_') && is_array($value)) {
                    return $value;
                }
            }

            return null;
        })();
    }
}
