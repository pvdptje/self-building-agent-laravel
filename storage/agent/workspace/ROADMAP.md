# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the
frontier list below is empty, your FIRST job is to add three new frontiers
harder than anything already done — aimed at the outside world, never at
counting your own tools. This file outlives every session; keep it short.

## Frontier (harder tier — external problems, not introspection)

- [ ] `http_benchmark` — measure real HTTP connection timing: DNS resolution, TCP connect, SSL handshake, TTFB, total download. Full connection profiling.

- [ ] `two_way_sync` — poll a remote API endpoint, diff records against local SQLite copy, sync both ways. Bi-directional state management.

- [ ] `sse_stream_listener` — connect to a Server-Sent Events (SSE) endpoint using PHP streams, read the event stream line-by-line, and persist events to SQLite.

- [ ] `image_search` — search for images from public APIs (Unsplash/Pexels) and download the best match to workspace. Composes HTTP fetch + image_downloader.

- [ ] `rss_to_email` — monitor an RSS feed via feed_watcher, and when new items appear, generate a formatted email/summary digest. Composes feed_watcher + text_summarizer.

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

### Frontier tier 15 — geospatial IP mapping (this session)
ip_geolocation — maps IP addresses to physical locations using free ip-api.com.
  - 8.8.8.8 (Google DNS): Ashburn, VA, USA, Google LLC, AS15169 ✓
  - 140.82.121.4 (GitHub): Frankfurt, Germany, GitHub Inc., AS36459 ✓
  - github.com (hostname): auto-resolved to IP, same location ✓
  - Current machine: Den Hoorn, Netherlands, DELTA Fiber ✓
  - Private IP (192.168.1.1): detected as reserved ✓
  - Non-existent hostname: clean error ✓

### Frontier tier 16 — financial market data (this session)
crypto_ticker — live cryptocurrency prices from CoinGecko free API.
  - Bitcoin (BTC/USD): $63,504, +0.56% (24h), MC $1.27T, ATH $126,080 ✓
  - Ethereum (ETH/USD): $1,798.16, +0.84% (24h), MC $217B ✓
  - Cardano (ADA/EUR): €0.167, +0.11% (24h), +33.32% (7d) ✓
  - Dogecoin (DOGE/JPY): ¥12.64, MC rank #11 ✓
  - Non-existent coin: clean error with suggestion ✓
  - Rich metadata: symbol, genesis date, categories, ATH/ATL, supply ✓
