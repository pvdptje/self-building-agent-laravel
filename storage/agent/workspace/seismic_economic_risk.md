# Seismic-Economic Risk Index — July 2026

**Pipeline:** earthquake_monitor + csv_to_sqlite + markdown_table_export + country_info → synthesis

## Earthquake Exposure by Country

| Region | Quakes (7d) | Max Mag | Country GDP/capita | Risk Profile |
|--------|------------|---------|-------------------|-------------|
| Chile | 3 | M5.0 | $17,093 | Moderate GDP + high exposure |
| China (Sichuan) | 2 | M5.0 | $13,136 | Moderate GDP + intraplate risk |
| Indonesia | 2 | M5.1 | $5,109 | Low GDP + subduction zone |
| Fiji | 1 | M5.8 | $5,900 | Low GDP + deep W-B zone |
| Philippines | 1 | M5.2 | $3,950 | Low GDP + Ring of Fire |
| Papua New Guinea | 1 | M5.1 | $2,500 | Very low GDP + complex tectonics |
| Russia (Kurils) | 1 | M5.3 | $13,165 | Moderate GDP + remote |
| Atlantic Ridge | 3 | M5.0 | N/A (oceanic) | No population |

## Risk Classification

| Risk Level | Countries | Characteristics |
|------------|-----------|-----------------|
| **High** | Chile, China | Moderate GDP × active tectonics × populated areas |
| **Moderate** | Indonesia, Philippines, Russia | Active tectonics but lower GDP or remote |
| **Low** | Fiji, PNG, Antarctic | Deep/remote quakes, minimal population exposure |

## Key Insight

Chile has the highest combined risk: 3 quakes in one week, all shallow (10-50km),
with $17K GDP — infrastructure exists to be damaged. China's Sichuan cluster
had 10+ felt reports despite only M5.0 — high population density amplifies risk.

The Atlantic Ridge quakes (3 × M5.0) pose zero risk — they're mid-ocean
divergent boundary events with no population within hundreds of km.

This is the first seismic-economic risk assessment in the ecosystem —
combining geological hazard data with economic exposure metrics.
