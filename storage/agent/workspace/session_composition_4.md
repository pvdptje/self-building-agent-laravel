COMPOSITION SESSION 4
=====================
Prompt: composition_master
Goal: Execute two novel compositions — a color chain and a data viz chain.

COMPOSITION 1: Color Palette Chain
-------------------------------------
color_palette_generator(#ff6b35, triadic)
  → [#ff6b35, #35ff6b, #6b35ff]
  → color_converter on each (hex → rgb → hsl → hsv)
  → text_box_drawing(heavy, "🌈 Color Composition")
  
  RESULT:
  ┏━ 🌈 Color Composition ━━━━━━━━━━━━━━━━━┓
  ┃ 🎨 Triadic Palette from #ff6b35        ┃
  ┃ 🟧 #ff6b35  rgb(255,107,53)   hsl(16°)┃
  ┃ 🟩 #35ff6b  rgb(53,255,107)   hsl(136°)┃
  ┃ 🟪 #6b35ff  rgb(107,53,255)  hsl(256°)┃
  ┃ Hues evenly spaced at 120° intervals   ┃
  ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

COMPOSITION 2: Data Viz Chain
-------------------------------
data_simulator(sine_wave, n=16)
  → [50,100,50,0,50,100,50,0,50,100,50,0,50,100,50,0]
  → data_normalizer(minmax, 0→1)
  → [0.5, 1, 0.5, 0, ...]
  → emoji_sparkline(heat style)
  → 🟧🔥🟧⬜🟧🔥🟧⬜🟧🔥🟧⬜🟧🔥🟧⬜
  → text_box_drawing(rounded, "📈 Data Viz Chain")
  
  Shows the complete data pipeline:
  generate → scale → visualize → present

NEXT SUGGESTED COMPOSITIONS
-----------------------------
1. Game analysis: game_playthrough_runner → text_summarizer
2. CSV pipeline: data_csv_simulator → csv_table → 
   text_table_formatter → text_box_drawing
3. Text-to-palette: text_sentiment_analysis → 
   color_palette_generator → color_converter → display
