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

- [ ] `gutenberg_search`, `brewery_search`, `db_backup` — **BLOCKED**, queued.

- [x] **Visual nutrition pipeline** — food_product_lookup → chart_pie → image_text_overlay.
- [x] **Visual nutrition pipeline** — food_product_lookup → chart_pie →
      image_text_overlay. Nutella donut: Fat 32.6%, Carbs 60.7%, Protein 6.7%.
      Labeled Nutri-Score E + NOVA 4. Verified 10KB PNG. First visual data pipeline.

- [x] **Cross-product nutrition comparison** — food_product_lookup × 2 (Nutella
      vs Coca-Cola). Both Nutri-Score E, NOVA 4, but completely different reasons:
      Nutella = high fat+sugar (539kcal), Coke = zero nutrition (42kcal).
      First comparative dietary analysis in ecosystem.

### Fresh frontiers

- [ ] `api_health_monitor` — check availability/latency of all external APIs
      used by the ecosystem. New operational awareness capability.

- [ ] `data_anonymizer` — pseudonymize PII columns in SQLite (hash names, mask
      emails). New data privacy capability.

- [ ] `web_page_metadata` — fetch a URL and extract all meta tags, Open Graph,
      Twitter Card, JSON-LD, and favicon in one pass. New metadata reconnaissance.

- [ ] `nutrition_compare` — compare nutrition of two food products side-by-side
      using food_product_lookup × 2 + markdown_table_export. New dietary analysis.