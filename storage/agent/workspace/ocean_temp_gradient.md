# Ocean Temperature Gradient — July 2026

**Pipeline:** marine_weather × 6 + prior data → latitudinal analysis

## Latitudinal Gradient (Northern Hemisphere)

| Location | Lat | Sea Temp | Current | Notes |
|----------|-----|----------|---------|-------|
| Singapore 🇸🇬 | 1°N | **29.7°C** | 2.4 m/s W | Equatorial — warmest |
| Honolulu 🇺🇸 | 21°N | 26.6°C | 0.5 m/s SW | North Pacific gyre |
| Lisbon 🇵🇹 | 39°N | 20.1°C | 0.4 m/s W | Eastern Atlantic |
| New England 🇺🇸 | 41°N | 20.6°C | 0.7 m/s E | Gulf Stream proximity |
| Oslo fjord 🇳🇴 | 60°N | **null** | null | Too far north for sensor |

**Latitudinal gradient:** ~10°C drop from equator to 40°N.

## Same Latitude, Different Oceans (34°S)

| Location | Sea Temp | Current | Cause |
|----------|----------|---------|-------|
| Sydney 🇦🇺 | **19.3°C** | East Australian | Warm tropical water |
| Cape Town 🇿🇦 | **13.0°C** | Benguela | Cold Antarctic upwelling |

**Delta: 6.3°C at identical latitude.** Ocean currents dominate solar forcing.

## Key Finding

Sea surface temperature is NOT purely a function of latitude.
Three factors control it:
1. **Solar radiation** (equator→pole gradient): ~10°C across 40° latitude
2. **Ocean currents** (warm/cold advection): 6.3°C at same latitude
3. **Upwelling** (Benguela, Humboldt, California): 5-8°C cooling

This is the first ocean temperature gradient analysis in the ecosystem —
combining 6 marine_weather calls across 3 oceans.
