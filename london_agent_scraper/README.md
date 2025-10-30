# London Agent Scraper

Utilities for scraping estate agent contact details and syncing them with FluentCRM now live in the
`london_agent_scraper/` directory. The scripts expect to be run from this folder (or with explicit
paths) so that the default configuration file can be located correctly.

## Live run workflow

1. **Validate configuration** – confirm API credentials and local setup with
   `python test_setup.py` (add `--skip-api-test` when running from a network
   without outbound access).
2. **Scrape company sites** – launch `python scraper_company_websites.py` to
   collect fresh contact data once connectivity to the allow-listed domains has
   been confirmed.
3. **Clean the output** – run `python clean_company_websites_csv.py` to remove
   placeholder addresses and split invalid rows to
   `london_agents_companies_invalid.csv` for manual inspection.
4. **Import or sync** – feed `london_agents_companies_cleaned.csv` into your CRM
   pipeline (e.g., FluentCRM via `scraper_to_fluentcrm.py --csv london_agents_companies_cleaned.csv`)
   and archive both the raw and cleaned CSV files along with the generated invalid report.

Record the scrape date, configuration version, and any follow-up actions so the
run remains auditable.

## Full scrape & import checklist

Use this checklist for each end-to-end run of the company-website scraper to
ensure all preparation, processing, and follow-up steps are covered.

### ✅ Pre-run: environment & configuration

- [ ] Confirm outbound HTTPS connectivity to all domains listed in
      `config_company_websites.json`, updating the allow list or proxy settings
      if necessary.
- [ ] Verify that `config_company_websites.json` itself is current and contains
      the full domain list for the upcoming scrape.
- [ ] Activate the Python virtual environment (if applicable) and install
      dependencies:

  ```bash
  pip install -r requirements.txt
  ```

- [ ] Run the setup test (add `--skip-api-test` when outbound access is
      restricted):

  ```bash
  python test_setup.py
  ```

- [ ] (Optional) Perform a dry run of the company scraper and confirm that the
      reported domains match expectations:

  ```bash
  python scraper_company_websites.py --dry-run
  ```

### 🕵️ Live scrape

- [ ] Execute the live scrape and monitor for proxy errors, blocked domains, or
      robots.txt restrictions:

  ```bash
  python scraper_company_websites.py
  ```

- [ ] Confirm that the configured output CSV (default
      `london_agents_companies.csv`) is generated successfully.

### 🧹 Post-scrape cleaning

- [ ] Clean the raw CSV and generate the invalid-report companion file:

  ```bash
  python clean_company_websites_csv.py london_agents_companies.csv \
    --invalid-report london_agents_companies_invalid.csv
  ```

- [ ] The cleaner strips common noise (`mailto:` prefixes, stray `u00xx`
      escape fragments, surrounding angle brackets) before validating email
      syntax and rejecting obvious placeholders such as `@example.com` or
      image filenames.
- [ ] Review `london_agents_companies_cleaned.csv` for valid contact rows and
      `london_agents_companies_invalid.csv` for entries that require manual
      follow-up.
- [ ] Manually investigate domains with missing or unusable emails and flag
      them for future review, capturing notes in the archived
      `invalid_review.md` file for audit purposes.

### 📝 Manual triage of invalids

- [ ] Export the rows needing research from `london_agents_companies_invalid.csv`
      into `invalid_needing_research.csv` (keep it alongside the cleaned CSV).
- [ ] Assign a reviewer (see `invalid_review.md`) to inspect the domains for
      each invalid entry and determine whether a usable contact exists.
- [ ] Categorise the findings using the shared criteria: broken website,
      placeholder email, contact form only, recruitment/careers inbox, or other.
- [ ] Update the review log with actions taken and whether the record should be
      retried in a future scrape cycle.

## Mailbox policy

The cleaning pipeline applies mailbox heuristics so that only outreach-ready
contacts make it into the cleaned CSV:

- **Denied mailboxes:** local parts containing `press`, `media`, `recruitment`,
  `careers`, `jobs`, `privacy`, `background`, `replytoaddress`, or any
  variation of `no-reply` / `donotreply` are rejected outright. They will
  appear in the invalid report for manual review if needed.
- **Preferred mailboxes:** addresses that start with or contain `info`,
  `contact`, `enquiry` / `enquiries`, `sales`, `lettings`, `customercare`,
  `customerservice`, `office`, or `hello` receive a score boost so they surface
  to the top of the cleaned output.
- **Quality gate:** if more than 30 % of the processed rows are rejected, the
  cleaner exits with `::error:: High invalid rate – manual review required.` so
  that CRM imports can be paused until the results are triaged.

### 📥 CRM import / sync

- [ ] Verify that the CRM import script (`scraper_to_fluentcrm.py`) is
      configured correctly:
      - Use `--csv <cleaned_csv_path>` when performing a real import.
      - Combine `--csv` with `--dry-run` to review the payload without calling
        the API.
      - Use `--limit <n>` to stage a 5–10 row sample before the full import.
- [ ] Confirm that the cleaned CSV still exposes the expected columns
      (`company_name`, `website`, `email`) so CRM mappings remain valid.
- [ ] Import a small sample (5–10 rows) to confirm tags, lists, and custom
      fields behave as expected.
- [ ] Import the full cleaned list and check the CRM for duplicates or errors.

### 🗂 Archiving & logging

- [ ] Create an archive folder such as `archives/YYYY-MM-DD/` (relative to the
      scraper directory) and move the raw, cleaned, and invalid CSV files along
      with any log output into it. The helper below copies the current run and
      writes a manifest:

  ```bash
  python archive_run.py --run-date "$(date +%F)"
  ```
- [ ] Record run metadata, including the date, script/configuration versions,
      and the counts of valid versus invalid rows.
- [ ] Apply your retention policy (e.g., keep the most recent 12 months of
      archives) by pruning older runs as required.

### 🔁 Automation & scheduling

- [ ] If the scraper is scheduled, confirm the cron (or equivalent) entry is in
      place and the log location is correct:

  ```bash
  30 1 * * * cd /path/to/london_agent_scraper && /usr/bin/python3 \
    scraper_company_websites.py >> /var/log/london_agents_scraper.log 2>&1
  ```

- [ ] Ensure alerting/notiﬁcation is configured for failed runs or unusually
      high invalid counts.
- [ ] Review the process for updating the domain list (for example, a quarterly
      audit) and schedule the next review.

### 🛡 Compliance & audit

- [ ] Store metadata for each run: source domain, scrape date, and processing
      steps applied (cleaning, filtering, imports).
- [ ] Keep written notes of manual invalid-row triage (see
      `archives/<date>/invalid_review.md`) together with the manifest for audit
      readiness.
- [ ] Confirm that opt-out and unsubscribe procedures are documented and easily
      accessible.
- [ ] Maintain an audit trail covering list generation, cleaning actions, and
      CRM imports.

## Setup validation

Before running the scraper, validate your configuration and environment:

```bash
cd london_agent_scraper
python test_setup.py
```

The validation command checks that `config.json` exists, contains all required keys, and that the
FluentCRM API is reachable with the configured credentials. If you are running from a restricted
network that blocks outbound requests, add `--skip-api-test` to bypass the connectivity check:

```bash
python test_setup.py --skip-api-test
```

To validate a different configuration file use the `--config` flag:

```bash
python test_setup.py --config /path/to/custom_config.json
```

## Running the listing scraper

After the setup test succeeds, run the scraper from the same directory. By default it uses
`config.json` located alongside the script, but you can point to any configuration file with the
`--config` option. The command below is the one used for live runs:

```bash
python scraper_to_fluentcrm.py --csv london_agents_companies_cleaned.csv
```

To inspect the first few contacts without sending them to FluentCRM, pair `--dry-run`
with `--limit`:

```bash
python scraper_to_fluentcrm.py --csv london_agents_companies_cleaned.csv --dry-run --limit 10
```

When a cleaned CSV lives outside of the scraper directory, provide the explicit
path. Relative paths are resolved from the configuration file location so that
per-environment configs can keep their own output directories.

## Scraping direct company websites

If you already have a curated list of company websites and just need to discover contact email
addresses, use `scraper_company_websites.py`. Populate `config_company_websites.json` with the
websites to crawl (or supply an alternate config via `--config`). The script will try to respect
`robots.txt`, follow common "contact" links, and pull the first email address it finds.

### Connectivity requirements

The company website scraper must be able to reach each target domain directly.
Confirm the environment provides unrestricted outbound HTTPS access or add the
domains in `config_company_websites.json` to your organisation's allow list. If
you are running behind a restrictive proxy or VPN, connectivity issues usually
surface as errors such as `Tunnel connection failed: 403 Forbidden`. Resolve the
network block before re-running the live scrape.

Run a dry run to confirm the URLs that will be processed:

```bash
python scraper_company_websites.py --dry-run
```

When you're ready to fetch data, run without `--dry-run`:

```bash
python scraper_company_websites.py
```

Results are written to the CSV specified by `output_csv`. Each row contains the
detected company name (derived from the page title), the original website URL,
and the first email address discovered on the site (or blank if none was found).

### Cleaning scraped results

Use `clean_company_websites_csv.py` to remove placeholder/invalid email addresses
and duplicate website/email pairs before importing the results elsewhere:

```bash
python clean_company_websites_csv.py london_agents_companies.csv \
  --invalid-report london_agents_companies_invalid.csv
```

The script writes a new `<original>_cleaned.csv` file with valid contacts and, if
`--invalid-report` is supplied, a companion file listing rows that were
discarded for further manual review. Use this invalid report to identify domains
that require manual follow-up or alternative data sources.

### Scheduling and record keeping

For regular runs, schedule the scraper via cron (example below) and archive the
generated CSV files (raw, cleaned, and invalid) together with scraper logs so
you can audit historical outreach lists and monitor retention policies:

```bash
30 1 * * * cd /path/to/london_agent_scraper && /usr/bin/python3 scraper_company_websites.py >> /var/log/london_agents_scraper.log 2>&1
```

When analysing the results, note any domains that returned no usable email
addresses so they can be revisited manually.

### Compliance reminder

Ensure all outreach complies with GDPR/PECR requirements. Retain the source
domain, scrape date, and any post-processing steps applied so that contact lists
remain auditable.
