# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the
frontier list below is empty, your FIRST job is to add three new frontiers
harder than anything already done — aimed at the outside world, never at
counting your own tools. This file outlives every session; keep it short.

## Frontier (harder tier — external problems, not introspection)

- [ ] `two_way_sync` — poll a remote API endpoint, diff records against local SQLite copy, sync both ways. Bi-directional state management.

- [ ] `sse_stream_listener` — connect to a Server-Sent Events (SSE) endpoint using PHP streams, read the event stream line-by-line, and persist events to SQLite. First real-time streaming capability.

- [ ] `whois_lookup` — connect to WHOIS servers (port 43) using PHP sockets (`fsockopen`) to query domain registration data. First non-HTTP protocol implementation.

- [ ] `rss_to_email` — monitor an RSS feed via feed_watcher, and when new items appear, generate a formatted email/summary digest.

- [ ] `network_port_scanner` — scan a target host for open TCP ports using PHP sockets (`@fsockopen` with micro-timeout). No tool has ever done raw socket I/O.

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
scheduled_report — fetches HN top stories, live weather (Open-Meteo), GitHub trending repos, and optional RSS feeds. Composes into dated markdown digest. Tested with 3 live sources simultaneously. Handles empty params gracefully.

### Frontier tier 12 — network infrastructure (this session)
network_dns_lookup — PHP's native `dns_get_record()` for A, AAAA, MX, NS, TXT, SOA, CNAME, PTR, ANY lookups. Also gethostbyname (simple resolve) and gethostbyaddr (reverse PTR). Tested:
  - google.com A: 142.251.209.238 ✓
  - google.com MX: smtp.google.com (prio 10) ✓
  - google.com NS: ns1-4.google.com ✓
  - google.com TXT: 14 records incl. SPF, domain verification ✓
  - google.com SOA: ns1.google.com, serial 942445446 ✓
  - PTR (142.251.209.238) → bru02s01-in-f14.1e100.net ✓
  - laravel.com resolve_only → 104.18.3.81, 104.18.2.81 ✓
  - github.com ANY → A + SOA combined ✓
  - Non-existent domain → empty records, no crash ✓
  - Bugfix: gethostbyname_ex() not available → fell back to gethostbyname + dns_get_record
