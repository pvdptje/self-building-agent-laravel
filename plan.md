# Agent Resources Plan

Keep this small: prompts live as resource files, tools live as PHP files, and self-modifying behavior is controlled by simple mode toggles.

## Goals

- Store reusable system prompts in `resources/agent-prompts/`.
- Give the LLM tools to list, read, and search those prompts.
- Let the LLM suggest a system prompt switch.
- Give the LLM one bootstrap tool that can create new PHP tools.
- Load agent-made tools from disk each iteration.
- Make autonomous prompt switching easy to turn on or off.
- Log prompt and tool changes so weird runs are traceable.

## Prompt Files

Create prompt files like:

```text
resources/agent-prompts/
  creative_experiment.system.md
  toolmaker.system.md
  critic.system.md
```

Each file can stay simple:

```md
---
id: creative_experiment
title: Creative Experiment Agent
tags: [system, exploratory, tools]
---

You are an experimental self-improving PHP agent...
```

## Minimal Tools

Add these built-in tools:

- `make_tool`: create a new PHP function tool and save it to disk.
- `list_prompt_resources`: show available prompt files.
- `read_prompt_resource`: read one prompt by id.
- `search_prompt_resources`: search prompt titles, tags, and content.
- `suggest_system_prompt`: ask the host program to switch to a prompt.

The model can suggest changes, but the PHP runtime decides whether they actually happen.

## Tool Making

Start the agent with one bootstrap tool:

```text
make_tool
```

It accepts:

- `name`: snake_case tool name.
- `description`: what the tool does.
- `parameters_schema`: JSON schema for tool arguments.
- `code`: PHP function body only, with no `<?php` tag and no function wrapper.

Save generated tools here:

```text
storage/agent/tools/
```

Each generated tool file should include:

- The tool definition used by the LLM API.
- The PHP function that executes the tool.

Example shape:

```php
$toolDefinition_roll_dice = [
    'type' => 'function',
    'function' => [
        'name' => 'roll_dice',
        'description' => 'Roll dice and return the result.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'sides' => ['type' => 'integer'],
            ],
            'required' => ['sides'],
        ],
    ],
];

function roll_dice($sides) {
    return random_int(1, $sides);
}
```

On every loop:

1. Load built-in tools.
2. Load generated tools from `storage/agent/tools/`.
3. Send all available tools to the LLM.
4. If the LLM calls `make_tool`, write the new tool file.
5. The new tool becomes available on the next iteration.

Keep the first safety pass basic:

- Only allow snake_case tool names.
- Allow shell/process execution in generated tools; rely on mode selection, review, child-process memory limits, and tool timeouts rather than static function-name blocking.
- Do not allow generated tools to overwrite built-in tools.
- Optionally ask for approval before saving a tool outside `madness` mode.

## Runtime Modes

Use one config array:

```php
$agentModes = [
    'sane' => [
        'allow_self_modify_system_prompt' => false,
        'require_human_approval_for_prompt_switch' => true,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => true,
    ],
    'supervised_weird' => [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => true,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => true,
    ],
    'madness' => [
        'allow_self_modify_system_prompt' => true,
        'require_human_approval_for_prompt_switch' => false,
        'allow_make_tool' => true,
        'require_human_approval_for_new_tools' => false,
    ],
];
```

Default to `sane`.

## Prompt Switching

When the model calls `suggest_system_prompt`:

1. Validate that the prompt exists.
2. Check the current mode.
3. If self-modification is disabled, reject the switch.
4. If human approval is required, ask before switching.
5. If approval is not required, switch on the next iteration.

Do not let generated tool code directly overwrite the active system prompt.

## Logging

Append prompt switches to:

```text
storage/agent/prompt-lineage.jsonl
storage/agent/tool-lineage.jsonl
```

Each log entry should include:

```json
{
  "iteration": 7,
  "from": "creative_experiment",
  "to": "toolmaker",
  "reason": "The agent wants stronger tool-building instructions.",
  "approved": true,
  "mode": "supervised_weird"
}
```

Tool log entries should include:

```json
{
  "iteration": 4,
  "tool": "roll_dice",
  "description": "Roll dice and return the result.",
  "approved": true,
  "mode": "supervised_weird"
}
```

## Nice Safety Fuse

Add simple limits:

```php
'max_prompt_switches_per_run' => 3,
'max_tools_created_per_run' => 10,
```

This keeps the agent from spending the whole experiment repeatedly reinventing its own personality or making endless tiny tools.
