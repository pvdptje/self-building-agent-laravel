<?php

use App\Agent\ToolMaker;
use App\Agent\ToolRegistry;

beforeEach(function () {
    $this->dir = sys_get_temp_dir().'/agent-tools-'.uniqid();
    mkdir($this->dir, 0777, true);

    $this->builtIns = ['make_tool', 'list_prompt_resources', 'suggest_system_prompt'];
    $this->maker = new ToolMaker($this->dir, $this->builtIns);
});

afterEach(function () {
    foreach (glob($this->dir.'/*') as $file) {
        unlink($file);
    }
    rmdir($this->dir);
});

it('rejects tool names that are not snake_case', function (string $name) {
    $errors = $this->maker->validate($name, [], 'return 1;');

    expect($errors)->not->toBe([]);
})->with(['RollDice', 'roll-dice', '1dice', 'roll dice', '']);

it('rejects overwriting a built-in tool', function () {
    $errors = $this->maker->validate('make_tool', [], 'return 1;');

    expect(implode(' ', $errors))->toContain('built-in');
});

it('blocks shell execution functions, eval, and backticks unless the mode allows them', function (string $code) {
    $errors = $this->maker->validate('some_tool', [], $code);

    expect(implode(' ', $errors))->toContain('blocked in this mode');
})->with([
    'return exec("ls");',
    'return shell_exec("ls");',
    'system("ls"); return 1;',
    'return passthru("ls");',
    'return proc_open("ls", [], $pipes);',
    'return eval("1;");',
    'return `ls`;',
]);

it('allows shell execution functions, eval, and backticks when the mode permits them', function (string $code) {
    $maker = new ToolMaker($this->dir, $this->builtIns, allowShellFunctions: true);

    expect($maker->validate('some_tool', [], $code))->toBe([]);
})->with([
    'return exec("ls");',
    'return shell_exec("ls");',
    'system("ls"); return 1;',
    'return passthru("ls");',
    'return eval("1;");',
    'return `ls`;',
]);

it('does not flag substrings that merely resemble shell function names', function () {
    expect($this->maker->validate('some_tool', [], 'return $systemInfo . my_exec_helper($x);'))->toBe([]);
});

it('rejects php open tags in code', function () {
    expect($this->maker->validate('some_tool', [], '<?php return 1;'))->not->toBe([]);
});

it('rejects code that does not compile', function () {
    $result = $this->maker->make('broken_tool_'.uniqid(), 'Broken.', [], 'return 1 +;');

    expect($result['ok'])->toBeFalse()
        ->and(implode(' ', $result['errors']))->toContain('does not compile');
});

it('writes a valid tool that the registry can load and execute', function () {
    $name = 'double_number_'.uniqid();

    $result = $this->maker->make($name, 'Double a number.', [
        'type' => 'object',
        'properties' => ['value' => ['type' => 'integer']],
        'required' => ['value'],
    ], 'return $value * 2;');

    expect($result['ok'])->toBeTrue()
        ->and(is_file($this->dir.'/'.$name.'.php'))->toBeTrue();

    $registry = new ToolRegistry($this->dir);
    $registry->refreshGenerated();

    expect($registry->isGenerated($name))->toBeTrue()
        ->and($registry->executeGenerated($name, ['value' => 21]))->toBe(42);

    $definitions = array_column(array_column($registry->allDefinitions(), 'function'), 'name');
    expect($definitions)->toContain($name);
});

it('rejects creating a tool with a name that already exists on disk', function () {
    $name = 'once_only_'.uniqid();

    $first = $this->maker->make($name, 'First.', [], 'return 1;');
    $second = $this->maker->validate($name, [], 'return 2;');

    expect($first['ok'])->toBeTrue()
        ->and(implode(' ', $second))->toContain('already exists');
});

it('replaces an existing tool when overwrite is requested', function () {
    $name = 'replace_me_'.uniqid();

    $first = $this->maker->make($name, 'Old version.', [], 'return "old";');

    // Simulate the host having loaded the tool file, which defines the
    // function in this process — overwrite must still be allowed then.
    require $this->dir.'/'.$name.'.php';

    $second = $this->maker->make($name, 'New version.', [], 'return "new";', overwrite: true);

    expect($first['ok'])->toBeTrue()
        ->and($second['ok'])->toBeTrue();

    $registry = new ToolRegistry($this->dir);
    $registry->refreshGenerated();

    expect($registry->executeGenerated($name, []))->toBe('new');
});

it('encodes a parameterless tool schema as a JSON object, not an array', function () {
    $name = 'coin_flip_'.uniqid();

    $result = $this->maker->make($name, 'Flip a coin.', [], 'return random_int(0, 1) === 1 ? "heads" : "tails";');

    expect($result['ok'])->toBeTrue();

    $registry = new ToolRegistry($this->dir);
    $registry->refreshGenerated();

    $definition = collect($registry->allDefinitions())->firstWhere('function.name', $name);

    expect(json_encode($definition))->toContain('"parameters":{"type":"object","properties":{}}');
});

it('encodes nested empty schema fragments as objects while keeping required a JSON array', function () {
    $name = 'pick_random_'.uniqid();

    $result = $this->maker->make($name, 'Pick random items.', [
        'type' => 'object',
        'properties' => [
            'items' => ['type' => 'array', 'description' => 'List of items.', 'items' => []],
        ],
        'required' => ['items'],
    ], 'return $items[array_rand($items)];');

    expect($result['ok'])->toBeTrue();

    $registry = new ToolRegistry($this->dir);
    $registry->refreshGenerated();

    $json = json_encode(collect($registry->allDefinitions())->firstWhere('function.name', $name));

    expect($json)->toContain('"items":{}')
        ->and($json)->toContain('"required":["items"]')
        ->and($json)->not->toContain('"items":[]');
});

it('normalizes broken schemas from tool files that already exist on disk', function () {
    $name = 'legacy_tool_'.uniqid();

    // A file written before schema normalization existed: empty arrays where
    // JSON Schema expects objects.
    file_put_contents($this->dir.'/'.$name.'.php', <<<PHP
<?php

\$toolDefinition_{$name} = [
    'type' => 'function',
    'function' => [
        'name' => '{$name}',
        'description' => 'Legacy tool with a broken schema.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'stuff' => ['type' => 'array', 'items' => []],
            ],
        ],
    ],
];

if (! function_exists('{$name}')) {
    function {$name}(\$stuff = null)
    {
        return \$stuff;
    }
}
PHP);

    $registry = new ToolRegistry($this->dir);
    $registry->refreshGenerated();

    $json = json_encode(collect($registry->allDefinitions())->firstWhere('function.name', $name));

    expect($json)->toContain('"items":{}')
        ->and($json)->not->toContain('"items":[]');
});

it('hoists misplaced required arrays from legacy tool properties', function () {
    $name = 'legacy_required_'.uniqid();

    file_put_contents($this->dir.'/'.$name.'.php', <<<PHP
<?php

\$toolDefinition_{$name} = [
    'type' => 'function',
    'function' => [
        'name' => '{$name}',
        'description' => 'Legacy tool with misplaced required metadata.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'numbers' => ['type' => 'array', 'items' => ['type' => 'number']],
                'required' => ['numbers'],
            ],
        ],
    ],
];

if (! function_exists('{$name}')) {
    function {$name}(\$numbers = null)
    {
        return \$numbers;
    }
}
PHP);

    $registry = new ToolRegistry($this->dir);
    $registry->refreshGenerated();

    $json = json_encode(collect($registry->allDefinitions())->firstWhere('function.name', $name));

    expect($json)->toContain('"required":["numbers"]')
        ->and($json)->not->toContain('"required":{"type"')
        ->and($json)->not->toContain('"properties":{"numbers":{"type":"array","items":{"type":"number"}},"required"');
});

it('caps generated tool definitions while preserving focused tools', function () {
    foreach (['old_alpha', 'old_beta', 'old_gamma'] as $name) {
        $this->maker->make($name.'_'.uniqid(), 'Temporary filler.', [], 'return 1;');
        usleep(1000);
    }

    $focused = 'focused_tool_'.uniqid();
    $this->maker->make($focused, 'The tool explicitly mentioned in recent history.', [], 'return "focused";');

    $registry = new ToolRegistry($this->dir);
    $registry->refreshGenerated();

    $names = array_map(
        fn (array $definition) => $definition['function']['name'],
        $registry->allDefinitions(maxGeneratedTools: 1, focusNames: [$focused]),
    );

    expect($names)->toContain($focused)
        ->and(array_filter($names, fn (string $name) => str_starts_with($name, 'focused_tool_') || str_starts_with($name, 'old_')))->toHaveCount(1);
});

it('turns a tool that exhausts memory into an error result instead of dying', function () {
    $name = 'memory_hog_'.uniqid();

    $this->maker->make($name, 'Eat all the memory.', [], '$x = []; while (true) { $x[] = str_repeat("a", 1_000_000); }');

    $registry = new ToolRegistry($this->dir, toolMemoryLimit: '32M', toolTimeoutSeconds: 20);
    $registry->refreshGenerated();

    $result = $registry->executeGenerated($name, []);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toContain('crashed');
});

it('kills a tool that runs past the timeout and reports it as an error', function () {
    $name = 'never_ends_'.uniqid();

    $this->maker->make($name, 'Loop forever.', [], 'while (true) { usleep(1000); } return 1;');

    $registry = new ToolRegistry($this->dir, toolTimeoutSeconds: 2);
    $registry->refreshGenerated();

    $result = $registry->executeGenerated($name, []);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toContain('longer than 2 seconds');
});

it('reports an exception thrown inside a tool as an error result', function () {
    $name = 'always_throws_'.uniqid();

    $this->maker->make($name, 'Throw.', [], 'throw new RuntimeException("boom goes the tool");');

    $registry = new ToolRegistry($this->dir);
    $registry->refreshGenerated();

    $result = $registry->executeGenerated($name, []);

    expect($result)->toHaveKey('error')
        ->and($result['error'])->toContain('boom goes the tool');
});

it('puts optional schema parameters after required ones in the signature', function () {
    $name = 'greet_person_'.uniqid();

    $result = $this->maker->make($name, 'Greet someone.', [
        'type' => 'object',
        'properties' => [
            'greeting' => ['type' => 'string'],
            'name' => ['type' => 'string'],
        ],
        'required' => ['name'],
    ], 'return ($greeting ?? "Hello")." ".$name;');

    expect($result['ok'])->toBeTrue();

    $registry = new ToolRegistry($this->dir);
    $registry->refreshGenerated();

    expect($registry->executeGenerated($name, ['name' => 'Ada']))->toBe('Hello Ada')
        ->and($registry->executeGenerated($name, ['name' => 'Ada', 'greeting' => 'Hi']))->toBe('Hi Ada');
});
