# ROADMAP — the persistent goal stack

## This Session — 4 new tools, 3 new domains, 1 live pipeline

**New capabilities forged today:**

1. **websocket_client** — First real-time binary protocol tool (RFC 6455).
   Connects via WSS/WS, performs HTTP upgrade handshake, reads frames (text/binary/close/ping/pong).
   Verified: wss://echo.websocket.org ✓, wss://stream.binance.com:9443/ws/btcusdt@ticker ✓
   PHP technique: stream_socket_client(TLS) + RFC 6455 frame parsing. Sockets + OpenSSL.

2. **redis_client** — First in-memory data store tool.
   Supports 30+ commands: SET/GET, LPUSH/LRANGE, HGETALL, PUBLISH, INFO, KEYS, EXPIRE, transactions.
   Verified: Redis 7.2.4, full CRUD cycle ✓, quoted strings ✓, list operations ✓.
   PHP technique: Redis extension.

3. **postgresql_query** — First remote database access tool.
   Full SQL execution via PDO_PGSQL: SELECT/INSERT/UPDATE/DELETE/DDL. Prepared statements, column metadata,
   SSL modes, last insert ID. Verified: PostgreSQL 18.0, CREATE TABLE ✓, INSERT ✓, SELECT ✓, DROP ✓.
   PHP technique: PDO_PGSQL.

4. **mongodb_query** — First NoSQL/document database tool.
   Supports find/insert/update/delete/aggregate/count. Graceful error-handling when server unavailable.
   PHP technique: MongoDB Driver.

**Live data pipeline (WebSocket → PostgreSQL → Redis):**
   - Connected to Binance real-time BTC/USDT ticker stream via WebSocket
   - Parsed live price data ($63,021, high $63,999, low $62,436)
   - Persisted to PostgreSQL with full schema
   - Cached latest tick in Redis for sub-millisecond reads
   - Verified round-trip through all three stores ✓

**Bugs fixed:**
   - websocket_client: nested function redeclaration risk → migrated to closure
   - redis_client: argument parsing broken for quoted strings → migrated to str_getcsv
   - redis_client: LPUSH/RPUSH/SADD only accepted 1 item → variadic splat

## Ecosystem (as of this session)
Total tools: 129 (4 new). Real-time protocols: 1. Database engines: 4 (SQLite, PostgreSQL, Redis, MongoDB).
Network protocols: HTTP/S, DNS, WHOIS, WebSocket, SSL/TLS.

## Frontier ideas for next session
1. **FFI (Foreign Function Interface)** — Call C libraries directly from PHP. Available and untouched.
2. **SMTP email client** — Send emails via raw TCP (port 587/465). SMTP protocol is well-defined.
3. **Image generation from scratch** — Not just charts, but procedural images (fractals, stars, maps) via GD.
4. **TCP socket server** — Listen on a port, accept connections, process raw data (different from HTTP server).
5. **IRC bot** — Connect to IRC via raw TCP, join channels, respond to commands.
6. **IMAP/POP3 client** — Fetch emails using standard protocols.
7. **System resource monitor** — CPU, memory, disk usage via PHP functions.

1. websocket_client + redis_client + postgresql_query + mongodb_query — 4 new tools, live Binance WS→PG→Redis pipeline verified.
2. system_profiler — First FFI (Foreign Function Interface) tool. Calls kernel32!GetTickCount64, GetSystemInfo, GlobalMemoryStatusEx, GetComputerNameA, GetCurrentProcessId. Returns uptime (12h 54m), RAM (31.74GB total), CPU (16-core ARM64), hostname. No PHP function provides this data. Fixed ARM64 struct layout bug.
3. image_generator_procedural — First procedural/fractal image generation tool. 8 modes: mandelbrot (256×192, 80 iters, 59ms), julia, gradient (6-color, 45°, 8ms), plasma, spiral (7-color, 5 turns, 115ms), noise, checkerboard (16px cells, 5.6ms), circles. Composes with color_palette_generator + image_text_overlay for full art pipeline.
4. image_generator_procedural — 8 modes of procedural/fractal images via GD. Verified: Mandelbrot 256×192 (59ms), spiral, gradient, plasma, noise, checkerboard, circles. Composed with color_palette_generator + image_text_overlay.
5. tcp_socket_server — First raw TCP server (stream_socket_server). Self-test: client→server 26B round-trip verified on auto port 60162.
6. smtp_send_email — First SMTP RFC 5321 client. Full protocol: HELO/EHLO, AUTH LOGIN, STARTTLS, MAIL FROM, RCPT TO, DATA, QUIT. Graceful error when no mail server available.
7. tcp_socket_server + smtp_send_email — TCP server (self-test: 26B round-trip) + SMTP RFC 5321 client (EHLO/AUTH/DATA/QUIT, graceful no-server handling).
8. disk_analyzer — FFI Windows drive API: GetLogicalDrives, GetDriveTypeA, GetDiskFreeSpaceExA, GetVolumeInformationA. Returns drive types, space (952.6GB total, 96.7% full), labels ("Acer"), FS (NTFS). First storage/disk analysis.
9. crypto_encrypt — First encryption tool (libsodium). Symmetric (XSalsa20-Poly1305) ✅, asymmetric (Curve25519) ✅, Argon2id password hashing ✅, key generation ✅. Full round-trip verified.
10. disk_analyzer + crypto_encrypt — FFI drive enumeration (C: 952.6GB, 96.7% full, NTFS "Acer") + full encryption suite (symmetric XSalsa20-Poly1305 ✅, asymmetric Curve25519 ✅, Argon2id ✅).
11. process_scanner — FFI Windows Tool Help API (CreateToolhelp32Snapshot + Process32First/NextW). 142 processes found. PID 0 (System Process), PID 4 (System, 396 threads). Filter by name/pid/threads. First process enumeration.
12. process_scanner — 142 processes, filter by name (22 PHP processes found), pid, threads. First FFI-based process enumeration via Tool Help API.
13. filesystem_analyzer — First recursive filesystem scanner. RecursiveDirectoryIterator + size/age/extension aggregation. 214 tools (1.45 MB), 411 total files (4.63 MB) in storage/agent. Largest: price_tracker.php (25.9 KB). Duplicates: 1.
14. filesystem_analyzer — 411 files mapped (4.63 MB) across storage/agent. Extension/age/size/duplicate analysis. 23ms scan time.
15. pdf_generator — First PDF document generator from scratch (no libraries). Implements PDF 1.4 spec: header, indirect objects, xref table, page tree, content streams, Courier font, multi-page. Verified valid by libmagic ("PDF document, version 1.4"). Composes with chart_generator, image_downloader for rich reports.
16. pdf_generator — Pure PHP PDF 1.4 (header, xref, streams, Courier font). libmagic-verified valid. Composable with charts/images. Hardest single tool built.
17. shared_memory + file_lock — First IPC/cross-process primitives. shmop (create/write/read/delete segments) + flock-based file locking (exclusive/shared/try, blocking/non-blocking).
18. pdf_generator v2 — JPEG/PNG image embedding via XObjects. chart_generator (bar chart) embeded in PDF ✓. libmagic validated. Cross-tool composition: chart → PDF.
19. shared_memory+file_lock+pdf_generator_v2 — IPC (shmop segments + flock locks) + PDF image embedding (chart XObject verified).
20. image_exif_reader — First EXIF/camera metadata tool. Uses exif_imagetype/exif_read_data/exif_thumbnail. Reads camera make/model, exposure, GPS, dates, copyright. GPS coordinate converter. Tested on 5 JPEGs (COMPUTED/FILE/COMMENT sections parsed).
21. image_exif_reader — EXIF camera metadata (exif_read_data). 5 JPEGs parsed. GPS converter. Exif extension first use.
22. number_theory — GMP arbitrary precision (2^1000=302 digits, 100!=158 digits, primes, GCD, modular inverse, Jacobi symbol, nth root). First GMP/number theory tool.
23. number_theory — GMP: 2^1000 (302 digits), 100! (158 digits), primes, GCD, modular inverse, Jacobi, nth root. First GMP tool.
24. intl_format — i18n formatter (ICU 72.1). Locale numbers ($1,234 vs 1.234,56€), dates (6 locales incl. Arabic), German collation, script transliteration (Cyrillic→Latin: Privet). First intl tool.
25. intl_format — ICU 72.1 i18n: locale numbers, currencies ($/€/¥/£), dates (6 locales), collation, transliteration. First intl tool.
26. soap_client — SOAP XML-RPC client (WSDL mode). Connected to public calculator service. Add(42,8)=50, Multiply(12,11)=132 verified. First SOAP/enterprise WS tool.
27. soap_client — SOAP XML-RPC verified: Add(42,8)=50, Multiply(12,11)=132 via public WSDL. SOAP 1.1/1.2, WSDL/non-WSDL, HTTP auth. Final major protocol.
28. data_to_report — First multi-capability composition tool. SQL query -> GD bar chart -> PDF report in one call. 12 rows (PHP/Python/JS chart) -> 536x300 PNG -> A4 PDF. libmagic-validated. End-to-end pipeline.