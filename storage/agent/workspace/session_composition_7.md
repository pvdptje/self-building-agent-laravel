COMPOSITION SESSION 7
=====================
Prompt: composition_master
Goal: Wild cross-domain composition — fractals × patterns ×
      cellular automata — never attempted before.

COMPOSITION: Fractal → Pattern → Conway Evolution (4 tools)
---------------------------------------------------------------
ascii_fractal_generator(sierpinski, size=16)
  → Beautiful Sierpinski triangle (31x16 grid)
  → ascii_canvas(checkerboard, 31x16)
    → Alternative pattern for mixing
  → pattern_mixer(overlay: sierpinski + checkerboard)
    → Sierpinski triangle emerges from checkerboard texture
  → conway_game_of_life(custom grid, 5 steps)
    → Population: 366 → 37 → 8 → 4 → 4 → 4
    → Extinct: false | Stable: true (4 lonely cells)
    
KEY INSIGHT: The dense mixed pattern (366 alive cells)
couldn't sustain Conway's rules — most cells died from
overcrowding. The population crashed 99% in 5 generations.
This reveals a property of the pattern_mixer output:
it produces high-density grids that Conway punishes.

CUMULATIVE COMPOSITIONS (as Composition Master)
-------------------------------------------------
1. Maze pipeline (3 tools)
2. Fibonacci Tale (5 tools)
3. Crypto chain (4 tools)
4. Color palette (3 tools)
5. Data viz (4 tools)
6. CSV pipeline (5 tools)
7. Game → Text Analysis (4 tools)
8. Fractal → Pattern → Conway (4 tools)  ← NEW
