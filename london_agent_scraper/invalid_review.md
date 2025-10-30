# Invalid Contact Review Log

## Review ownership
- **Primary reviewer:** Alex Morgan (Operations)
- **Backup reviewer:** Priya Shah (Marketing Ops)

## Review cadence
- Run invalid triage within 48 hours of each scrape.
- Update this log whenever an `invalid_needing_research.csv` file is produced.

## Triage criteria
1. **Broken website** – the company domain no longer resolves or returns 4xx/5xx.
2. **Placeholder email** – obvious catch-alls such as `info@example.com` or image filenames.
3. **Missing email** – the scraper found no address. Prioritise these when the
   invalid report flags the `missing-email` reason.
4. **Contact form only** – the site exposes a form but no dedicated email address.
5. **Recruitment / careers** – mailboxes dedicated to hiring should not enter outreach lists.
6. **Generic catch-all** – `info@`, `hello@`, etc., are acceptable if no better option exists.
7. **Other** – add context (e.g., GDPR block, duplicate listing) in the notes column.

## Workflow
1. Open `invalid_needing_research.csv` and filter by triage status.
2. Investigate each row by visiting the company website or searching for verified contacts.
3. Record findings in the log with one of the criteria above.
4. Flag any promising leads back to the scraping backlog for prioritised follow-up.
5. Once complete, export the updated log alongside the archived run folder.

## Tracking table template

| Company | Website | Email candidate | Finding | Action | Reviewer | Date |
|---------|---------|-----------------|---------|--------|----------|------|
| Example Estates | https://example-estates.co.uk | info@example-estates.co.uk | Contact form only | Added to follow-up backlog | Alex Morgan | 2025-10-30 |

The `invalid_reason` column in `london_agents_companies_invalid.csv` should be
copied into the “Finding” column when creating a new review entry. Use this
table for each archive under `archives/<run-date>/invalid_review.md`.
