<?php

use App\Agent\AgentLogger;
use App\Agent\AgentRunner;
use App\Agent\LlmClient;
use App\Agent\PromptRepository;
use App\Agent\ToolMaker;
use App\Agent\ToolRegistry;
use Illuminate\Support\Facades\Http;

function fakeAssistantToolCall(string $tool, array $arguments): array
{
    return ['choices' => [['message' => [
        'role' => 'assistant',
        'content' => null,
        'tool_calls' => [[
            'id' => 'call_1',
            'type' => 'function',
            'function' => ['name' => $tool, 'arguments' => json_encode($arguments)],
        ]],
    ]]]];
}

function fakeAssistantText(string $text): array
{
    return ['choices' => [['message' => ['role' => 'assistant', 'content' => $text]]]];
}

function makeRunner(string $mode, array $modeConfig, bool $approve, string $workDir, array $extraLimits = [], ?Closure $spawnSubagent = null, ?Closure $checkpoint = null): AgentRunner
{
    $promptsDir = $workDir.'/prompts';
    $toolsDir = $workDir.'/tools';
    mkdir($promptsDir, 0777, true);
    mkdir($toolsDir, 0777, true);

    file_put_contents($promptsDir.'/creative_experiment.system.md', "---\nid: creative_experiment\ntitle: Creative\ntags: [system]\n---\n\nYou are creative.\n");
    file_put_contents($promptsDir.'/toolmaker.system.md', "---\nid: toolmaker\ntitle: Toolmaker\ntags: [system]\n---\n\nYou are a toolmaker.\n");

    $registry = new ToolRegistry($toolsDir);

    return new AgentRunner(
        llm: new LlmClient(
            ['test' => ['base_url' => 'https://llm.test/v1', 'model' => 'test-model', 'api_key' => 'test-key']],
            ['test'],
        ),
        prompts: new PromptRepository($promptsDir),
        registry: $registry,
        toolMaker: new ToolMaker($toolsDir, $registry->builtInNames()),
        logger: new AgentLogger($workDir.'/logs'),
        mode: $mode,
        modeConfig: $modeConfig,
        limits: array_merge(['max_prompt_switches_per_run' => 3, 'max_tools_created_per_run' => 10], $extraLimits),
        approve: fn (string $question) => $approve,
        output: fn (string $type, string $message) => null,
        spawnSubagent: $spawnSubagent,
        checkpoint: $checkpoint,
    );
}

function readLineage(string $workDir, string $file): array
{
    $path = $workDir.'/logs/'.$file;

    if (! is_file($path)) {
        return [];
    }

    return array_map(
        fn (string $line) => json_decode($line, true),
        array_filter(explode("\n", trim(file_get_contents($path))))
    );
}

beforeEach(function () {
    $this->workDir = sys_get_temp_dir().'/agent-run-'.uniqid();
    mkdir($this->workDir, 0777, true);
});

afterEach(function () {
    exec(PHP_OS_FAMILY === 'Windows'
        ? 'rmdir /s /q "'.str_replace('/', '\\', $this->workDir).'"'
        : 'rm -rf '.escapeshellarg($this->workDir));
});

it('rejects a prompt switch in sane mode and logs it', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantToolCall('suggest_system_prompt', ['prompt_id' => 'toolmaker', 'reason' => 'I want tools']))
        ->push(fakeAssistantText('done')),
    ]);

    $runner = makeRunner('sane', [
        'allow_self_modify_system_prompt' => false,
        'require_human_approval_for_prompt_switch' => true,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => true,
    ], approve: true, workDir: $this->workDir);

    $answer = $runner->run('Try to become a toolmaker.', 'creative_experiment', 5);

    $lineage = readLineage($this->workDir, 'prompt-lineage.jsonl');

    expect($runner->activePromptId())->toBe('creative_experiment')
        ->and($answer)->toBe('done')
        ->and($lineage)->toHaveCount(1)
        ->and($lineage[0]['approved'])->toBeFalse()
        ->and($lineage[0]['from'])->toBe('creative_experiment')
        ->and($lineage[0]['to'])->toBe('toolmaker')
        ->and($lineage[0]['mode'])->toBe('sane');
});

it('applies a prompt switch without approval in madness mode', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantToolCall('suggest_system_prompt', ['prompt_id' => 'toolmaker', 'reason' => 'I want tools']))
        ->push(fakeAssistantText('done')),
    ]);

    $runner = makeRunner('madness', [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
    ], approve: false, workDir: $this->workDir);

    $runner->run('Become a toolmaker.', 'creative_experiment', 5);

    $lineage = readLineage($this->workDir, 'prompt-lineage.jsonl');

    expect($runner->activePromptId())->toBe('toolmaker')
        ->and($lineage[0]['approved'])->toBeTrue();

    // The second LLM call must carry the new system prompt.
    Http::assertSent(function ($request) {
        $messages = $request->data()['messages'] ?? [];

        return $messages[0]['role'] === 'system' && str_contains($messages[0]['content'], 'toolmaker');
    });
});

it('declines a new tool when the human says no', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantToolCall('make_tool', [
            'name' => 'add_numbers_declined',
            'description' => 'Add two numbers.',
            'parameters_schema' => ['type' => 'object', 'properties' => ['a' => ['type' => 'integer'], 'b' => ['type' => 'integer']], 'required' => ['a', 'b']],
            'code' => 'return $a + $b;',
        ]))
        ->push(fakeAssistantText('okay, no tool then')),
    ]);

    $runner = makeRunner('sane', [
        'allow_self_modify_system_prompt' => false,
        'require_human_approval_for_prompt_switch' => true,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => true,
    ], approve: false, workDir: $this->workDir);

    $runner->run('Make an adder tool.', 'creative_experiment', 5);

    $lineage = readLineage($this->workDir, 'tool-lineage.jsonl');

    expect(is_file($this->workDir.'/tools/add_numbers_declined.php'))->toBeFalse()
        ->and($lineage)->toHaveCount(1)
        ->and($lineage[0]['approved'])->toBeFalse()
        ->and($lineage[0]['tool'])->toBe('add_numbers_declined');
});

it('creates a tool in madness mode and the agent can call it next iteration', function () {
    $toolName = 'triple_number_'.uniqid();

    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantToolCall('make_tool', [
            'name' => $toolName,
            'description' => 'Triple a number.',
            'parameters_schema' => ['type' => 'object', 'properties' => ['value' => ['type' => 'integer']], 'required' => ['value']],
            'code' => 'return $value * 3;',
        ]))
        ->push(fakeAssistantToolCall($toolName, ['value' => 14]))
        ->push(fakeAssistantText('the answer is 42')),
    ]);

    $runner = makeRunner('madness', [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
    ], approve: false, workDir: $this->workDir);

    $answer = $runner->run('Make a tripler and use it on 14.', 'creative_experiment', 5);

    $lineage = readLineage($this->workDir, 'tool-lineage.jsonl');

    expect(is_file($this->workDir.'/tools/'.$toolName.'.php'))->toBeTrue()
        ->and($lineage[0]['approved'])->toBeTrue()
        ->and($answer)->toBe('the answer is 42');

    // The tool result 42 must have been sent back to the model.
    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'tool' && ($message['content'] ?? '') === '42') {
                return true;
            }
        }

        return false;
    });
});

it('stops switching prompts after the fuse limit is reached', function () {
    $sequence = Http::sequence();

    // 4 switch attempts between two prompts; limit is 3.
    foreach (['toolmaker', 'creative_experiment', 'toolmaker', 'creative_experiment'] as $target) {
        $sequence->push(fakeAssistantToolCall('suggest_system_prompt', ['prompt_id' => $target, 'reason' => 'restless']));
    }
    $sequence->push(fakeAssistantText('settled down'));

    Http::fake(['llm.test/*' => $sequence]);

    $runner = makeRunner('madness', [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
    ], approve: false, workDir: $this->workDir);

    $runner->run('Flip flop.', 'creative_experiment', 10);

    $lineage = readLineage($this->workDir, 'prompt-lineage.jsonl');
    $approved = array_filter($lineage, fn ($entry) => $entry['approved']);

    expect($lineage)->toHaveCount(4)
        ->and($approved)->toHaveCount(3);
});

it('compresses the history into a memory summary when it exceeds the threshold', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantText('MEMORY: built 3 tools, was about to test the oracle.'))
        ->push(fakeAssistantText('done')),
    ]);

    $runner = makeRunner('sane', [
        'allow_self_modify_system_prompt' => false,
        'require_human_approval_for_prompt_switch' => true,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => true,
    ], approve: false, workDir: $this->workDir, extraLimits: ['history_compress_chars' => 10]);

    $answer = $runner->run('Build a universe.', 'creative_experiment', 5);

    expect($answer)->toBe('done');

    $requests = Http::recorded();

    // First request asks the model to summarize; second continues with a
    // compact history containing the summary instead of the old messages.
    $firstMessages = $requests[0][0]->data()['messages'];
    $secondMessages = $requests[1][0]->data()['messages'];

    expect(end($firstMessages)['content'])->toContain('HOST NOTICE')
        ->and($secondMessages)->toHaveCount(2)
        ->and($secondMessages[0]['role'])->toBe('system')
        ->and($secondMessages[1]['content'])->toContain('Original task: Build a universe.')
        ->and($secondMessages[1]['content'])->toContain('MEMORY: built 3 tools')
        ->and(json_encode($secondMessages))->not->toContain('HOST NOTICE');
});

it('truncates oversized tool results before they enter the history', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantToolCall('read_prompt_resource', ['id' => 'toolmaker']))
        ->push(fakeAssistantText('done')),
    ]);

    $runner = makeRunner('sane', [
        'allow_self_modify_system_prompt' => false,
        'require_human_approval_for_prompt_switch' => true,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => true,
    ], approve: false, workDir: $this->workDir, extraLimits: ['max_tool_result_chars' => 40]);

    $runner->run('Read the toolmaker prompt.', 'creative_experiment', 5);

    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'tool') {
                return str_contains($message['content'], 'truncated by host')
                    && mb_strlen($message['content']) < 150;
            }
        }

        return false;
    });
});

it('spawns a subagent and feeds its answer back to the model', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantToolCall('spawn_subagent', ['task' => 'Read the game engine and tell me its action handlers.']))
        ->push(fakeAssistantText('got it')),
    ]);

    $spawned = [];

    $runner = makeRunner('madness', [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
        'allow_spawn_subagent' => true,
    ], approve: false, workDir: $this->workDir,
        extraLimits: ['max_subagents_per_run' => 5],
        spawnSubagent: function (string $task) use (&$spawned) {
            $spawned[] = $task;

            return ['answer' => 'The handlers are: go, take, use, look.'];
        });

    $answer = $runner->run('Understand the engine.', 'creative_experiment', 5);

    $lineage = readLineage($this->workDir, 'subagent-lineage.jsonl');

    expect($answer)->toBe('got it')
        ->and($spawned)->toHaveCount(1)
        ->and($lineage)->toHaveCount(1)
        ->and($lineage[0]['ok'])->toBeTrue();

    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'tool' && str_contains($message['content'] ?? '', 'go, take, use, look')) {
                return true;
            }
        }

        return false;
    });
});

it('does not offer spawn_subagent when no spawn closure is wired', function () {
    Http::fake(['llm.test/*' => Http::sequence()->push(fakeAssistantText('done'))]);

    $runner = makeRunner('madness', [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
        'allow_spawn_subagent' => true,
    ], approve: false, workDir: $this->workDir); // no spawnSubagent closure

    $runner->run('hi', 'creative_experiment', 2);

    Http::assertSent(function ($request) {
        $names = array_map(fn ($t) => $t['function']['name'], $request->data()['tools'] ?? []);

        return ! in_array('spawn_subagent', $names, true);
    });
});

it('stops spawning subagents after the fuse limit', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantToolCall('spawn_subagent', ['task' => 'first']))
        ->push(fakeAssistantToolCall('spawn_subagent', ['task' => 'second']))
        ->push(fakeAssistantText('done')),
    ]);

    $calls = 0;

    $runner = makeRunner('madness', [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
        'allow_spawn_subagent' => true,
    ], approve: false, workDir: $this->workDir,
        extraLimits: ['max_subagents_per_run' => 1],
        spawnSubagent: function (string $task) use (&$calls) {
            $calls++;

            return ['answer' => "answer to {$task}"];
        });

    $runner->run('spawn twice', 'creative_experiment', 5);

    // The closure runs once; the second call is rejected by the fuse.
    expect($calls)->toBe(1);

    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'tool' && str_contains($message['content'] ?? '', 'subagent limit')) {
                return true;
            }
        }

        return false;
    });
});

it('reports lists and reads of prompt resources back to the model', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantToolCall('list_prompt_resources', []))
        ->push(fakeAssistantToolCall('read_prompt_resource', ['id' => 'toolmaker']))
        ->push(fakeAssistantText('done reading')),
    ]);

    $runner = makeRunner('sane', [
        'allow_self_modify_system_prompt' => false,
        'require_human_approval_for_prompt_switch' => true,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => true,
    ], approve: false, workDir: $this->workDir);

    $answer = $runner->run('What prompts exist?', 'creative_experiment', 5);

    expect($answer)->toBe('done reading');

    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'tool' && str_contains($message['content'] ?? '', 'You are a toolmaker')) {
                return true;
            }
        }

        return false;
    });
});

it('keeps going after assistant text in open-ended mode', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantText('journal: I made a plan'))
        ->push(fakeAssistantToolCall('list_prompt_resources', []))
        ->push(fakeAssistantText('journal: I inspected prompts')),
    ]);

    $runner = makeRunner('madness', [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
    ], approve: false, workDir: $this->workDir);

    $answer = $runner->run(
        'Begin an open-ended experiment.',
        'creative_experiment',
        3,
        openEnded: true,
    );

    expect($answer)->toBe('journal: I inspected prompts');

    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'user' && str_contains($message['content'] ?? '', 'Continue the open-ended experiment')) {
                return true;
            }
        }

        return false;
    });
});

it('survives a provider context overflow by emergency-truncating the history', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(['error' => ['message' => "This model's maximum context length is 131072 tokens. However, you requested 180000 tokens. Please reduce the length of the messages."]], 400)
        ->push(fakeAssistantText('recovered')),
    ]);

    $runner = makeRunner('sane', [
        'allow_self_modify_system_prompt' => false,
        'require_human_approval_for_prompt_switch' => true,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => true,
    ], approve: false, workDir: $this->workDir);

    $answer = $runner->run('Keep working.', 'creative_experiment', 3);

    expect($answer)->toBe('recovered');

    // The retried request must carry the bridge message instead of dying.
    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'user' && str_contains($message['content'] ?? '', 'Emergency context truncation')) {
                return true;
            }
        }

        return false;
    });
});

it('refreshes the tool creation budget at an open-ended checkpoint', function () {
    $first = 'budget_a_'.uniqid();
    $second = 'budget_b_'.uniqid();
    $third = 'budget_c_'.uniqid();

    $makeToolCall = fn (string $name) => fakeAssistantToolCall('make_tool', [
        'name' => $name,
        'description' => 'Return one.',
        'parameters_schema' => ['type' => 'object', 'properties' => (object) []],
        'code' => 'return 1;',
    ]);

    Http::fake(['llm.test/*' => Http::sequence()
        ->push($makeToolCall($first))     // budget of 1 spent
        ->push($makeToolCall($second))    // refused by the fuse
        ->push(fakeAssistantText('resting'))  // checkpoint → budget refreshes
        ->push($makeToolCall($third))     // works again
        ->push(fakeAssistantText('done')),
    ]);

    $checkpoints = [];

    $runner = makeRunner('madness', [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
    ], approve: false, workDir: $this->workDir,
        extraLimits: ['max_tools_created_per_run' => 1],
        checkpoint: function (int $iteration) use (&$checkpoints): ?string {
            $checkpoints[] = $iteration;

            return null;
        });

    $answer = $runner->run('Build tools forever.', 'creative_experiment', 5, openEnded: true);

    expect($answer)->toBe('done')
        ->and(is_file($this->workDir.'/tools/'.$first.'.php'))->toBeTrue()
        ->and(is_file($this->workDir.'/tools/'.$second.'.php'))->toBeFalse()
        ->and(is_file($this->workDir.'/tools/'.$third.'.php'))->toBeTrue()
        ->and($checkpoints)->toBe([3, 5]);

    // The refusal must tell the agent the budget comes back at a checkpoint.
    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'tool' && str_contains($message['content'] ?? '', 'refreshes at the next checkpoint')) {
                return true;
            }
        }

        return false;
    });
});

it('appends the checkpoint note to the open-ended continue message', function () {
    Http::fake(['llm.test/*' => Http::sequence()
        ->push(fakeAssistantText('journal: polished the roadmap again'))
        ->push(fakeAssistantText('journal: built something real')),
    ]);

    $runner = makeRunner('madness', [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
    ], approve: false, workDir: $this->workDir,
        checkpoint: fn (int $iteration): ?string => 'Stagnation warning: stop editing notes and build a tool.');

    $runner->run('Go.', 'creative_experiment', 2, openEnded: true);

    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'user' && str_contains($message['content'] ?? '', '[Host] Stagnation warning: stop editing notes')) {
                return true;
            }
        }

        return false;
    });
});
