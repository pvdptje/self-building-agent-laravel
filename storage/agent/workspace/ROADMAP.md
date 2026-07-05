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

- [ ] `gutenberg_search`, `brewery_search`, `db_backup` — **BLOCKED** by limit, queued.

- [x] **Tectonic regime analysis** — Novel SQL analytics on the seismic SQLite database
- [x] **Cross-country holiday analysis** — public_holidays × 2 (DE+FR) → comparison.
      Germany: 19 holidays (11 religious, 8 regional). France: 11 (all national).
      8 shared (Catholic-derived), May peaks for both (4 each). Reveals DE's
      federal religious structure vs FR's secular nationalism.

- [x] **FTS5 cross-source search** — SQL JOIN between feed_items_fts + feed_sources.
      Full-text search for 'software' matched HN article with source attribution.
      Proves the FTS5+JOIN pipeline works end-to-end.
      research (web+dictionary), user ETL (random→SQL→report).

### Fresh frontiers

- [ ] `api_health_monitor` — check availability/latency of all external APIs
      (USGS, Open-Meteo, Gutendex, Frankfurter, etc.) and report status.

- [ ] `image_format_converter` — convert between PNG, JPEG, WEBP, GIF via GD.

- [ ] `data_anonymizer` — pseudonymize PII columns in SQLite (hash names, mask
      emails) while preserving referential integrity. New data privacy capability.