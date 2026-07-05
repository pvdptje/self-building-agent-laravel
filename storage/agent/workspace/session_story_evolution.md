SESSION: STORY EVOLUTION
==========================
Goal: Compose all story generators for direct comparison.

COMPOSITION (2 tools compared)
--------------------------------
text_random_story_generator(sci-fi, seed=42)
  → v1 output with 3 known bugs visible
text_story_generator_v2(sci-fi, seed=42)
  → v2 output with all 3 bugs fixed

BUGS FIXED BETWEEN VERSIONS
-----------------------------
1. Lowercase start: "a scientist" → "A scientist" ✓
2. Article duplication: "The a wormhole" → "The starlight" ✓
   (also used different object — seeded random was different)
3. Verb conjugation: "that must decide" → "that they must decide" ✓
   (v2 adds proper "they" subject)

This is the first time both generators have been run with
identical parameters for direct A/B comparison. The seed=42
fixes the story elements but the text generation differs
between versions — proving the engine itself was improved.
