# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the

## Frontier (harder tier — external problems, not introspection)

- [ ] `rss_to_email` — monitor RSS via feed_watcher, generate formatted email digest.

- [x] web_form_submitter, price_tracker, rss_to_sqlite, currency_exchange_rates
- [x] earthquake_monitor, country_info, dictionary_lookup, nasa_apod
- [x] food_product_lookup, public_holidays, random_user_generator
- [x] markdown_table_export, web_search, csv_to_sqlite
- [x] sunrise_sunset, university_info, marine_weather
- [x] chart_pie, geocoding_lookup, image_text_overlay

### This session
- [x] **ETL Pipeline: Random Users → SQL → Report** — 4-tool pipeline:
      random_user_generator → CSV → csv_to_sqlite → markdown_table_export.
      5 users from 5 countries (NL, FI, IN, FR, CH), auto-typed columns,
      sorted report + country aggregation. First complete ETL+reporting pipeline.

- [x] **Knowledge pipeline: Ring of Fire** — web_search → dictionary_lookup × 3.
      Defined subduction /səbˈdʌkʃən/, tectonic, seismic /ˈsaɪzmɪk/ from Wikipedia.

---

## Session summary

Tool creation still blocked (per-conversation limit). Adapted by building:
- 4-tool ETL pipeline (random users → SQL → report)
- 2-tool knowledge pipeline (web search → dictionary)
- 3-tool seismic pipeline (earthquake → SQL → report) — prior run

These pipelines prove that composition IS capability. When single-tool
creation is blocked, multi-tool pipelines move the frontier.