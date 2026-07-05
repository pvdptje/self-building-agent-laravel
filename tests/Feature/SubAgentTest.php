<?php

use App\Agent\LlmClient;
use App\Agent\PromptRepository;
use App\Agent\SubAgent;
use App\Agent\ToolMaker;
use App\Agent\ToolRegistry;
use Illuminate\Support\Facades\Http;

function subAgentWorld(string $workDir): array
{
    $promptsDir = $workDir.'/prompts';
    $toolsDir = $workDir.'/tools';
    mkdir($promptsDir, 0777, true);
    mkdir($toolsDir, 0777, true);

    file_put_contents($promptsDir.'/worker.system.md', "---\nid: worker\ntitle: Worker\ntags: [system]\n---\n\nYou are a focused subagent.\n");

    $registry = new ToolRegistry($toolsDir);

    return [$registry, new PromptRepository($promptsDir), $toolsDir];
}

function subAgentClient(): LlmClient
{
    return new LlmClient(
        ['test' => ['base_url' => 'https://llm.test/v1', 'model' => 'test-model', 'api_key' => 'test-key']],
        ['test'],
    );
}

beforeEach(function () {
    $this->workDir = sys_get_temp_dir().'/subagent-'.uniqid();
    mkdir($this->workDir, 0777, true);
});

afterEach(function () {
    exec(PHP_OS_FAMILY === 'Windows'
        ? 'rmdir /s /q "'.str_replace('/', '\\', $this->workDir).'"'
        : 'rm -rf '.escapeshellarg($this->workDir));
});

it('offers a subagent only read-only and generated tools, never self-modification', function () {
    [$registry, , $toolsDir] = subAgentWorld($this->workDir);

    (new ToolMaker($toolsDir, $registry->builtInNames()))
        ->make('echo_back', 'Echo the input.', [
            'type' => 'object',
            'properties' => ['text' => ['type' => 'string']],
            'required' => ['text'],
        ], 'return $text;');

    $registry->refreshGenerated();

    $names = array_map(fn ($t) => $t['function']['name'], $registry->subagentDefinitions());

    expect($names)->toContain('echo_back')
        ->and($names)->toContain('read_prompt_resource')
        ->and($names)->not->toContain('make_tool')
        ->and($names)->not->toContain('suggest_system_prompt')
        ->and($names)->not->toContain('spawn_subagent');
});

it('runs a subagent that uses a generated tool and returns a distilled answer', function () {
    [$registry, $prompts, $toolsDir] = subAgentWorld($this->workDir);

    (new ToolMaker($toolsDir, $registry->builtInNames()))
        ->make('count_words', 'Count words in text.', [
            'type' => 'object',
            'properties' => ['text' => ['type' => 'string']],
            'required' => ['text'],
        ], 'return str_word_count($text);');

    Http::fake(['llm.test/*' => Http::sequence()
        ->push(['choices' => [['message' => [
            'role' => 'assistant',
            'content' => null,
            'tool_calls' => [[
                'id' => 'c1',
                'type' => 'function',
                'function' => ['name' => 'count_words', 'arguments' => json_encode(['text' => 'one two three four'])],
            ]],
        ]]]])
        ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => 'The text has 4 words.']]]]),
    ]);

    $subagent = new SubAgent(subAgentClient(), $registry, $prompts);

    $answer = $subagent->run('How many words in "one two three four"?', 'worker', 4);

    expect($answer)->toBe('The text has 4 words.');
});

it('rejects a subagent attempt to use a forbidden tool', function () {
    [$registry, $prompts] = subAgentWorld($this->workDir);

    Http::fake(['llm.test/*' => Http::sequence()
        ->push(['choices' => [['message' => [
            'role' => 'assistant',
            'content' => null,
            'tool_calls' => [[
                'id' => 'c1',
                'type' => 'function',
                'function' => ['name' => 'make_tool', 'arguments' => '{}'],
            ]],
        ]]]])
        ->push(['choices' => [['message' => ['role' => 'assistant', 'content' => 'I cannot create tools.']]]]),
    ]);

    $subagent = new SubAgent(subAgentClient(), $registry, $prompts);

    $answer = $subagent->run('Try to make a tool.', 'worker', 4);

    expect($answer)->toBe('I cannot create tools.');

    Http::assertSent(function ($request) {
        foreach ($request->data()['messages'] ?? [] as $message) {
            if (($message['role'] ?? '') === 'tool' && str_contains($message['content'] ?? '', 'cannot use tool')) {
                return true;
            }
        }

        return false;
    });
});
