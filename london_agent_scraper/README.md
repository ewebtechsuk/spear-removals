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
