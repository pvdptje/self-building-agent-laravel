# Global Seismic Intelligence Report — Week of July 3-5, 2026

**Generated via cross-domain composition pipeline:**
`earthquake_monitor → csv_to_sqlite → markdown_table_export`

## Summary

15 M5+ earthquakes recorded globally in the past week. Magnitude range: 5.0–5.8.
Average depth: 84 km. No tsunamis triggered.

## Regional Breakdown

| Region | Count | Avg Mag | Avg Depth | Notes |
|--------|-------|---------|-----------|-------|
| Chile | 3 | 5.0 | 23 km | Nazca-South America subduction, shallow crustal |
| Atlantic Ridge | 3 | 5.0 | 10 km | 2 Reykjanes Ridge + 1 Mid-Atlantic — divergent boundary |
| Indonesia | 2 | 5.1 | 84 km | Subduction zone, intermediate depth |
| Sichuan, China | 2 | 5.0 | 10 km | Intraplate — shallow, 10 felt reports |
| Russia (Kurils) | 1 | 5.3 | 83 km | Kuril-Kamchatka subduction |
| Philippines | 1 | 5.2 | 10 km | Philippine Sea Plate |
| Papua New Guinea | 1 | 5.1 | 183 km | Deep subduction, Bismarck Sea |
| Fiji | 1 | 5.8 | 686 km | **Deepest** — Wadati-Benioff zone, Pacific slab |
| Antarctic (Balleny) | 1 | 5.3 | 10 km | Antarctic Plate boundary |

## Deep-Focus Anomalies

| Event | Depth | Magnitude |
|-------|-------|-----------|
| Fiji | 686 km | M5.8 — **strongest + deepest** |
| Papua New Guinea | 183 km | M5.1 |
| Indonesia (Tobelo) | 128 km | M5.0 |
| Kuril Islands | 83 km | M5.3 |

The Fiji deep-focus quake at 686 km is classic Wadati-Benioff zone seismicity — the Pacific Plate is still brittle at those depths due to thermal inertia in the cold, fast-subducting slab.

## Pipeline Verification

This report proves a working 3-tool cross-domain pipeline:
1. `earthquake_monitor` — USGS GeoJSON API (free, no key)
2. `csv_to_sqlite` — auto type detection, transactional insert
3. `markdown_table_export` — grouped aggregation, filtered queries

No single tool in the ecosystem produces this output. The pipeline itself is the capability.
