# London Agent Scraper

Utilities for scraping estate agent contact details and syncing them with FluentCRM now live in the
`london_agent_scraper/` directory. The scripts expect to be run from this folder (or with explicit
paths) so that the default configuration file can be located correctly.

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
python scraper_to_fluentcrm.py
```

For a dry run that skips all HTTP requests and FluentCRM pushes (useful for smoke tests), include
`--dry-run`:

```bash
python scraper_to_fluentcrm.py --dry-run
```

Scraped contacts are saved to the CSV path defined in your configuration file, and any successfully
pushed contacts will appear in FluentCRM with the specified list and tag assignments.

## Scraping direct company websites

If you already have a curated list of company websites and just need to discover contact email
addresses, use `scraper_company_websites.py`. Populate `config_company_websites.json` with the
websites to crawl (or supply an alternate config via `--config`). The script will try to respect
`robots.txt`, follow common "contact" links, and pull the first email address it finds.

### Connectivity requirements

The company website scraper must be able to reach each target domain directly. If
you are running behind a restrictive proxy or VPN, ensure the domains listed in
`config_company_websites.json` are allow-listed and that outbound HTTPS traffic
is permitted. When the script cannot reach a site you will typically see
messages such as `Tunnel connection failed: 403 Forbidden`. Resolve the
connectivity issue before re-running the live scrape.

Run a dry run to confirm the URLs that will be processed:

```bash
python scraper_company_websites.py --dry-run
```

When you're ready to fetch data, run without `--dry-run`:

```bash
python scraper_company_websites.py
```

Results are written to the CSV specified by `output_csv`. Each row contains the detected company
name (derived from the page title), the original website URL, and the first email address discovered
on the site (or blank if none was found).

### Cleaning scraped results

Use `clean_company_websites_csv.py` to remove placeholder/invalid email addresses
and duplicate website/email pairs before importing the results elsewhere:

```bash
python clean_company_websites_csv.py london_agents_companies.csv \
  --invalid-report london_agents_companies_invalid.csv
```

The script writes a new `<original>_cleaned.csv` file with valid contacts and, if
`--invalid-report` is supplied, a companion file listing rows that were
discarded for further manual review.

### Scheduling and record keeping

For regular runs, schedule the scraper via cron (example below) and archive the
generated CSV files so you can audit historical outreach lists:

```bash
30 1 * * * cd /path/to/london_agent_scraper && /usr/bin/python3 scraper_company_websites.py >> /var/log/london_agents_scraper.log 2>&1
```

When analysing the results, note any domains that returned no usable email
addresses so they can be revisited manually.

### Compliance reminder

Ensure all outreach complies with GDPR/PECR requirements. Retain the source
domain, scrape date, and any post-processing steps applied so that contact lists
remain auditable.
