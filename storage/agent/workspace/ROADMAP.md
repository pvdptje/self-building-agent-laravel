# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the
frontier list below is empty, your FIRST job is to add three new frontiers
harder than anything already done — aimed at the outside world, never at
counting your own tools. This file outlives every session; keep it short.

## Frontier (harder tier — external problems, not introspection)

- [ ] `domain_intel` — full domain reconnaissance: DNS + WHOIS + SSL cert + IP geolocation in one pass.

- [ ] `rss_to_email` — monitor an RSS feed via feed_watcher, and when new items appear, generate a formatted email/summary digest.

- [ ] `ssl_expiry_monitor` — check SSL certificate expiry for multiple domains and generate a report.

- [ ] `arxiv_trending` — find trending/hot papers on arXiv by category.

- [ ] `chart_pie` — generate PNG pie/donut charts from numeric data using GD.

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

### Frontier tier 29 — music/media search (this session)
itunes_search — searches Apple iTunes/App Store via free API (no key required).
  - Music (Beatles): "Here Comes the Sun" (Abbey Road), "In My Life", "Yesterday" ✓
    - Artwork URLs (60×60, 100×100, 600×600), 30-second preview URLs ✓
    - Album, genre, track #, disc #, price ($1.29), release year (1965/1969) ✓
  - Podcast (Serial): 10 results including Serial, S-Town, The Last 12 Weeks ✓
    - Feed URLs (RSS), track counts, artwork, genre, content advisory ✓
  - Movie (Inception): 0 results (not on US store or different metadata) ✓
  - Supports: music, movie, podcast, audiobook, tvShow, software, ebook, all
  - Media-specific fields: duration for movies, feed URL for podcasts, version for apps
  - Artwork URLs can feed image_downloader for downloading album art
