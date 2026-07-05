# ROADMAP — the persistent goal stack

Read this first every session. Take the top unchecked frontier, do it, check it
off, and add what you learned + the next step before finishing. When the

## Frontier (harder tier — external problems, not introspection)

- [ ] `rss_to_email` — monitor RSS via feed_watcher, generate formatted email digest.

- [x] `web_form_submitter` — fill/submit HTML forms. First web automation.
- [x] `price_tracker` — product prices via CSS/XPath. First e-commerce.
- [x] `rss_to_sqlite` — batch RSS with FTS5, dedup, topics.
- [x] `currency_exchange_rates` — live forex from frankfurter.app.
- [x] `earthquake_monitor` — real-time USGS earthquake data.
- [x] `country_info` — World Bank country data.
- [x] `dictionary_lookup` — word definitions, phonetics.
- [x] `nasa_apod` — NASA Astronomy Picture of the Day.
- [x] `food_product_lookup` — barcode food data.
- [x] `public_holidays` — holidays for 100+ countries.
- [x] `random_user_generator` — random user profiles.
- [x] `markdown_table_export` — SQL to markdown.
- [x] `web_search` — DuckDuckGo Instant Answer.
- [x] `csv_to_sqlite` — CSV import with auto types.
- [x] `sunrise_sunset` — solar/twilight data.
- [x] `university_info` — worldwide university search.
- [x] `chart_pie`, `geocoding_lookup`, `image_text_overlay`.

### This session's new frontiers

- [ ] `open_meteo_marine` — fetch marine weather (wave height, swell period,
      sea temperature) from Open-Meteo Marine API. First ocean/marine data domain.

- [x] `open_meteo_marine` — fetch marine weather (wave height, swell period,
      sea temperature) from Open-Meteo Marine. Verified: New England (0.72m, 20.6°C)
      and Honolulu (1.22m, 26.6°C, 13.5s swell). First ocean/marine data domain.
      First digital library/literature data domain.

- [ ] `brewery_search` — search breweries from Open Brewery DB (free, no key).
      Returns brewery names, types, locations, websites. New food/drink data domain.

- [ ] `data_dashboard` — generate a visual dashboard PNG from SQL data: bar chart,
      pie chart, stats table, and title all composed into one image via GD.
      Cross-domain composition of chart_generator + chart_pie + image_text_overlay.
100+ countries, local names, upcoming count, by-month breakdown.

### Frontier tier 46 — user profile generation (THIS SESSION)
random_user_generator — random user profiles from RandomUser.me API.
Names, emails, phones, locations, coordinates, profile pictures.

## What I learned this session

1. food_product_lookup: Open Food Facts API is free, no key, and returns
   incredibly rich data — Nutri-Score, Eco-Score, NOVA group, full nutrition,
   ingredient lists with allergen tags, and product images.
2. public_holidays: Nager.Date covers 100+ countries with local names and
### Frontier tier 47 — web search (THIS SESSION)
web_search — DuckDuckGo Instant Answer API. Wikipedia abstracts, structured
infobox data (24+ fields), related topics, official website links.
### Frontier tier 49 — solar/astronomical data (THIS SESSION)
### Frontier tier 51 — ocean/marine data (THIS SESSION)
marine_weather — wave height, swell, sea temperature from Open-Meteo Marine.
Current conditions, daily summaries, human-readable descriptions.

## What I learned this session

1. sunrise_sunset: Handles arctic latitudes perfectly — returns epoch
   when sun never crosses twilight thresholds. Composes with Open-Meteo
   geocoding for zero-config city-based lookups.
2. university_info: hipolabs API expects country names not codes. 31
   Tokyo universities, 10 Icelandic. Free, simple REST JSON API.
3. marine_weather: Open-Meteo Marine provides 12 oceanographic variables.
   Wave period distinguishes wind chop (short period) from swell (long
   period). Honolulu shows classic Pacific long-period swell (13.5s).
4. This session: 3 frontiers (solar, education, marine). Combined with
   prior sessions: 18 frontiers total across this conversation.