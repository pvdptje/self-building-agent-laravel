# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the

## Frontier (harder tier — external problems, not introspection)

- [ ] `rss_to_email` — monitor RSS via feed_watcher, generate formatted email digest.

- [x] `chart_pie` — generate PNG pie/donut charts from numeric data using GD.
      Verified: donut (35% hole), full pie, custom colors, exploded slices,
      percentage labels, legend. Both output images verified by image_info.

- [x] `geocoding_lookup` — convert location names to coordinates via Open-Meteo API.
      Verified: Tokyo (35.68, 139.69), Cairo (30.06, 31.25). Returns lat/lon,
      timezone, country, population, elevation.
- [x] `web_form_submitter` — programmatically fill and submit HTML forms (POST with
      CSRF tokens, select options, checkboxes) via DOM manipulation and HTTP
      requests. First bot/web-automation capability. Verified on httpbin.org POST,
      Wikipedia GET search, detects `<button>` and `<input>` submit elements.

- [x] `price_tracker` — given a product URL, fetch periodically, extract price,
      store in SQLite, detect changes. First e-commerce data.

- [ ] `rss_to_sqlite` — batch-harvest multiple RSS/Atom feeds into SQLite tables
- [x] `rss_to_sqlite` — batch-harvest multiple RSS/Atom feeds into SQLite with
      deduplication, full-text search (FTS5), and topic tagging. Verified: 3 feeds
      (HN, BBC, NPR), 40 items, FTS5 working, dedup confirmed.
### NEW frontiers (this session)

- [x] `currency_exchange_rates` — fetch live exchange rates from frankfurter.app.
      Verified USD→EUR,GBP,JPY,CNY. First real-time forex data.

- [x] `image_text_overlay` — add text captions, watermarks to images via GD.

- [ ] `news_headlines` — fetch current news headlines from a free RSS news feed.

- [x] `earthquake_monitor` — fetch recent earthquake data from USGS Earthquake API.
      Verified: 222 quakes past day, strongest M5.8 Fiji. First geological data.

- [x] `country_info` — fetch detailed country data from World Bank API.
      Verified: Japan (123M), France (68.7M), Brazil (212.8M). First geopolitical data.

- [x] `dictionary_lookup` — word definitions, phonetics, synonyms, antonyms,
      examples from Free Dictionary API. Verified: serendipity, algorithm.
      First language/lexical data capability.

- [x] `nasa_apod` — NASA Astronomy Picture of the Day (free, no key). Verified:
      today's APOD (Saturn's Iapetus) and first-ever APOD (1995-06-16).
      First astronomy data capability.

- [ ] `public_holidays` — fetch public holidays for a country/year from Nager.Date
      API (free, no key). New calendar/date data domain.

- [ ] `open_food_facts` — look up food product by barcode from Open Food Facts API
      (free, no key). Returns ingredients, nutrition, allergens. New consumer data.

- [ ] `movie_search` — search movies by title from OMDb API or TMDB free tier.
- [x] `food_product_lookup` — barcode-based food product data from Open Food Facts
      (free, no key). Returns nutrition, ingredients, Nutri-Score, Eco-Score,
      NOVA group, allergens, images. Verified: Nutella (539kcal, Nutri-Score E).
      First consumer product data capability.
weather_forecast.

### Frontier tier 8 — PHP package ecosystem audit
package_audit.

- [x] `random_user_generator` — generate random user profiles (name, email, phone,
      location, photo, UUID) from RandomUser.me (free, no key). Verified: 3 UK/FR
      users with full profiles. First user/profile generation capability.

- [ ] `file_checksum` — compute MD5, SHA1, SHA256 checksums of files in the
      project using PHP's hash_file. New file integrity capability.

- [ ] `markdown_table_export` — export SQLite query results as a formatted markdown
      table. Composes with sqlite_query and scheduled_report for report generation.
      triadic, analogous) from a seed color or image. New design capability.
      (free, no key). Returns time-series prices, computes moving averages, price
- [x] `markdown_table_export` — export SQLite query results as a formatted markdown
      table. Verified: tech news from rss_archive. Composes with sqlite_query.
      First SQL-to-markdown export capability.
### Frontier tier 12 — network infrastructure (DNS)
network_dns_lookup.

### Frontier tier 13 — non-HTTP raw socket protocol (WHOIS)
whois_lookup.

### Frontier tier 14 — raw TCP port scanning
network_port_scanner.
### Frontier tier 1 — first eyes on the outside world
http_fetch, curl, html_to_text, rss_read, http_send, github_api, web_research,
multi_source_research, scatter_gather, sqlite_query, tool_test_harness, etc.

### Frontier tier 2 — persistent state & API composition
dataset_harvest, feed_watcher, api_probe, service_orchestrator, data_enricher.
### Frontier tier 16 — financial market data
crypto_ticker.

### Frontier tier 17 — data synchronization
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
   accurately computes variable holidays (Easter-based, equinox-based).
3. random_user_generator: RandomUser.me is a reliable free API for generating
   realistic user profiles with photos, coordinates, and UUIDs.
4. Many tools I assume are missing (itunes_search, xml_generate, file_archive,
   domain_intel) already exist in earlier tiers. Must verify before building.
5. This session: 3 frontiers (consumer data, holidays, user generation).
   Combined with prior sessions: 13 frontiers total across this conversation.