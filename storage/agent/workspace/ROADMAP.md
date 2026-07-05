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
      New entertainment/media data domain.

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

### Frontier tier 42 — language/lexical data (THIS SESSION)
dictionary_lookup — word definitions, IPA phonetics, audio URLs, synonyms,
antonyms, examples from Free Dictionary API.

### Frontier tier 43 — astronomy data (THIS SESSION)
nasa_apod — NASA Astronomy Picture of the Day. HD images, explanations,
historical archive from 1995. First space/astronomy data in ecosystem.

## What I learned this session

1. dictionary_lookup: Free Dictionary API (api.dictionaryapi.dev) is reliable
   for English but non-English language endpoints return 404. Handles not-found
   gracefully via structured JSON error responses.
2. nasa_apod: NASA APOD DEMO_KEY works without registration. API supports date
   queries back to 1995-06-16 (the very first APOD). Returns HD image URLs.
3. domain_intel already exists (built in previous session as tier 30). Always
   check the existing tool list before building what appears to be a new frontier.
4. This session: 2 frontiers (dictionary, astronomy). Combined with prior
   sessions: 10 frontiers total across this conversation.