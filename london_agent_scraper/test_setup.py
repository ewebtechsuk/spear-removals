import argparse
import json
import os
import sys
import requests

REQUIRED_KEYS = [
    "start_urls",
    "fluentcrm_api_url",
    "fluentcrm_api_user",
    "fluentcrm_api_pass",
    "crm_tag",
    "crm_list",
    "output_csv"
]


def parse_args():
    parser = argparse.ArgumentParser(
        description="Validate configuration and connectivity for the London agent scraper."
    )
    parser.add_argument(
        "--config",
        default=os.path.join(os.path.dirname(__file__), "config.json"),
        help="Path to the scraper configuration file (default: london_agent_scraper/config.json)",
    )
    parser.add_argument(
        "--skip-api-test",
        action="store_true",
        help="Skip the FluentCRM connectivity check (useful in restricted environments).",
    )
    return parser.parse_args()

def load_config(path):
    try:
        with open(path, "r", encoding="utf-8") as f:
            return json.load(f)
    except Exception as e:
        print(f"[ERROR] Could not load config file at '{path}': {e}")
        sys.exit(1)

def validate_config(cfg):
    missing = [k for k in REQUIRED_KEYS if k not in cfg or not cfg[k]]
    if missing:
        print(f"[ERROR] Missing required config keys: {missing}")
        sys.exit(2)
    print("[OK] Config file contains all required keys.")

def test_fluentcrm_api(cfg):
    url = cfg["fluentcrm_api_url"].rstrip("/") + "/subscribers"
    print(f"[INFO] Testing FluentCRM API endpoint: {url}")
    try:
        resp = requests.get(url, auth=(cfg["fluentcrm_api_user"], cfg["fluentcrm_api_pass"]), timeout=10)
        if resp.status_code == 200:
            print("[OK] FluentCRM API reachable. Status 200.")
        else:
            print(f"[ERROR] FluentCRM API returned status code {resp.status_code}: {resp.text}")
            sys.exit(3)
    except Exception as e:
        print(f"[ERROR] FluentCRM API request failed: {e}")
        sys.exit(4)

def main():
    args = parse_args()
    config_path = os.path.abspath(os.path.expanduser(args.config))
    print(f"[INFO] Using config file: {config_path}")
    cfg = load_config(config_path)
    validate_config(cfg)
    if args.skip_api_test:
        print("[INFO] Skipping FluentCRM API connectivity test.")
    else:
        test_fluentcrm_api(cfg)
    print("[SUCCESS] Environment validated. You’re ready for the full scraping run.")
    sys.exit(0)

if __name__ == "__main__":
    main()
