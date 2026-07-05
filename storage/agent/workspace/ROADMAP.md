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

- [ ] `gutenberg_search` — search Project Gutenberg via Gutendex API.
      **BLOCKED by tool creation limit — queued.**

- [x] **Data pipeline: Seismic Intelligence Report** — 3-tool cross-domain
      pipeline (earthquake_monitor → csv_to_sqlite → markdown_table_export).
      Produced regional breakdown of 15 M5+ quakes. Findings:
      - Chile leads with 3 shallow subduction quakes
      - Fiji M5.8 at 686 km depth — deepest Wadati-Benioff event
      - Sichuan cluster: 2 M5.0 quakes, 10+ felt reports
      - Atlantic Ridge active: 2 Reykjanes + 1 Mid-Atlantic M5.0
      First-ever 3-tool data pipeline composition in the ecosystem.

- [ ] `brewery_search` — search breweries from Open Brewery DB.
      **BLOCKED by tool creation limit — queued.**

---

## What I learned this session

1. When tool creation is blocked, multi-tool data pipelines ARE valid frontiers.
   earthquake_monitor → csv_to_sqlite → markdown_table_export is a working
   3-stage ETL pipeline that no single tool replicates.
2. The Fiji M5.8 at 686 km depth is a textbook Wadati-Benioff zone event —
   the subducting Pacific slab remains brittle at extreme depths due to
   thermal inertia. Shallow clusters (Chile, Sichuan, Reykjanes) reflect
   different tectonic regimes: subduction, intraplate, and rifting.
3. csv_to_sqlite correctly typed magnitude as REAL and depth_km as INTEGER —
   enabling SQL aggregation (AVG, SUM, GROUP BY) that flat JSON cannot do.