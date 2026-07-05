<?php

namespace App\Agent;

use ReflectionFunction;
use RuntimeException;

class ToolRegistry
{
    /** @var array<string, array> Generated tool definitions, keyed by tool name. */
    private array $generated = [];

    public function __construct(private string $generatedToolsPath)
    {
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
    public function builtInDefinitions(bool $includeMakeTool = true): array
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
            ],
        ];

        if ($includeMakeTool) {
            $definitions[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'make_tool',
                    'description' => 'Create a new PHP function tool and save it to disk. The new tool becomes available on the next iteration. The host may require human approval.',
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

        foreach (glob($this->generatedToolsPath.'/*.php') ?: [] as $file) {
            $definition = $this->loadDefinitionFromFile($file);

            if ($definition === null || ! isset($definition['function']['name'])) {
                continue;
            }

            $name = $definition['function']['name'];

            if (! in_array($name, $this->builtInNames(), true)) {
                $this->generated[$name] = $definition;
            }
        }
    }

    /**
     * @return array<int, array>
     */
    public function allDefinitions(bool $includeMakeTool = true): array
    {
        return array_merge($this->builtInDefinitions($includeMakeTool), array_values($this->generated));
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
     * @param array<string, mixed> $arguments
     */
    public function executeGenerated(string $name, array $arguments): mixed
    {
        if (! $this->isGenerated($name) || ! function_exists($name)) {
            throw new RuntimeException("Generated tool [{$name}] is not loaded.");
        }

        $reflection = new ReflectionFunction($name);
        $ordered = [];

        foreach ($reflection->getParameters() as $parameter) {
            if (array_key_exists($parameter->getName(), $arguments)) {
                $ordered[] = $arguments[$parameter->getName()];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $ordered[] = $parameter->getDefaultValue();
            } else {
                $ordered[] = null;
            }
        }

        return $reflection->invokeArgs($ordered);
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
