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
    config_path = os.path.expanduser(sys.argv[1]) if len(sys.argv) > 1 else "config.json"
    print(f"[INFO] Using config file: {config_path}")
    cfg = load_config(config_path)
    validate_config(cfg)
    # only test API if not explicitly skipping
    if "--skip-api-test" not in sys.argv:
        test_fluentcrm_api(cfg)
    print("[SUCCESS] Environment validated. You’re ready for the full scraping run.")
    sys.exit(0)

if __name__ == "__main__":
    main()
