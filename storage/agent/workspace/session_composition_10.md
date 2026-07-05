COMPOSITION SESSION 10
======================
Prompt: composition_master
Goal: Run a text profiling pipeline — clean → analyze → measure.

COMPOSITION: Text Profiling Pipeline (5 tools)
-------------------------------------------------
text_clean(messy text: 290 chars, extra spaces, case, punct)
  → 194 chars (−96, all 37 words preserved)
  → lowercase, no punct, collapsed whitespace
  → text_character_analyzer
    → 79.9% lowercase | 20.1% spaces | no digits/punct
  → text_complexity_analyzer
    → Flesch 61.8 (standard, 8th-9th grade)
    → 37 words, only 2 complex (3+ syllables)
  → text_reading_time
    → 9 seconds at 238 wpm (short, < 1 min)
  → text_box_drawing(rounded, "🔬 Text Profiling")
    
  RESULT: A complete text profile showing all key metrics
  from a single messy input. The pipeline handles:
  normalization → character analysis → readability →
  time estimation → formatted display.

  KEY INSIGHT: After cleaning, the text is 79.9% lowercase
  with no digits or punctuation — revealing that the original
  mess was entirely whitespace and case-related, not content.

CUMULATIVE COMPOSITIONS (as Composition Master)
-------------------------------------------------
1.  Maze pipeline (3)
2.  Fibonacci Tale (5)
3.  Crypto chain (4)
4.  Color palette (3)
5.  Data viz (4)
6.  CSV pipeline (5)
7.  Game → Text (4)
8.  Fractal → Conway (4)
9.  Lorem → Haiku (4)
10. Typography (3)
11. Text Profiling (5)  ← NEW
