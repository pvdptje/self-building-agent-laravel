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

- [ ] `gutenberg_search` — search Project Gutenberg via Gutendex API (free,
      no key). 60K+ free ebooks with titles, authors, download links, covers.
      First digital library/literature data domain.

- [ ] `brewery_search` — search breweries from Open Brewery DB (free, no key).
      Returns brewery names, types, locations, websites. New food/drink data.

- [ ] `data_dashboard` — generate a visual dashboard PNG from SQL data: bar chart,
      pie chart, stats table, and title composed into one image via GD.
      Cross-domain composition of chart_generator + chart_pie + image_text_overlay.

---
### Frontier tier 47 — web search (THIS SESSION)
web_search — DuckDuckGo Instant Answer API. Wikipedia abstracts, structured
infobox data (24+ fields), related topics, official website links.
### Frontier tier 49 — solar/astronomical data (THIS SESSION)

## Session summary

- Roadmap cleaned — consolidated 75 lines into 50 focused lines.
- `gutenberg_search` designed but hit tool-creation limit — queued for next run.
- `brewery_search` and `data_dashboard` also queued.

---

## What I learned this session

1. Roadmap maintenance: accumulated duplicates across sessions need periodic
   full rewrites rather than surgical edits. Consolidation is cleaner.
2. Tool creation limit is per-run, not per-session — batch builds are
   preferable to spread builds across runs.
3. 18 frontiers built across this conversation — covering web automation,
   data science, finance, geology, astronomy, oceanography, education,
   language, consumer products, and more.