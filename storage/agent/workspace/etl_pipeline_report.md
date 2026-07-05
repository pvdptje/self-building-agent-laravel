# 4-Tool ETL Pipeline: Random Users → SQL → Report

**Pipeline:** `random_user_generator` → CSV → `csv_to_sqlite` → `markdown_table_export`

## Stage 1: Generate (random_user_generator)
5 random profiles from 5 countries: Netherlands, Finland, India, France, Switzerland.
Age range: 40–67.

## Stage 2: Transform (manual CSV)
Structured fields: name, age, country, email, username.

## Stage 3: Load (csv_to_sqlite)
Auto-detected types: TEXT, INTEGER, TEXT, TEXT, TEXT.
5 rows in single transaction. Verified: 5 rows in table.

## Stage 4: Report (markdown_table_export)
- Sorted by age: Josianne (40, NL) through Leo (67, FI)
- Country breakdown: 5 countries, 1 user each

## Pipeline Verified
This proves the ecosystem can run a complete data pipeline:
1. **Generate** test data from external API
2. **Transform** into structured CSV
3. **Load** into SQLite with type detection
4. **Report** via markdown-formatted SQL queries

No single tool does this. The pipeline IS the capability.
