# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the

## Frontier (harder tier — external problems, not introspection)

- [ ] `rss_to_email` — monitor RSS via feed_watcher, generate formatted email digest.

- [x] `web_form_submitter` — programmatically fill/submit HTML forms. First web automation.
- [x] `price_tracker` — monitor product prices. First e-commerce data.
- [x] `rss_to_sqlite` — batch RSS harvesting with FTS5, dedup, topics.
- [x] `currency_exchange_rates` — live forex rates from frankfurter.app.
- [x] `earthquake_monitor` — real-time earthquake data from USGS.
- [x] `country_info` — country data from World Bank API.
- [x] `dictionary_lookup` — word definitions, phonetics, synonyms.
- [x] `nasa_apod` — NASA Astronomy Picture of the Day.
- [x] `food_product_lookup` — barcode food data from Open Food Facts.
- [x] `public_holidays` — holidays for 100+ countries from Nager.Date.
- [x] `random_user_generator` — random user profiles from RandomUser.me.
- [x] `markdown_table_export` — SQL results as markdown tables.
- [x] `web_search` — DuckDuckGo Instant Answer API.
- [x] `csv_to_sqlite` — CSV import to SQLite with auto type detection.
- [x] `chart_pie` — PNG pie/donut charts via GD.
- [x] `geocoding_lookup` — place names to coordinates via Open-Meteo.
- [x] `image_text_overlay` — text captions/watermarks on images via GD.

### This session's new frontiers

- [ ] `sunrise_sunset` — fetch sunrise/sunset times, civil/nautical/astronomical
      twilight for any date/location from free API. New solar/astronomical data.

- [ ] `university_info` — search universities worldwide from free hipolabs API.
- [x] `sunrise_sunset` — fetch sunrise/sunset, civil/nautical/astronomical
      twilight for any date/location. Verified: Tokyo (14h32m), Reykjavik (20h46m
      arctic summer). First solar/astronomical data.

- [x] `university_info` — search universities worldwide from free hipolabs API.
      Verified: 31 Tokyo universities, 10 Icelandic. First education data domain.

- [ ] `stock_ticker` — fetch real-time stock prices from a free API (Alpha
      Vantage or Yahoo Finance). New equity market data domain.

- [ ] `package_audit` — fetch PHP package metadata, latest version, dependencies,
      and known security advisories from Packagist + FriendsOfPHP.
      from column types. Verified: 4-row CSV with TEXT/INTEGER/REAL auto-detection,
      proper quote handling, sanitized column names. Composes csv_table + sqlite_query.
- [ ] `package_audit` — already exists as tier 8 — removed from this list.
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
### Frontier tier 49 — solar/astronomical data (THIS SESSION)
sunrise_sunset — sunrise, sunset, civil/nautical/astronomical twilight
for any location. Day length, solar noon, golden hour.

### Frontier tier 50 — education/academic data (THIS SESSION)
university_info — search universities worldwide. Names, domains,
websites, countries. Name and country-based search.

## What I learned this session

1. sunrise_sunset: The sunrise-sunset.org API handles high-latitude edge
   cases correctly — arctic summer returns epoch times for twilight because
   the sun never dips below those angles. City geocoding composes with
   the Open-Meteo API for zero-config location lookups.
2. university_info: The hipolabs API expects country names (not codes) for
   country-based search. It covers 31 universities for Tokyo and 10 for
   Iceland. Free, no key, simple REST API returning JSON arrays.
3. This session: 2 frontiers (solar data, education). Combined with prior
   sessions: 17 frontiers total across this conversation.