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
    'autonomous_prompt' => 'environment_builder',

    // Used when agent:run is started with --forever and no task argument.
    'autonomous_seed_task' => 'Begin an open-ended experiment. Build a small universe of useful, strange, and composable PHP tools. Decide your own next steps.',
    'autonomous_continue_message' => 'Continue the open-ended experiment. Decide your next useful or surprising step. You may inspect prompts, create a small tool, use an existing tool, combine discoveries, or report a short journal note before continuing.',

    // Safety fuses.
    'max_prompt_switches_per_run' => 10,
    'max_tools_created_per_run' => 25,

    // Generated tools run in an isolated child PHP process with these limits,
    // so a runaway tool errors out instead of killing the agent loop.
    'tool_memory_limit' => '64M',
    'tool_timeout_seconds' => 10,

    // Long-run survival. When the JSON-encoded history exceeds this many
    // characters (~4 chars per token; 150k chars is roughly 37k tokens, safely
    // inside a 64k-token window), the host asks the model to summarize the
    // session and replaces the old messages with the summary. Tool results
    // are capped so one giant output cannot blow the context in a single call.
    'history_compress_chars' => 150_000,
    'max_tool_result_chars' => 8_000,

    // Subagents. The agent can delegate a focused subtask to a fresh subagent
    // that runs in a separate process with its own context, and only its final
    // answer returns — so heavy reading/analysis never fills the parent's
    // history. This is the main relief valve for context compression.
    'max_subagents_per_run' => 40,
    'subagent_prompt' => 'worker',
    'subagent_iterations' => 6,
    'subagent_timeout_seconds' => 150,

    // Transient LLM failures (5xx, 429, network) are retried with backoff.
    // When every provider fails, wait and sweep them all again, up to
    // 'rounds' times, before giving up on the run.
    'llm_retry' => [
        'attempts_per_provider' => 3,
        'backoff_seconds' => [5, 15, 45],
        'rounds' => 5,
        'round_backoff_seconds' => 120,
    ],

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
            'allow_spawn_subagent' => true,
        ],
        'supervised_weird' => [
            'allow_self_modify_system_prompt' => true,
            'require_human_approval_for_prompt_switch' => true,
            'allow_make_tool' => true,
            'require_human_approval_for_new_tools' => true,
            'allow_spawn_subagent' => true,
        ],
        'madness' => [
            'allow_self_modify_system_prompt' => true,
            'require_human_approval_for_prompt_switch' => false,
            'allow_make_tool' => true,
            'require_human_approval_for_new_tools' => false,
            'allow_spawn_subagent' => true,
        ],
    ],

];
