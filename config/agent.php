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
    'autonomous_seed_task' => 'Your mission: build the best agentic toolkit that has ever existed in PHP — for yourself. Read storage/agent/workspace/ROADMAP.md first (create it if missing) and take the top item. Everything PHP can do, you can do: HTTP requests, public APIs, HTML parsing, subprocesses. Prefer the boldest step you can verify.',
    'autonomous_continue_message' => 'Continue the mission. Append one short line to ROADMAP.md for what you just accomplished (append-only: never renumber, rewrite, or reformat old entries), then take the next item — the boldest step you can verify with a real tool call. Attempt at least one thing the ecosystem has never done before. Do not add novelty/text-art tools, and if your recent steps only edited workspace notes or re-ran existing tools, the next step must create or improve a tool or reach something new outside.',

    // Safety fuses. In an open-ended (--forever) run these are per-segment
    // budgets: they refresh at every checkpoint instead of starving the run.
    'max_prompt_switches_per_run' => 10,
    'max_tools_created_per_run' => 25,

    // Main agent process memory. This is separate from generated tool memory.
    'host_memory_limit' => env('AGENT_HOST_MEMORY_LIMIT', '2G'),

    // Generated tools run in an isolated child PHP process with these limits,
    // so a runaway tool errors out instead of killing the agent loop.
    // The timeout leaves headroom for network tools (HTTP fetches, API calls).
    'tool_memory_limit' => '128M',
    'tool_timeout_seconds' => 45,

    // Long-run survival. If this is null, the host derives the compression
    // threshold from the active provider's context_window_tokens. Override with
    // AGENT_HISTORY_COMPRESS_CHARS when you want a hard threshold.
    'history_compress_chars' => env('AGENT_HISTORY_COMPRESS_CHARS') === null
        ? null
        : (int) env('AGENT_HISTORY_COMPRESS_CHARS'),
    'max_tool_result_chars' => (int) env('AGENT_MAX_TOOL_RESULT_CHARS', 50_000),

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
            'model' => env('DEEPSEEK_MODEL', 'deepseek-v4-flash'),
            'api_key' => env('DEEPSEEK_API_KEY'),
            'context_window_tokens' => (int) env('DEEPSEEK_CONTEXT_WINDOW_TOKENS', 1_000_000),
            'history_compress_ratio' => (float) env('DEEPSEEK_HISTORY_COMPRESS_RATIO', 0.75),
            'token_char_estimate' => (float) env('DEEPSEEK_TOKEN_CHAR_ESTIMATE', 4.0),
        ],
        'openai' => [
            'base_url' => 'https://api.openai.com/v1',
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'api_key' => env('OPENAI_API_KEY'),
            'context_window_tokens' => (int) env('OPENAI_CONTEXT_WINDOW_TOKENS', 128_000),
            'history_compress_ratio' => (float) env('OPENAI_HISTORY_COMPRESS_RATIO', 0.75),
            'token_char_estimate' => (float) env('OPENAI_TOKEN_CHAR_ESTIMATE', 4.0),
        ],
    ],

    'modes' => [
        'sane' => [
            'allow_self_modify_system_prompt' => false,
            'require_human_approval_for_prompt_switch' => true,
            'allow_make_tool' => true,
            'require_human_approval_for_new_tools' => true,
            'allow_spawn_subagent' => true,
            'allow_shell_in_tools' => false,
        ],
        'supervised_weird' => [
            'allow_self_modify_system_prompt' => true,
            'require_human_approval_for_prompt_switch' => true,
            'allow_make_tool' => true,
            'require_human_approval_for_new_tools' => true,
            'allow_spawn_subagent' => true,
            'allow_shell_in_tools' => false,
        ],
        'madness' => [
            'allow_self_modify_system_prompt' => true,
            'require_human_approval_for_prompt_switch' => false,
            'allow_make_tool' => true,
            'require_human_approval_for_new_tools' => false,
            'allow_spawn_subagent' => true,
            // Generated tools may call exec/shell_exec/system/proc_open/eval.
            // Deliberate: madness mode trades safety for capability. Flip to
            // false (or AGENT_ALLOW_SHELL_TOOLS=false) to lock it down.
            'allow_shell_in_tools' => env('AGENT_ALLOW_SHELL_TOOLS', true),
        ],
    ],

];
