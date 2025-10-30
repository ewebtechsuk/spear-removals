#!/usr/bin/env python3
"""Import cleaned agent data into FluentCRM."""

from __future__ import annotations

import argparse
import csv
import json
import os
import sys
from pathlib import Path
from typing import Iterable, Optional

import requests

REQUIRED_CONFIG_KEYS = (
    "fluentcrm_api_url",
    "fluentcrm_api_user",
    "fluentcrm_api_pass",
    "crm_tag",
    "crm_list",
)

EXPECTED_CSV_COLUMNS = ("company_name", "website", "email")
DEFAULT_CLEANED_NAME = "london_agents_companies_cleaned.csv"


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description=(
            "Import a cleaned companies CSV into FluentCRM. Provide --csv for a live run "
            "or combine it with --dry-run to preview without pushing."
        )
    )
    parser.add_argument(
        "--config",
        default=os.path.join(Path(__file__).parent, "config.json"),
        help=(
            "Path to configuration JSON file (default: london_agent_scraper/config.json). "
            "The CRM credentials and tags are loaded from here."
        ),
    )
    parser.add_argument(
        "--csv",
        type=Path,
        required=False,
        help=(
            "Path to the cleaned CSV to import. When omitted, the script falls back to the "
            "config-relative cleaned CSV location. Required unless --dry-run is provided."
        ),
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Show which contacts would be imported without calling the FluentCRM API.",
    )
    parser.add_argument(
        "--limit",
        type=int,
        default=None,
        help="Optional maximum number of contacts to process (useful for sampling).",
    )

    args = parser.parse_args()

    if not args.dry_run and args.csv is None:
        parser.error("--csv is required for a live import. Add --dry-run to preview without importing.")

    if args.limit is not None and args.limit < 1:
        parser.error("--limit must be a positive integer if supplied.")

    return args


def resolve_password(config: dict) -> None:
    env_key = config.get("fluentcrm_api_pass_env")
    if env_key:
        env_value = os.getenv(env_key)
        if env_value:
            config["fluentcrm_api_pass"] = env_value
    if not config.get("fluentcrm_api_pass"):
        if env_key:
            raise SystemExit(
                "Config missing CRM password. Set environment variable "
                f"{env_key} or populate fluentcrm_api_pass."
            )
        raise SystemExit(
            "Config missing CRM password. Populate fluentcrm_api_pass in the configuration file."
        )


def load_config(config_path: Path) -> dict:
    if not config_path.exists():
        raise SystemExit(f"Config file not found: {config_path}")

    try:
        config = json.loads(config_path.read_text(encoding="utf-8"))
    except json.JSONDecodeError as exc:
        raise SystemExit(f"Error parsing config file: {exc}") from exc

    missing = [key for key in REQUIRED_CONFIG_KEYS if key not in config]
    if missing:
        raise SystemExit(f"Config missing required keys: {', '.join(missing)}")

    resolve_password(config)

    return config


def resolve_csv_path(
    csv_arg: Optional[Path],
    config_path: Path,
    config: dict,
) -> Path:
    """Return the CSV path interpreted relative to the configuration location."""

    if csv_arg is not None:
        candidate = Path(csv_arg)
        if candidate.is_absolute():
            return candidate
        return (Path.cwd() / candidate).resolve()

    default_name = config.get("cleaned_csv_path") or DEFAULT_CLEANED_NAME
    candidate = Path(default_name)
    if candidate.is_absolute():
        return candidate

    base_dir = config_path.parent
    resolved = (base_dir / candidate).resolve()
    return resolved


def load_contacts(csv_path: Path) -> list[dict[str, str]]:
    if not csv_path.exists():
        raise SystemExit(f"CSV file not found: {csv_path}")

    with csv_path.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        fieldnames = reader.fieldnames or []
        missing_columns = [column for column in EXPECTED_CSV_COLUMNS if column not in fieldnames]
        if missing_columns:
            raise SystemExit(
                "CSV is missing required columns: " + ", ".join(sorted(missing_columns))
            )
        contacts = [row for row in reader]

    return contacts


def build_contact_payload(contact: dict[str, str], config: dict) -> dict:
    email = contact.get("email", "").strip()
    company = contact.get("company_name", "").strip()
    website = contact.get("website", "").strip()

    custom_values = {
        "agent_company": company,
        "website": website,
    }

    payload = {
        "email": email,
        "first_name": "",
        "last_name": "",
        "status": "subscribed",
        "tags": [config["crm_tag"]],
        "lists": [config["crm_list"]],
        "custom_values": custom_values,
    }
    return payload


def push_contacts(
    contacts: Iterable[dict[str, str]],
    config: dict,
    limit: Optional[int] = None,
    dry_run: bool = False,
) -> bool:
    api_url = config["fluentcrm_api_url"]
    auth = (config["fluentcrm_api_user"], config["fluentcrm_api_pass"])

    processed = 0
    failures = 0
    for contact in contacts:
        if limit is not None and processed >= limit:
            break

        payload = build_contact_payload(contact, config)
        email = payload["email"]

        if dry_run:
            company = contact.get("company_name", "")
            website = contact.get("website", "")
            print(f"[DRY-RUN] Would import {email} (company={company}, website={website})")
            processed += 1
            continue

        try:
            response = requests.post(
                api_url,
                auth=auth,
                headers={"Content-Type": "application/json"},
                json=payload,
                timeout=20,
            )
            response.raise_for_status()
        except requests.RequestException as exc:
            print(f"[ERROR] Failed to import {email}: {exc}")
            failures += 1
        else:
            print(f"Imported {email} → FluentCRM (status={response.status_code})")
        processed += 1

    print(f"Processed {processed} contacts{' (dry-run)' if dry_run else ''}.")

    return failures == 0


def main() -> int:
    args = parse_args()

    config_path = Path(args.config).expanduser().resolve()
    config = load_config(config_path)

    csv_path: Optional[Path]
    if args.csv is None and args.dry_run:
        csv_path = resolve_csv_path(None, config_path, config)
        if not csv_path.exists():
            print(
                "Dry run requested but no CSV path found. Provide --csv to preview a specific file."
            )
            return 0
    else:
        csv_path = resolve_csv_path(args.csv, config_path, config)

    contacts = load_contacts(csv_path)

    if args.limit is not None:
        print(f"Limiting to first {args.limit} contacts from {csv_path}")
    else:
        print(f"Importing contacts from {csv_path}")

    success = push_contacts(contacts, config, limit=args.limit, dry_run=args.dry_run)

    return 0 if success or args.dry_run else 1


if __name__ == "__main__":
    sys.exit(main())
