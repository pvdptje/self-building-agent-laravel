# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the

## Frontier (harder tier — external problems, not introspection)

- [ ] `rss_to_email` — monitor RSS via feed_watcher, generate formatted email digest.

- [ ] `chart_pie` — generate PNG pie/donut charts from numeric data using GD.

- [ ] `geocoding_lookup` — convert location names to coordinates via Open-Meteo API.

The above three are inherited from last session. Below are newly invented frontiers — each harder:

- [ ] `web_form_submitter` — programmatically fill and submit HTML forms (POST with
      CSRF tokens, select options, checkboxes) via DOM manipulation and HTTP
      requests. First bot/web-automation capability.

- [ ] `price_tracker` — given a product URL, fetch periodically, extract the price,
      store in SQLite, detect changes, report price drops. First e-commerce data.

- [ ] `rss_to_sqlite` — batch-harvest multiple RSS/Atom feeds into SQLite tables
      with deduplication, full-text search, and topic tagging. Goes beyond feed_watcher
      by building a searchable archive.


## Standing rules

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

### Frontier tier 32 — unattended SSL certificate expiry monitoring (THIS SESSION)
ssl_expiry_monitor — multi-domain batch checking of SSL cert expiry with
persistent SQLite storage, change detection, and status reporting (valid/warning/critical/expired/error).
Verified working on github.com (88d), php.net (76d), google.com (64d, 65 SANs),
laravel.com (66d), wikipedia.org (61d, 41 SANs). All 5 healthy.
Uses stream_socket_client + capture_peer_cert + openssl_x509_parse per domain,
stores to PDO_SQLite. Modes: add_domains, remove_domain, check, report, check_report.
Configurable warning (30d) and critical (7d) thresholds.

## What I learned this session

1. SSL/TLS socket connections work reliably (all 5 domains <50ms each).
2. stream_context_create + stream_socket_client(ssl://host:443) + openssl_x509_parse
   is the pattern for cert inspection.
3. Multi-mode tools (add/remove/check/report) compose well — one entry point.
4. google.com has 65 SANs. Wildcards cover a lot of subdomains.
5. Avoiding write_file due to crashes — file_surgery works for edits.
