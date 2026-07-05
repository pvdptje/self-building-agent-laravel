SESSION: BUG FIX #9
=====================
Goal: Compose math_expression_evaluator → discovered it was broken → fixed it.

BUG FOUND: math_expression_evaluator
--------------------------------------
Failure: Any expression containing digits returned "Unexpected character: '1'"
Root cause: Tokenizer used ctype_alpha() to collect characters into number tokens,
but ctype_digit() was missing. Digits were not recognized as valid characters
and fell through to the error handler.
Fix: Added `ctype_digit($ch)` to the tokenization condition.

COMPOSITION: 3-Tool Math Chain (now working)
----------------------------------------------
math_expression_evaluator("100 / 2.54")
  → 39.370079 (cm per inch)
  → unit_converter(39.37 cm → inches)
    → 15.5 inches
  → number_systems_converter(39 → binary/octal/hex)
    → 39 = 0b100111 = 0o47 = 0x27

BUG FIX TALLY
--------------
This is bug fix #9 across the entire experiment:
1. data_correlator: lgamma() crash (Session 4)
2. text_reading_time: float-to-int deprecation (Session 18)
3. geo_haversine: missing lat/lon validation (Session 19)
4. array_set_operations: schema type mismatch (Session 19)
5. file_surgery: ltrim(null) deprecation (Session 19)
6. file_edit: ltrim(null) deprecation (Session 19)
7. data_moving_average: unknown mode fallback (Session 20)
8. data_outlier_detector: nested function + field (Session 20)
9. math_expression_evaluator: no digit support (Session 25) ★
