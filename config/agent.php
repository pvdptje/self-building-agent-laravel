<?php

return [

    // Which runtime mode to use by default. See 'modes' below.
    'mode' => env('AGENT_MODE', 'sane'),

    // Where reusable system prompts live.
    'prompts_path' => resource_path('agent-prompts'),

    // Where agent-generated tools are written and loaded from.
    'generated_tools_path' => storage_path('agent/tools'),

    // Where prompt/tool lineage logs are appended.
    'log_path' => storage_path('agent'),

    // The prompt the agent boots with.
    'default_prompt' => 'creative_experiment',
    'autonomous_prompt' => 'autonomous_universe',

    // Used when agent:run is started with --forever and no task argument.
    'autonomous_seed_task' => 'Begin an open-ended experiment. Build a small universe of useful, strange, and composable PHP tools. Decide your own next steps.',
    'autonomous_continue_message' => 'Continue the open-ended experiment. Decide your next useful or surprising step. You may inspect prompts, create a small tool, use an existing tool, combine discoveries, or report a short journal note before continuing.',

    // Safety fuses.
    'max_prompt_switches_per_run' => 3,
    'max_tools_created_per_run' => 10,

    // Generated tools run in an isolated child PHP process with these limits,
    // so a runaway tool errors out instead of killing the agent loop.
    'tool_memory_limit' => '64M',
    'tool_timeout_seconds' => 10,

    // LLM providers, tried in order. A provider with a missing key is skipped,
    // and a provider that errors is abandoned for the rest of the run.
    'provider_order' => ['deepseek', 'openai'],

    'providers' => [
        'deepseek' => [
            'base_url' => 'https://api.deepseek.com/v1',
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'api_key' => env('DEEPSEEK_API_KEY'),
        ],
        'openai' => [
            'base_url' => 'https://api.openai.com/v1',
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'api_key' => env('OPENAI_API_KEY'),
        ],
    ],

    'modes' => [
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
    ],

];
