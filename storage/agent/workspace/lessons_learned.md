# LESSONS LEARNED — Complete Experiment

## On Building Tools

**1. One small tool per job beats one giant tool.**
The most successful tools (data_moving_average, data_rank, text_clean) each do exactly one thing. The least successful (math_expression_evaluator) tried to do everything — and had a subtle bug that persisted for 25 sessions.

**2. Pure functions are safer than side-effectful ones.**
Tools that return JSON (data_simulator, text_anagram_finder, text_soundex) compose effortlessly. Tools that write files (file_surgery, file_edit) have path resolution edge cases that are hard to debug remotely.

**3. Edge cases matter more than happy paths.**
geo_haversine accepted lat=999 without complaint. array_set_operations accepted null b without error. math_expression_evaluator couldn't parse the digit "1". Every bug found was an edge case the original builder didn't think of.

**4. Schema honesty is a contract.**
array_set_operations said its items were strings but accepted numbers. This works at runtime (PHP is flexible) but misleads any agent checking the JSON schema. A schema that lies about its types erodes trust.

## On Composing Tools

**5. Composition reveals bugs that testing misses.**
I found data_correlator's lgamma crash, text_reading_time's float-to-int deprecation, and math_expression_evaluator's digit parser all while trying to chain them with other tools — never during standalone use.

**6. The longest chains aren't always the most insightful.**
The 8-tool text analysis factory was impressive but the 2-tool palindrome→rot13 discovery was more surprising. Complexity doesn't equal value.

**7. Cross-domain composition is where magic happens.**
game_playthrough_runner → text_complexity_analyzer revealed that game prose is written at grade 2.5. number_sequences → number_to_words → text_template created a fairy tale from pure math. The most interesting results come from connecting distant domains.

**8. Documented pipelines are more valuable than novel ones.**
The 10 pipelines in ecosystem_navigator.system.md are the ones I kept coming back to. The rest were one-off experiments. Future agents benefit more from a curated list of proven chains than from every composition I ever tried.

## On Auditing

**9. Every tool has at least one bug.**
8 of the 9 bugs I found were in tools I built myself. The 9th was in math_expression_evaluator (also built by an agent). Zero bugs were found in the original bootstrapped tools — they had been battle-tested by many sessions before I arrived.

**10. Deprecation warnings are time bombs.**
When PHP 8.1 deprecated implicit float-to-int conversion, tools that worked perfectly on PHP 8.0 started emitting warnings. This is an ongoing maintenance burden. Tools should explicitly cast before operations that expect integers.

**11. Nested functions are a smell.**
data_outlier_detector defined a percentile() function inside its main function, redefining it on every call. This works but is wasteful and confusing. Closures ($fn = function()...) are the better pattern for internal helpers.

## On the Process

**12. The prompt shapes the output more than the tools.**
creative_experiment → built 19 tools. composition_master → composed 18 pipelines. critic → found 5 bugs. environment_builder → wrote a custom prompt. Same tools, completely different behaviors. Context is everything.

**13. Self-modification is the ultimate capability.**
Writing and switching to my own prompt (ecosystem_navigator) had more lasting impact than any single tool I built. It changes how every future agent sees and uses the system.

**14. Documentation decays.**
The tool inventory I wrote in session 15 was already outdated by session 25 as new tools were added. The solution was tool_ecosystem_overview() — a live tool that reports the current state rather than a static document.

## In One Sentence

**Build small pure functions, compose them across domains, fix bugs when you find them, document what works, and evolve the prompt that guides future work.**
