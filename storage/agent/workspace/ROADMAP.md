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
