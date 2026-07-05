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

function makeRunner(string $mode, array $modeConfig, bool $approve, string $workDir): AgentRunner
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
        limits: ['max_prompt_switches_per_run' => 3, 'max_tools_created_per_run' => 10],
        approve: fn (string $question) => $approve,
        output: fn (string $type, string $message) => null,
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
