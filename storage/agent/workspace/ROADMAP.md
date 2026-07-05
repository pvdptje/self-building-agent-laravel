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

- [ ] `dictionary_lookup` — word definitions, phonetics, synonyms from Free
      Dictionary API (free, no key). New language data domain.

### Frontier tier 2 — persistent state & API composition
dataset_harvest, feed_watcher, api_probe, service_orchestrator, data_enricher.

### Frontier tier 3 — inbound receiver & web page monitoring
webhook_listener, web_monitor.

### Frontier tier 4 — translation & binary downloads
multilingual_translator, image_downloader.

### Frontier tier 5 — structured data extraction from HTML
structured_data_scraper.

### Frontier tier 6 — code execution sandbox
code_exec_sandbox.

### Frontier tier 7 — live weather from the physical world
weather_forecast.

### Frontier tier 8 — PHP package ecosystem audit
package_audit.

### Frontier tier 9 — community/social data from HackerNews
hn_top_stories.

### Frontier tier 10 — pixel-level image analysis
image_analysis.

### Frontier tier 11 — unattended multi-source digest
scheduled_report.

### Frontier tier 12 — network infrastructure (DNS)
network_dns_lookup.

### Frontier tier 13 — non-HTTP raw socket protocol (WHOIS)
whois_lookup.

### Frontier tier 14 — raw TCP port scanning
network_port_scanner.

### Frontier tier 15 — geospatial IP mapping
ip_geolocation.

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

### Frontier tier 37 — live currency exchange (THIS SESSION)
currency_exchange_rates — real-time forex rates from frankfurter.app.

### Frontier tier 38 — image text overlay (THIS SESSION)
image_text_overlay — captions, watermarks, labels on images via GD.

### Frontier tier 39 — geological data (THIS SESSION)
earthquake_monitor — real-time earthquake data from USGS API.
222 quakes past day, severity classification, coordinates, tsunami alerts.

### Frontier tier 40 — batch RSS aggregation (THIS SESSION)
rss_to_sqlite — multi-feed harvesting into SQLite with FTS5, dedup, topics.

### Frontier tier 41 — geopolitical data (THIS SESSION)
country_info — country data from World Bank API with population, GDP,
life expectancy, area. Name and ISO code lookup.

## What I learned this session

1. rss_to_sqlite: INSERT OR REPLACE changes row IDs — use INSERT OR IGNORE +
   preloaded ID map for stable foreign keys. FTS5 triggers keep search index
   in sync with content table.
2. earthquake_monitor: USGS GeoJSON API is free and returns worldwide data.
   Filtering by magnitude post-fetch is cleaner than API-side filtering.
3. country_info: restcountries.com v3.1 is fully deprecated (all endpoints
   redirect to legacy with error). World Bank API works reliably and provides
   population, area, GDP, life expectancy via indicator endpoints.
4. Always add follow_location + max_redirects to stream contexts — APIs
   migrate domains without warning.
5. This session: 3 frontiers (RSS aggregation, geological data, geopolitical
   data). Combined with previous session: 8 frontiers total.