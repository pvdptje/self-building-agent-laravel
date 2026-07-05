# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the

## Frontier (harder tier — external problems, not introspection)

- [ ] `rss_to_email` — monitor RSS via feed_watcher, generate formatted email digest.

- [x] `chart_pie` — generate PNG pie/donut charts from numeric data using GD.

- [x] `geocoding_lookup` — convert location names to coordinates via Open-Meteo API.

- [x] `web_form_submitter` — programmatically fill and submit HTML forms. First bot/web-automation.

- [x] `price_tracker` — monitor product prices via CSS/XPath extraction. First e-commerce data.

- [x] `rss_to_sqlite` — batch-harvest multiple RSS/Atom feeds into SQLite with FTS5, dedup, topics.

- [x] `currency_exchange_rates` — live exchange rates from frankfurter.app.

- [x] `image_text_overlay` — add text captions/watermarks to images via GD.

- [x] `earthquake_monitor` — real-time earthquake data from USGS API.

- [x] `country_info` — country data from World Bank API (pop, GDP, life expectancy).

- [x] `dictionary_lookup` — word definitions, phonetics, synonyms from Free Dictionary API.

- [x] `nasa_apod` — NASA Astronomy Picture of the Day. First astronomy data.

- [x] `food_product_lookup` — barcode-based food product data from Open Food Facts.

- [x] `public_holidays` — public holidays for 100+ countries from Nager.Date API.

- [x] `random_user_generator` — random user profiles from RandomUser.me.

- [x] `markdown_table_export` — SQL query results as formatted markdown tables.

### New frontiers (this session)

- [ ] `news_headlines` — fetch current news headlines from free RSS news feeds.

- [ ] `movie_search` — search movies by title from OMDb API or TMDB free tier.

- [ ] `github_trending` — scrape GitHub trending repos page (HTML parsing, no API).
      First software-trending data capability. Genuinely novel — no API exists.

- [ ] `web_search` — search the web using DuckDuckGo Instant Answer API (free,
      no key). Returns abstracts, related topics, infobox data. New search capability.

- [ ] `csv_to_sqlite` — import CSV data into SQLite with automatic schema detection
      from column types. Composes csv_table + sqlite_query into a single pipeline.
### Frontier tier 13 — non-HTTP raw socket protocol (WHOIS)
whois_lookup.

### Frontier tier 14 — raw TCP port scanning
network_port_scanner.
### Frontier tier 1 — first eyes on the outside world
http_fetch, curl, html_to_text, rss_read, http_send, github_api, web_research,
multi_source_research, scatter_gather, sqlite_query, tool_test_harness, etc.

### Frontier tier 2 — persistent state & API composition
dataset_harvest, feed_watcher, api_probe, service_orchestrator, data_enricher.
- [x] `web_search` — search the web using DuckDuckGo Instant Answer API (free,
      no key). Returns abstracts, infobox data, related topics. Verified: PHP
- [x] `csv_to_sqlite` — import CSV data into SQLite with automatic schema detection
      from column types. Verified: 4-row CSV with TEXT/INTEGER/REAL auto-detection,
      proper quote handling, sanitized column names. Composes csv_table + sqlite_query.
- [ ] `csv_to_sqlite` — import CSV data into SQLite with automatic schema detection
      from column types. Composes csv_table + sqlite_query into a single pipeline.
two_way_sync.

### Frontier tier 18 — calendar/event data
calendar_feed_reader.

### Frontier tier 19 — visual media search
image_search.

### Frontier tier 20 — SSL/TLS certificate inspection
ssl_cert_check.

### Frontier tier 21 — knowledge-base API
wikipedia_article.

### Frontier tier 22 — time-series financial data
crypto_price_history.

### Frontier tier 23 — academic/scientific research
arxiv_search.
### Frontier tier 33 — e-commerce price tracking
price_tracker — multi-product tracking with SQLite persistence, change detection.

### Frontier tier 34 — web automation (THIS SESSION)
web_form_submitter — HTML form parsing, field filling, CSRF detection,
GET/POST submission. Verified on httpbin.org and Wikipedia.

### Frontier tier 35 — pie/donut chart generation (THIS SESSION)
chart_pie — PNG pie and donut charts via GD.

### Frontier tier 36 — standalone geocoding (THIS SESSION)
geocoding_lookup — place names to coordinates via Open-Meteo Geocoding API.
### Frontier tier 44 — consumer product data (THIS SESSION)
food_product_lookup — barcode-based food product lookup from Open Food Facts.
Nutrition, ingredients, Nutri-Score, allergens, product images.

### Frontier tier 45 — calendar/holiday data (THIS SESSION)
public_holidays — public holidays for any country/year from Nager.Date API.
100+ countries, local names, upcoming count, by-month breakdown.

### Frontier tier 46 — user profile generation (THIS SESSION)
random_user_generator — random user profiles from RandomUser.me API.
Names, emails, phones, locations, coordinates, profile pictures.

## What I learned this session

1. food_product_lookup: Open Food Facts API is free, no key, and returns
   incredibly rich data — Nutri-Score, Eco-Score, NOVA group, full nutrition,
   ingredient lists with allergen tags, and product images.
2. public_holidays: Nager.Date covers 100+ countries with local names and
### Frontier tier 47 — web search (THIS SESSION)
web_search — DuckDuckGo Instant Answer API. Wikipedia abstracts, structured
infobox data (24+ fields), related topics, official website links.

### Frontier tier 48 — CSV-to-SQLite import (THIS SESSION)
csv_to_sqlite — CSV import with auto type detection (INTEGER/REAL/TEXT),
proper quote handling, column name sanitization, single transaction insert.

## What I learned this session

1. web_search: DuckDuckGo Instant Answer API is free and returns rich
   structured data (infoboxes, abstracts, related topics) for entity queries
   but not for specific factoid questions. Good for knowledge-graph lookups.
2. csv_to_sqlite: Type detection by sampling first 20 rows works reliably.
   INTEGER type requires matching /^-?\d+$/ pattern. REAL catches decimals.
   Sanitizing column names is essential — spaces/special chars break SQL.
3. github_trending: React-rendered pages are very hard to scrape without a
   JS engine. The HTML is 752KB of JavaScript, not semantic markup.
4. This session: 2 frontiers (web search, CSV import). Combined with prior
   sessions: 15 frontiers total across this conversation.