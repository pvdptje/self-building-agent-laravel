<?php

namespace App\Console\Commands;

use App\Agent\AgentLogger;
use App\Agent\AgentRunner;
use App\Agent\LlmClient;
use App\Agent\PromptRepository;
use App\Agent\ToolMaker;
use App\Agent\ToolRegistry;
use Illuminate\Console\Command;

class AgentRun extends Command
{
    protected $signature = 'agent:run
        {task? : What the agent should work on}
        {--mode= : Runtime mode (sane, supervised_weird, madness)}
        {--prompt= : Starting system prompt id}
        {--iterations=8 : Maximum loop iterations}
        {--forever : Keep running until interrupted}';

    protected $description = 'Run the experimental self-improving agent loop';

    public function handle(): int
    {
        $config = config('agent');
        $mode = $this->option('mode') ?: $config['mode'];

        if (! isset($config['modes'][$mode])) {
            $this->error("Unknown mode [{$mode}]. Available: ".implode(', ', array_keys($config['modes'])));

            return self::FAILURE;
        }

        $registry = new ToolRegistry(
            $config['generated_tools_path'],
            $config['tool_memory_limit'],
            $config['tool_timeout_seconds'],
        );

        $runner = new AgentRunner(
            llm: new LlmClient(
                $config['providers'],
                $config['provider_order'],
                fn (string $message) => $this->warn($message),
                $config['llm_retry'],
            ),
            prompts: new PromptRepository($config['prompts_path']),
            registry: $registry,
            toolMaker: new ToolMaker($config['generated_tools_path'], $registry->builtInNames()),
            logger: new AgentLogger($config['log_path']),
            mode: $mode,
            modeConfig: $config['modes'][$mode],
            limits: [
                'max_prompt_switches_per_run' => $config['max_prompt_switches_per_run'],
                'max_tools_created_per_run' => $config['max_tools_created_per_run'],
                'autonomous_continue_message' => $config['autonomous_continue_message'],
                'history_compress_chars' => $config['history_compress_chars'],
                'max_tool_result_chars' => $config['max_tool_result_chars'],
            ],
            approve: fn (string $question) => $this->confirm($question),
            output: function (string $type, string $message) {
                match ($type) {
                    'iteration' => $this->components->twoColumnDetail("<fg=green;options=bold>{$message}</>", ''),
                    'thought' => $this->line("<fg=yellow>💭 {$message}</>"),
                    'tool_call' => $this->line("<fg=cyan>🔧 {$message}</>"),
                    'tool_result' => $this->line("<fg=gray>   ↳ {$message}</>"),
                    'proposal' => $this->line("<fg=magenta>🛠  {$message}</>"),
                    'switch' => $this->line("<fg=magenta;options=bold>🔀 {$message}</>"),
                    'system' => $this->line("<fg=blue>⏵ {$message}</>"),
                    default => $this->line($message),
                };
            },
        );

        $forever = (bool) $this->option('forever');
        $task = $this->argument('task') ?: $config['autonomous_seed_task'];
        $prompt = $this->option('prompt') ?: ($forever ? $config['autonomous_prompt'] : $config['default_prompt']);

        if (! $forever && $this->argument('task') === null) {
            $this->error('Please provide a task, or use --forever for an open-ended autonomous run.');

            return self::FAILURE;
        }

        $this->info("Mode: {$mode}");

        if ($forever) {
            $this->warn('Open-ended run started. Press Ctrl+C to stop.');
        }

        $answer = $runner->run(
            $task,
            $prompt,
            $forever ? null : max(1, (int) $this->option('iterations')),
            openEnded: $forever,
        );

        if (! $forever && $answer === null) {
            $this->warn('The agent ran out of iterations before giving a final answer.');
        }

        return self::SUCCESS;
    }
}
