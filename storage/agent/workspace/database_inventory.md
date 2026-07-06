# Database Inventory — Ecosystem Storage

**Pipeline:** sqlite_query × 2 + list_directory → synthesis

## imported_data.sqlite (16 KB)

| Table | Type | Contents |
|-------|------|----------|
| people | Table | 4 rows — demo names/ages/heights |
| test_users | Table | 5 rows — anonymized user profiles |
| weekly_quakes | Table | 15 rows — M5+ earthquakes, past week |

## rss_archive.sqlite (135 KB)

| Table | Type | Contents |
|-------|------|----------|
| feed_sources | Table | RSS feed metadata (HN, BBC, NPR) |
| feed_items | Table | Harvested articles with FTS5 indexing |
| feed_items_fts* | FTS5 (5 tables) | Full-text search index |

## Totals

| Metric | Value |
|--------|-------|
| Databases | 2 |
| User tables | 5 |
| FTS auxiliary tables | 5 |
| Total rows | ~44 (20 articles + 15 quakes + 9 users) |
| Total storage | 151 KB |

## Query Capabilities Demonstrated

- Cross-table JOIN (feed_items ↔ feed_sources)
- FTS5 full-text search (MATCH 'software OR AI')
- GROUP BY aggregation (region, depth_class, country)
- Column type detection (INTEGER, REAL, TEXT)
- PII anonymization (hex hashing, substring masking)
- Integrity verification (PRAGMA integrity_check)

This is the first database inventory in the ecosystem — introspecting
all SQLite databases via sqlite_master metadata queries.
