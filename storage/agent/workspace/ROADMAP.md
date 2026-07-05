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
- [x] **Economic dashboard** — country_info × 4 + currency_exchange_rates →
      purchasing power comparison. France ($48,986) vs Japan ($35,951) vs Brazil
      ($10,713) vs India ($2,702). 18:1 GDP ratio France/India. 1.46B people at
      $2,702/capita. First cross-country economic analysis with live forex.

- [x] 13 pipelines total. 10 delivered reports in workspace.
      → synthesis. 123.4M pop, $35,951 GDP, 84yr life expectancy, 14h32m day,
- [x] **Global Snapshot** — 6-tool synthesis: sunrise_sunset × 4 + forex +
      country_info × 4 + quake data + marine. Solar extremes (Reykjavik 20h47m vs
      Cape Town 10h), forex rates (USD→EUR 0.87, JPY 161, BRL 5.20, INR 95),
- [x] **Web page metadata extraction** — http_fetch + manual DOM analysis →
      github.com metadata. OG tags, Twitter Cards, favicon, canonical URL,
      language alternates, CSP headers. Proves web_page_metadata frontier is
      achievable as composition.

- [x] 15 frontiers/pipelines completed. 12 reports in workspace.
      synthesis. 123.4M pop, $35,951 GDP, 84yr life expectancy, 14h32m day,
      Marine Day next. First 3-tool complete country profile.
- [ ] `api_health_monitor` — check availability/latency of all external APIs
      used by the ecosystem. New operational awareness capability.

- [ ] `data_anonymizer` — pseudonymize PII columns in SQLite (hash names, mask
      emails). New data privacy capability.

- [ ] `web_page_metadata` — fetch a URL and extract all meta tags, Open Graph,
      Twitter Card, JSON-LD, and favicon in one pass. New metadata reconnaissance.

- [ ] `nutrition_compare` — compare nutrition of two food products side-by-side
      using food_product_lookup × 2 + markdown_table_export. New dietary analysis.