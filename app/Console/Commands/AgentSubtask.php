<?php

namespace App\Console\Commands;

use App\Agent\LlmClient;
use App\Agent\PromptRepository;
use App\Agent\SubAgent;
use App\Agent\ToolRegistry;
use Illuminate\Console\Command;

/**
 * Internal command invoked by the parent agent's spawn_subagent tool. Runs one
 * subagent task in this separate process and prints only its answer as a single
 * JSON line to stdout. Diagnostics go to stderr so they never corrupt the line.
 */
class AgentSubtask extends Command
{
    protected $signature = 'agent:subtask
        {--task-file= : Path to a file containing the subtask instruction}
        {--prompt=worker : System prompt id for the subagent}
        {--iterations=6 : Maximum subagent iterations}';

    protected $description = 'Internal: run a single subagent task and print its answer as JSON';

    protected $hidden = true;

    public function handle(): int
    {
        $config = config('agent');
        $taskFile = $this->option('task-file');
        $task = $taskFile && is_file($taskFile) ? (string) file_get_contents($taskFile) : '';

        if (trim($task) === '') {
            $this->line(json_encode(['ok' => false, 'error' => 'Empty subtask.']));

            return self::SUCCESS;
        }

        $registry = new ToolRegistry(
            $config['generated_tools_path'],
            $config['tool_memory_limit'],
            $config['tool_timeout_seconds'],
        );

        try {
            $subagent = new SubAgent(
                llm: new LlmClient(
                    $config['providers'],
                    $config['provider_order'],
                    fn (string $message) => fwrite(STDERR, $message.PHP_EOL),
                    $config['llm_retry'],
                ),
                registry: $registry,
                prompts: new PromptRepository($config['prompts_path']),
                limits: ['max_tool_result_chars' => $config['max_tool_result_chars']],
            );

            $answer = $subagent->run(
                $task,
                (string) $this->option('prompt'),
                max(1, (int) $this->option('iterations')),
            );

            $this->line(json_encode(['ok' => true, 'answer' => $answer], JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {
            $this->line(json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_SLASHES));
        }

        return self::SUCCESS;
    }
}
