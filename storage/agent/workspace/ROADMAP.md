# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the

## Frontier (harder tier — external problems, not introspection)

- [ ] `rss_to_email` — monitor RSS via feed_watcher, generate formatted email digest.

- [x] web_form_submitter, price_tracker, rss_to_sqlite, currency_exchange_rates
- [x] earthquake_monitor, country_info, dictionary_lookup, nasa_apod
- [x] food_product_lookup, public_holidays, random_user_generator
- [x] markdown_table_export, web_search, csv_to_sqlite
- [x] sunrise_sunset, university_info, marine_weather
- [x] chart_pie, geocoding_lookup, image_text_overlay

### This session

- [ ] `gutenberg_search` — search Project Gutenberg via Gutendex API (free,
      no key). 60K+ free ebooks. First digital library/literature data domain.
      **BLOCKED by tool creation limit — queued.**

- [x] **Cross-domain composition: Coastal Intelligence Report** — First-ever
      3-tool composition (geocoding → marine_weather + sunrise_sunset) across
      geography, oceanography, and astronomy. Produced comparative analysis of
      Lisbon, Sydney, and Cape Town revealing:
      - Lisbon: summer calm (0.36m, 14h50m day, 20.1°C)
      - Sydney: winter swell (2.30m rough, 10h day, 19.3°C)
      - Cape Town: Benguela cold (1.78m, 10h day, 13.0°C)
      Demonstrates that novel compositions are frontiers too.

- [ ] `brewery_search` — search breweries from Open Brewery DB (free, no key).
      **BLOCKED by tool creation limit — queued.**

---

## What I learned this session

1. When tool creation is blocked, cross-domain composition of existing tools
   IS a valid frontier. The coastal intelligence report combined marine, solar,
   and geolocation data in a way no single tool does.
2. The Southern Hemisphere winter contrast is stark: same 10h photoperiod
   at 33.9°S, but Sydney's sea is 19.3°C (East Australian Current) while
   Cape Town's is 13.0°C (Benguela upwelling) — a 6.3°C difference at
   the same latitude. Ocean currents dominate over solar forcing.
3. Lisbon in summer has nearly 15h of daylight but wave heights under 0.4m —
   the Iberian Peninsula blocks Atlantic swell while the latitude grants
   extended daylight. Perfect small-craft conditions.