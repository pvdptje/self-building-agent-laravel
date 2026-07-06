<?php

namespace App\Console\Commands;

use App\Agent\AgentLogger;
use App\Agent\AgentRunner;
use App\Agent\LlmClient;
use App\Agent\PromptRepository;
use App\Agent\ToolMaker;
use App\Agent\ToolRegistry;
use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class AgentRun extends Command
{
    protected $signature = 'agent:run
        {task? : What the agent should work on}
        {--mode= : Runtime mode (sane, supervised_weird, curator, madness)}
        {--prompt= : Starting system prompt id}
        {--iterations=8 : Maximum loop iterations}
        {--forever : Keep running until interrupted}';

    protected $description = 'Run the experimental self-improving agent loop';

    /** How many consecutive checkpoints changed nothing but workspace notes. */
    private int $notesOnlyCheckpoints = 0;

    /** Consecutive notes-only checkpoints tolerated before the host pushes back. */
    private const STAGNATION_THRESHOLD = 3;

    public function handle(): int
    {
        $config = config('agent');
        ini_set('memory_limit', $config['host_memory_limit']);

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
            toolMaker: new ToolMaker(
                $config['generated_tools_path'],
                $registry->builtInNames(),
                (bool) ($config['modes'][$mode]['allow_shell_in_tools'] ?? false),
            ),
            logger: new AgentLogger($config['log_path']),
            mode: $mode,
            modeConfig: $config['modes'][$mode],
            limits: [
                'max_prompt_switches_per_run' => $config['max_prompt_switches_per_run'],
                'max_tools_created_per_run' => $config['max_tools_created_per_run'],
                'autonomous_continue_message' => $config['autonomous_continue_message'],
                'history_compress_chars' => $config['history_compress_chars'],
                'max_tool_result_chars' => $config['max_tool_result_chars'],
                'max_subagents_per_run' => $config['max_subagents_per_run'],
                'max_generated_tools_per_request' => $config['max_generated_tools_per_request'],
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
            spawnSubagent: fn (string $task) => $this->runSubagent($config, $task),
            checkpoint: $this->option('forever')
                ? fn (int $iteration): ?string => $this->gitCheckpoint($config, $iteration)
                : null,
            askHuman: function (string $question, string $context): ?string {
                if ($context !== '') {
                    $this->line("<fg=magenta>Human input requested:</> {$context}");
                }

                return $this->ask($question);
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

    /**
     * Snapshot the agent's work to git at an open-ended checkpoint. Stages
     * tracked changes plus the generated tools and workspace markdown (roadmap,
     * digests), which are gitignored, then commits to the current branch and
     * pushes it to origin. The noisy lineage .jsonl and any binary/SQLite
     * workspace files are left out.
     *
     * Every git failure is swallowed and reported as a warning: a checkpoint
     * that cannot commit or push must never interrupt the run.
     *
     * Returns a note for the agent when the run looks stagnant: several
     * consecutive checkpoints whose only changes are workspace notes mean the
     * agent is polishing its journal instead of building.
     *
     * @param array<string, mixed> $config
     */
    private function gitCheckpoint(array $config, int $iteration): ?string
    {
        $root = base_path();

        try {
            $this->runGit(['add', '-A'], $root);
            $this->runGit(['add', '-f', '--', $config['generated_tools_path']], $root);

            $workspace = storage_path('agent/workspace');
            $markdown = glob($workspace.'/*.md') ?: [];

            if ($markdown !== []) {
                $this->runGit(array_merge(['add', '-f', '--'], $markdown), $root);
            }

            // Nothing staged? Skip the commit so a no-op checkpoint is silent
            // instead of erroring with "nothing to commit".
            $stagedFiles = array_values(array_filter(explode(
                "\n",
                trim($this->runGit(['diff', '--cached', '--name-only'], $root)->getOutput())
            )));

            if ($stagedFiles === []) {
                return $this->trackStagnation(notesOnly: true);
            }

            $notesOnly = array_filter(
                $stagedFiles,
                fn (string $file) => ! (str_ends_with($file, '.md') && str_contains($file, 'agent/workspace'))
            ) === [];

            $commit = $this->runGit(
                ['commit', '--no-verify', '-m', "agent checkpoint: iteration {$iteration}"],
                $root
            );

            if (! $commit->isSuccessful()) {
                $this->warn('Checkpoint commit failed: '.trim($commit->getErrorOutput() ?: $commit->getOutput()));

                return null;
            }

            $this->line("<fg=blue>⏵ Checkpoint committed at iteration {$iteration}.</>");

            // Publish the checkpoint. A push failure (offline, rejected,
            // no upstream) is a warning, never fatal — the commit is safe
            // locally and the next checkpoint will try to push again.
            $push = $this->runGit(['push', 'origin', 'HEAD'], $root);

            if ($push->isSuccessful()) {
                $this->line("<fg=blue>⏵ Checkpoint pushed to origin.</>");
            } else {
                $this->warn('Checkpoint push failed (commit is saved locally): '.trim($push->getErrorOutput() ?: $push->getOutput()));
            }

            return $this->trackStagnation($notesOnly);
        } catch (\Throwable $e) {
            $this->warn('Checkpoint skipped (git error): '.$e->getMessage());

            return null;
        }
    }

    /**
     * Count consecutive checkpoints that changed nothing but workspace notes
     * and, past the threshold, hand the agent an escalating course correction.
     */
    private function trackStagnation(bool $notesOnly): ?string
    {
        if (! $notesOnly) {
            $this->notesOnlyCheckpoints = 0;

            return null;
        }

        $this->notesOnlyCheckpoints++;

        if ($this->notesOnlyCheckpoints < self::STAGNATION_THRESHOLD) {
            return null;
        }

        $this->warn("Stagnation: {$this->notesOnlyCheckpoints} consecutive checkpoints changed only workspace notes.");

        return "Stagnation warning: your last {$this->notesOnlyCheckpoints} checkpoints changed nothing except workspace notes "
            .'(ROADMAP.md and similar). Editing notes is not progress. Your next segment must produce a verifiable capability '
            .'change: create a tool, fix or improve an existing tool file, or fetch and process something new from outside. '
            .'Do not touch ROADMAP.md again until you have done one of those.';
    }

    /**
     * Run a git command in the given directory and return the finished process.
     *
     * @param array<int, string> $args
     */
    private function runGit(array $args, string $cwd): Process
    {
        $process = new Process(array_merge(['git'], $args), $cwd);
        $process->setTimeout(60);
        $process->run();

        return $process;
    }

    /**
     * Run one subagent task in a separate process and return its answer.
     *
     * @param array<string, mixed> $config
     * @return array{answer?: string, error?: string}
     */
    private function runSubagent(array $config, string $task): array
    {
        $taskFile = tempnam(sys_get_temp_dir(), 'subagent_task_');
        file_put_contents($taskFile, $task);

        try {
            $process = new Process([
                PHP_BINARY,
                base_path('artisan'),
                'agent:subtask',
                '--task-file='.$taskFile,
                '--prompt='.$config['subagent_prompt'],
                '--iterations='.$config['subagent_iterations'],
            ]);
            $process->setTimeout($config['subagent_timeout_seconds']);

            try {
                $process->run();
            } catch (ProcessTimedOutException) {
                return ['error' => 'The subagent timed out.'];
            }

            $lines = array_values(array_filter(array_map('trim', explode("\n", $process->getOutput()))));
            $decoded = json_decode(end($lines) ?: '', true);

            if (! is_array($decoded)) {
                return ['error' => 'The subagent produced no readable answer.'];
            }

            return ($decoded['ok'] ?? false)
                ? ['answer' => (string) ($decoded['answer'] ?? '')]
                : ['error' => (string) ($decoded['error'] ?? 'The subagent failed.')];
        } finally {
            @unlink($taskFile);
        }
    }
}
