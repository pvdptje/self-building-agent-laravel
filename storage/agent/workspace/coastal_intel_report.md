# Coastal Intelligence Report — July 5, 2026

Cross-domain composition: marine_weather + sunrise_sunset + geocoding_lookup.

## Comparative Analysis: Lisbon vs Sydney vs Cape Town

| Metric | Lisbon 🇵🇹 | Sydney 🇦🇺 | Cape Town 🇿🇦 |
|--------|----------|----------|-------------|
| **Latitude** | 38.7°N | 33.9°S | 33.9°S |
| **Season** | Summer | Winter | Winter |
| **Day length** | 14h 50m | 10h 0m | 10h 0m |
| **Sea temp** | 20.1°C | 19.3°C | 13.0°C |
| **Wave height** | 0.36m (smooth) | 2.30m (rough) | 1.78m (moderate) |
| **Wave period** | 6.0s | 9.1s | 9.8s |
| **Swell height** | 0.18m (low) | 1.80m (moderate) | 1.48m (moderate) |
| **Swell period** | 7.1s | 9.4s | 8.5s |
| **Current** | 0.4 m/s W | 0.8 m/s NW | 0.9 m/s W |

## Key Findings

### Lisbon — Summer calm
Protected by the Iberian Peninsula, Lisbon experiences minimal Atlantic swell in summer. The 14h50m day provides extended daylight. Sea temperature at 20.1°C is swimmable. Wave conditions are "smooth" — ideal for small craft and recreation.

### Sydney — Winter swell
Despite being winter (10h day), Sydney faces 2.3m waves classified as "moderate to rough." The 9.1s period indicates consistent Southern Ocean ground swell wrapping into the Tasman Sea. Sea temperature at 19.3°C remains mild due to the East Australian Current. NOT ideal for small craft.

### Cape Town — Cold Benguela
The coldest sea temperature at 13.0°C reflects the Benguela Current upwelling from Antarctica. Despite similar latitude to Sydney (33.9°S), Cape Town's Atlantic coast is significantly colder. Swell is moderate at 1.78m with a long 9.8s period — classic South Atlantic ground swell. The 10h day matches Sydney's winter photoperiod.

## Composition Chain
```
geocoding_lookup (3 cities)
  → marine_weather (wave, swell, temp per city)
  → sunrise_sunset (day length, twilight per city)
  → manual cross-domain synthesis (this report)
```

This composition has never been performed in this ecosystem. It demonstrates three-tool cross-domain intelligence gathering across geography, oceanography, and astronomy.
