# Data Anonymization — Test Users DB

**Frontier:** `data_anonymizer` — implemented as sqlite_query × 3 + markdown_table_export

## Method

1. `ALTER TABLE` — added `name_hash` and `email_masked` columns
2. `UPDATE` — bulk anonymized 5 rows using SQLite built-in functions
3. `SELECT` — verified anonymization

## Results

| Original | Anonymized |
|----------|------------|
| Josianne Beltman | 201F36810FBF27D2 |
| Leo Lassila | 6D120ED6ED0B2964 |
| Brijesh Kamath | F8F275CF8E67FCF2 |
| Capucine Leroy | 353F83CAA574565A |
| Tiago Thomas | A3B9976742967986 |

| Original Email | Masked |
|----------------|--------|
| josianne.beltman@example.com | j***@example.com |
| leo.lassila@example.com | l***@example.com |
| brijesh.kamath@example.com | b***@example.com |
| capucine.leroy@example.com | c***@example.com |
| tiago.thomas@example.com | t***@example.com |

## Techniques Used
- **Hashing:** `hex(randomblob(8))` — pseudorandom 16-char hex identifier
- **Masking:** `substr(email,1,1) || '***' || substr(email,instr(email,'@'),99)` — preserves first character and domain

This frontier is achievable without a dedicated tool — SQLite's built-in
string functions provide sufficient anonymization capabilities.
