<?php

use App\Agent\PromptRepository;

beforeEach(function () {
    $this->dir = sys_get_temp_dir().'/agent-prompts-'.uniqid();
    mkdir($this->dir, 0777, true);

    file_put_contents($this->dir.'/alpha.system.md', <<<'MD'
---
id: alpha
title: Alpha Agent
tags: [system, exploration]
---

You are alpha. You love spelunking through code.
MD);

    file_put_contents($this->dir.'/beta.system.md', <<<'MD'
---
id: beta
title: Beta Reviewer
tags: [system, review]
---

You are beta. You review tools carefully.
MD);

    file_put_contents($this->dir.'/broken.md', "No frontmatter here at all.\n");

    $this->repo = new PromptRepository($this->dir);
});

afterEach(function () {
    foreach (glob($this->dir.'/*') as $file) {
        unlink($file);
    }
    rmdir($this->dir);
});

it('lists prompts with parsed frontmatter and skips files without frontmatter', function () {
    $all = $this->repo->all();

    expect($all)->toHaveCount(2)
        ->and(array_column($all, 'id'))->toBe(['alpha', 'beta'])
        ->and($all[0]['title'])->toBe('Alpha Agent')
        ->and($all[0]['tags'])->toBe(['system', 'exploration']);
});

it('finds a prompt by id including its body', function () {
    $prompt = $this->repo->find('beta');

    expect($prompt)->not->toBeNull()
        ->and($prompt['body'])->toContain('review tools carefully');
});

it('returns null for an unknown prompt id', function () {
    expect($this->repo->find('nope'))->toBeNull();
});

it('searches across title, tags, and body case-insensitively', function () {
    expect(array_column($this->repo->search('REVIEW'), 'id'))->toBe(['beta'])
        ->and(array_column($this->repo->search('spelunking'), 'id'))->toBe(['alpha'])
        ->and($this->repo->search('zebra'))->toBe([]);
});
