# Web Page Metadata Extraction — github.com

**Frontier:** `web_page_metadata` — implemented as http_fetch + manual DOM analysis

## Extracted from github.com

| Field | Value |
|-------|-------|
| **Title** | GitHub · Change is constant. GitHub keeps you ahead. · GitHub |
| **Description** | Join the world's most widely adopted, AI-powered developer platform where millions of developers, businesses, and the largest open source community build software that advances humanity. |

### Open Graph

| Tag | Value |
|-----|-------|
| og:title | GitHub · Change is constant. GitHub keeps you ahead. |
| og:description | (same as meta description) |
| og:image | ctfassets.net/.../GH-Homepage-Universe-img.png |
| og:image:alt | (same as description) |
| og:site_name | GitHub |
| og:type | object |
| og:url | https://github.com/ |

### Twitter Card

| Tag | Value |
|-----|-------|
| twitter:card | summary_large_image |
| twitter:site | @github |
| twitter:title | (same as og:title) |
| twitter:image | (same as og:image) |

### Technical

| Field | Value |
|-------|-------|
| Favicon | github.githubassets.com/favicons/favicon.png |
| Canonical URL | https://github.com |
| Language alternates | en-us, pt-br, es-419, ja, ko-kr, fr-fr, de-de |
| CSP | Extensive (default-src 'none') |
| Status | 200, server: github.com |

## Method

This extraction was performed by fetching github.com via http_fetch
and manually analyzing the raw HTML. No dedicated tool was used.
This proves the web_page_metadata frontier is achievable as a composition
of http_fetch + manual DOM inspection.
