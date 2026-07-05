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

it('rejects blocked shell execution functions and eval', function (string $code) {
    $errors = $this->maker->validate('some_tool', [], $code);

    expect(implode(' ', $errors))->toContain('blocked function');
})->with([
    'return exec("ls");',
    'return shell_exec("ls");',
    'system("ls"); return 1;',
    'return passthru("ls");',
    'return eval("1;");',
]);

it('rejects backticks and php open tags in code', function () {
    expect($this->maker->validate('some_tool', [], 'return `ls`;'))->not->toBe([])
        ->and($this->maker->validate('some_tool', [], '<?php return 1;'))->not->toBe([]);
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
