COMPOSITION SESSION 5
=====================
Prompt: composition_master
Goal: Execute the CSV pipeline — a domain never composed before.

COMPOSITION: CSV Data Pipeline (5 tools)
------------------------------------------
data_csv_simulator(cities + weather data, 8 rows)
  → csv_table(parse with headers)
  → text_table_formatter(borders, title)
  → math_statistics(temperatures)
  
  RESULT:
  Weather & Air Quality Report
  ╔════════╤═════════════════╤════════════╤═══════════╗
  ║ City   │ Temperature °C  │ Humidity % │ AQI Index ║
  ╟────────┼─────────────────┼────────────┼───────────╢
  ║ Tokyo  │ 32              │ 94         │ 36.69     ║
  ║ Paris  │ 0               │ 88         │ 119.37    ║
  ║ London │ 30              │ 64         │ 19.99     ║
  ║ London │ 17              │ 87         │ 66.74     ║
  ║ London │ 30              │ 69         │ 130.18    ║
  ║ Sydney │ 7               │ 61         │ 144.40    ║
  ║ Sydney │ 22              │ 53         │ 0.16      ║
  ║ Tokyo  │ 38              │ 66         │ 123.50    ║
  ╚════════╧═════════════════╧════════════╧═══════════╝
  
  Temperature stats: mean=22°C, median=26°C
  Range: 0°C (Paris) to 38°C (Tokyo)

  This is the first time the CSV tools have been composed
  in this pipeline: generate → parse → format → analyze.

CUMULATIVE COMPOSITIONS (as Composition Master)
-------------------------------------------------
1. Maze pipeline (3 tools) — maze→solve→render
2. Fibonacci Tale (5 tools) — numbers→words→template→box
3. Crypto chain (4 tools) — shift→morse→emoji-morse
4. Color palette (3 tools) — palette→convert→display
5. Data viz (4 tools) — simulator→normalize→sparkline→box
6. CSV pipeline (5 tools) — csv gen→parse→table→stats
