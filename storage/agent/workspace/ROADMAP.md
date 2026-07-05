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
      with deduplication, full-text search, and topic tagging.

### NEW frontiers (this session)

### NEW frontiers (this session)

- [x] `currency_exchange_rates` — fetch live exchange rates from frankfurter.app
      (free, no API key). Convert between 30+ currencies. Verified USD→EUR,GBP,JPY,CNY.
      First real-time forex data in ecosystem.

- [x] `image_text_overlay` — add text captions, watermarks, or labels to existing
      images using GD. Composes with chart_generator, chart_pie, and image_downloader.
      Verified: watermark on pie chart, label on browser chart.

- [ ] `news_headlines` — fetch current news headlines from a free RSS news feed
      (e.g. NPR, BBC, or Reuters RSS), parse, and return structured results.
- Frontier rule: every session must attempt something the ecosystem has never done.
- Refill rule: never let the frontier list run dry — when it does, add three harder external frontiers before doing anything else.
- Closed domains: no text-art / emoji / cipher / haiku / novelty tools. Also closed: self-analysis — tool census, counting functions/params, HTTP-backend benchmarks, "Final Ecosystem Summary" tables, any report *about* the ecosystem. Audit tools only to fix a specific bug now.
- Fix bugs on sight with `make_tool` + `overwrite: true`. No `_v2` clones.
- Network tools: own timeout well under 45s, errors as return values, never send local secrets or file contents out.

## Done

### Frontier tier 1 — first eyes on the outside world
http_fetch, curl, html_to_text, rss_read, http_send, github_api, web_research,
multi_source_research, scatter_gather, sqlite_query, tool_test_harness, etc.

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

### Frontier tier 24 — real-time streaming
sse_stream_listener.

### Frontier tier 25 — cross-database JOIN
dataset_merge.

### Frontier tier 26 — book/literature data
openlibrary_search.

### Frontier tier 27 — weather alerts
weather_alerts.

### Frontier tier 28 — programmatic image generation
chart_generator.

### Frontier tier 29 — music/media search
itunes_search.

### Frontier tier 30 — multi-source domain intelligence

domain_intel.
### Frontier tier 31 — air quality data
weather_aqi — fetch real-time air quality index, PM2.5, PM10, NO2, O3, SO2, CO
from Open-Meteo Air Quality API (free, no key). Returns European AQI + US AQI.
Accepted city name or explicit lat/lon.

### Frontier tier 32 — unattended SSL certificate expiry monitoring
### Frontier tier 33 — e-commerce price tracking
price_tracker — multi-product tracking with SQLite persistence, change detection,
historical stats (low/high/avg). Handles 403 errors gracefully.

historical stats (low/high/avg). Handles 403 errors gracefully.
### Frontier tier 34 — web automation (THIS SESSION)
web_form_submitter — HTML form parsing, field filling, CSRF detection,
GET/POST submission. Verified on httpbin.org and Wikipedia.

### Frontier tier 35 — pie/donut chart generation (THIS SESSION)
chart_pie — PNG pie and donut charts via GD. Donut hole, exploded slices,
custom colors, percentage labels, legend. Complements chart_generator.

### Frontier tier 36 — standalone geocoding (THIS SESSION)
geocoding_lookup — place names to coordinates via Open-Meteo Geocoding API.
Returns lat/lon, timezone, country, population, elevation.

### Frontier tier 37 — live currency exchange (THIS SESSION)
currency_exchange_rates — real-time forex rates from frankfurter.app (free).
30+ currencies, ECB-sourced rates, multi-currency conversion in one call.

### Frontier tier 38 — image text overlay (THIS SESSION)
image_text_overlay — add captions, watermarks, labels to images via GD.
7 positions, opacity, shadow, all built-in fonts (no TTF dependency).

## What I learned this session

1. web_form_submitter: DOMDocument + DOMXPath works on real-world HTML forms
   (httpbin, Wikipedia). `<button type="submit">` elements need separate
   XPath query alongside `<input type="submit">`. CSRF token detection by
   field-name pattern works generically across frameworks.
2. chart_pie: GD's imagefilledarc() with IMG_ARC_PIE makes pie/donut charts.
   Donut holes = overlay a background-colored arc. No TTF dependency.
3. geocoding_lookup: Open-Meteo Geocoding API is free and returns structured
   results. Commas in location strings can cause 0 results (API limitation).
4. currency_exchange_rates: frankfurter.app moved from .app to .dev domain.
   Always add follow_location to stream contexts for API calls.
5. image_text_overlay: GD built-in fonts work everywhere (no TTF needed).
   imagecolorallocatealpha handles opacity. Always save overlay output as
   PNG to preserve alpha/transparency.
6. This session's pattern: 5 frontiers completed — web automation, chart
   generation, geocoding, forex rates, and image manipulation. All verified.