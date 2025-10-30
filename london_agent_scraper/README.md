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

## Running the scraper

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

### Post-run verification

1. Inspect the CSV defined by `output_csv` (default: `london_letting_agents_full.csv`) to confirm new
   rows were captured. The file is overwritten on each run, so archive it elsewhere if you need a
   historical record.
2. Log in to your WordPress dashboard and open FluentCRM → Contacts. Filter by the list or tag
   configured in `config.json` to confirm the same contacts were added.

### Handling errors and connectivity issues

* Transient network failures, blocked proxies, or FluentCRM authentication problems are printed to
  the console. Investigate and rerun once connectivity is restored.
* If you are running behind a corporate proxy, ensure the Python process is allowed to reach both the
  source websites and your WordPress domain. A 403 error usually indicates the proxy blocked the
  request.
* The script continues after errors, so review the console output after each run to identify any URLs
  that need manual follow-up.

### Scheduling / automation

To run the scraper automatically (for example every night at 01:30) add a cron entry on the host
machine:

```
30 1 * * * cd /path/to/your/project/london_agent_scraper && /usr/bin/python3 scraper_to_fluentcrm.py >> /var/log/london_agent_scraper.log 2>&1
```

Update the working-directory path and Python interpreter as needed. Ensure the process has
permission to write the CSV file and that log rotation is configured for `/var/log/london_agent_scraper.log`.
