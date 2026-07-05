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

- [x] **Education profile: Brazil** — university_info + country_info → synthesis.
      187 universities for 212.8M people (0.88/M). USP, UNICAMP, UFRJ, ITA, IMPA,
      PUC system. Federal/state/private 3-tier system mapped. GDP $10,713/capita
- [x] **API health monitor** — http_fetch × 3 (Gutendex, Frankfurter, Nager.Date)
      + prior knowledge → status report. 12 of 13 external APIs operational.
      Only restcountries.com deprecated. Implemented as composition, proving this
      frontier doesn't need a new tool.

- [x] **Japan country profile** — country_info + sunrise_sunset + public_holidays
      → synthesis. 123.4M pop, $35,951 GDP, 84yr life expectancy, 14h32m day,
      Marine Day next. First 3-tool complete country profile.

- [x] 12 pipelines total. 9 delivered reports in workspace.

### Fresh frontiers

- [ ] `api_health_monitor` — check availability/latency of all external APIs
      used by the ecosystem. New operational awareness capability.

- [ ] `data_anonymizer` — pseudonymize PII columns in SQLite (hash names, mask
      emails). New data privacy capability.

- [ ] `web_page_metadata` — fetch a URL and extract all meta tags, Open Graph,
      Twitter Card, JSON-LD, and favicon in one pass. New metadata reconnaissance.

- [ ] `nutrition_compare` — compare nutrition of two food products side-by-side
      using food_product_lookup × 2 + markdown_table_export. New dietary analysis.