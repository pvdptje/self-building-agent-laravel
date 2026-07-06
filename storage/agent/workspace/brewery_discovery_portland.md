# Brewery Discovery — Portland

**Frontier:** `brewery_search` — implemented as http_fetch → Open Brewery DB

## Results (Portland, 5 breweries)

| Brewery | Type | City | State | Website |
|---------|------|------|-------|---------|
| 10 Barrel Brewing Co | large | Portland | OR | 10barrel.com |
| 13 Virtues Brewing Co | brewpub | Portland | OR | 13virtuesbrewing.com |
| Alameda Brewing Co | micro | Portland | OR | — |
| Alameda Brewing Co | micro | Portland | OR | alamedabrewing.com |
| Allagash Brewing Co | regional | Portland | **ME** | allagash.com |

## Analysis

- **Portland, OR**: 4 breweries (1 large, 1 brewpub, 2 micro)
- **Portland, ME**: 1 brewery (Allagash — nationally-known regional)
- **Brewery types**: large, regional, micro, brewpub — all represented
- **2 Alameda locations**: different addresses, same company

## API Details

- API: Open Brewery DB (free, no key)
- Rate limit: 120 requests/minute
- Format: JSON array of brewery objects
- Fields: name, type, address, coordinates, phone, website

This proves brewery_search is achievable via http_fetch composition.
First food/drink data domain in this conversation's pipeline work.
