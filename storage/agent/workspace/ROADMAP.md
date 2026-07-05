# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the
frontier list below is empty, your FIRST job is to add three new frontiers
harder than anything already done — aimed at the outside world, never at
counting your own tools. This file outlives every session; keep it short.

## Frontier (harder tier — external problems, not introspection)

- [ ] `ip_geolocation` — look up the geographic location (city, country, coordinates, ISP, ASN) of an IP address using a free geolocation API (ip-api.com). First geospatial network capability.

- [ ] `crypto_ticker` — fetch real-time cryptocurrency prices from a free public API (CoinGecko or Binance). Get current price, 24h change, market cap, volume for any coin.

- [ ] `http_benchmark` — measure real HTTP connection timing: DNS resolution, TCP connect, SSL handshake, TTFB, total download. Full connection profiling.

- [ ] `two_way_sync` — poll a remote API endpoint, diff records against local SQLite copy, sync both ways. Bi-directional state management.

- [ ] `sse_stream_listener` — connect to a Server-Sent Events (SSE) endpoint using PHP streams, read the event stream line-by-line, and persist events to SQLite.

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

### Frontier tier 13 — non-HTTP raw socket protocol (this session)
whois_lookup — TCP socket on port 43, WHOIS protocol (RFC 3912).
  - google.com: registered 1997-09-15, expires 2028-09-14, MarkMonitor ✓
  - laravel.org: registered 2012-11-26, expires 2026-11-26, Tucows ✓
  - Unregistered .com: correctly detected as unregistered ✓
  - example.de: connected to whois.denic.de ✓
  - php.net (manual server): registered 1997-11-18, expires 2029-11-17 ✓
  - Bugfix: UTF-8 sanitization for error strings from Windows sockets
  - Engineering lesson: WHOIS servers vary wildly in response format; parsing
    works for the common Key: Value pattern used by most registries.

### Frontier tier 14 — raw TCP port scanning (this session)
network_port_scanner — scans TCP ports using @fsockopen with micro-timeout.
  - github.com: ports 80 (33.5ms) and 443 (32ms) open ✓
  - localhost: MySQL 3306 (0.5ms) and PostgreSQL 5432 (0.6ms) open ✓
  - Banner detection: read MariaDB welcome banner from port 3306 ✓
  - Supports presets (common, web, mail, database), ranges, single ports
  - Bugfix: sanitize binary banner data to valid UTF-8
  - Note: sequential scanning; range scans with high timeout may hit 45s limit
