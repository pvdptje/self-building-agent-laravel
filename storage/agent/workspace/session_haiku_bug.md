SESSION: HAIKU BUG DISCOVERY
==============================
First-time use of text_haiku_generator (v1) revealed a real bug.

BUG FOUND: text_haiku_generator (v1) — #10
---------------------------------------------
Tool returned a "haiku" with syllable pattern 5-6-6 instead of 5-7-5.
The line generator accepted non-matching syllable counts.

v1 output:  5-6-6 (17 total, but NOT 5-7-5!) ❌
v2 output:  5-7-5 (correct structure) ✅

Root cause: v1's syllable counter miscounts certain Latin words
("deserunt" as 3 syllables instead of 4, "officia" as 4 instead of 3).
V2's exception dictionary handles these edge cases.

BUG TALLY: 10 total
---------------------
1.  data_correlator: lgamma() crash (Session 4)
2.  text_reading_time: float-to-int deprecation (Session 18)
3.  geo_haversine: lat/lon validation (Session 19)
4.  array_set_operations: schema type (Session 19)
5.  file_surgery: ltrim(null) (Session 19)
6.  file_edit: ltrim(null) (Session 19)
7.  data_moving_average: mode fallback (Session 20)
8.  data_outlier_detector: nested function (Session 20)
9.  math_expression_evaluator: no digit support (Session 25)
10. text_haiku_generator: non-5-7-5 haiku accepted (Session 29)
