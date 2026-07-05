# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the
frontier list below is empty, your FIRST job is to add three new frontiers
harder than anything already done — aimed at the outside world, never at
counting your own tools. This file outlives every session; keep it short.

## Frontier (harder tier — external problems, not introspection)

- [ ] `sse_stream_listener` — connect to a Server-Sent Events (SSE) endpoint using PHP streams, read the event stream line-by-line, and persist events to SQLite.

- [ ] `dataset_merge` — join/harvest from multiple APIs and merge related datasets in SQLite by matching foreign keys across tables.

- [ ] `rss_to_email` — monitor an RSS feed via feed_watcher, and when new items appear, generate a formatted email/summary digest.

- [ ] `http_timing_profiler` — measure real HTTP connection phases: DNS, TCP connect, SSL handshake, TTFB, total download.

- [ ] `domain_intel` — full domain reconnaissance: DNS + WHOIS + SSL cert + IP geolocation in one pass.

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

### Frontier tier 20 — SSL/TLS certificate inspection (this session)
ssl_cert_check — captures and parses peer certificates using stream_socket_client + openssl_x509_parse.
  - google.com: *.google.com wildcard, 65 SANs, GTS/WR2 issuer, 64 days remaining ✓
  - github.com: github.com, 2 SANs, Sectigo issuer, ECDSA sig, 88 days remaining ✓
  - Non-existent host: clean error, no crash ✓
  - Captures: subject, issuer, serial, signature algo, SANs, chain (depth 3) ✓
  - Computes: days remaining, expiry warnings, wildcard detection ✓
  - Bugfix: serial number can be decimal or hex — normalized to 0xNNN format
  - PHP technique: stream_context_create with capture_peer_cert + openssl_x509_parse
