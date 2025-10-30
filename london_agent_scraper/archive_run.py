#!/usr/bin/env python3
"""Archive scraper outputs with metadata for auditability."""

from __future__ import annotations

import argparse
import csv
import datetime as dt
import hashlib
import json
import shutil
from pathlib import Path
from typing import Optional


SCRIPT_DIR = Path(__file__).parent

DEFAULT_FILES = {
    "raw": SCRIPT_DIR / "london_agents_companies.csv",
    "cleaned": SCRIPT_DIR / "london_agents_companies_cleaned.csv",
    "invalid": SCRIPT_DIR / "london_agents_companies_invalid.csv",
}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Archive the latest scraper run into an archives/<date>/ directory."
    )
    parser.add_argument(
        "--run-date",
        default=dt.date.today().isoformat(),
        help="Run date to use for the archive folder (default: today in ISO format).",
    )
    parser.add_argument(
        "--archive-dir",
        type=Path,
        default=Path(__file__).parent / "archives",
        help="Base directory to store archive folders (default: london_agent_scraper/archives).",
    )
    parser.add_argument(
        "--notes",
        default="",
        help="Optional free-form notes to include in the manifest.",
    )
    parser.add_argument(
        "--overwrite",
        action="store_true",
        help="Allow overwriting an existing archive folder for the same date.",
    )
    return parser.parse_args()


def count_rows(csv_path: Path) -> Optional[int]:
    if not csv_path.exists():
        return None
    with csv_path.open(newline="", encoding="utf-8") as handle:
        reader = csv.reader(handle)
        try:
            next(reader)
        except StopIteration:
            return 0
        return sum(1 for _ in reader)


def file_sha256(path: Path) -> Optional[str]:
    if not path.exists():
        return None
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(8192), b""):
            digest.update(chunk)
    return digest.hexdigest()


def copy_if_exists(source: Path, destination: Path) -> bool:
    if not source.exists():
        return False
    destination.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(source, destination)
    return True


def main() -> None:
    args = parse_args()

    run_date = args.run_date
    archive_base = args.archive_dir
    archive_dir = archive_base / run_date

    if archive_dir.exists() and not args.overwrite:
        raise SystemExit(
            f"Archive folder {archive_dir} already exists. Use --overwrite to replace it."
        )

    archive_dir.mkdir(parents=True, exist_ok=True)

    copied_files: dict[str, Optional[str]] = {}
    row_counts: dict[str, Optional[int]] = {}

    for label, path in DEFAULT_FILES.items():
        dest = archive_dir / path.name
        copied = copy_if_exists(path, dest)
        copied_files[label] = str(dest) if copied else None
        row_counts[label] = count_rows(path) if copied else None

    archived_at = dt.datetime.now(dt.timezone.utc).replace(microsecond=0)

    manifest = {
        "scrape_date": run_date,
        "archived_at_utc": archived_at.isoformat().replace("+00:00", "Z"),
        "config_company_websites_sha256": file_sha256(
            SCRIPT_DIR / "config_company_websites.json"
        ),
        "raw_csv_path": copied_files.get("raw"),
        "cleaned_csv_path": copied_files.get("cleaned"),
        "invalid_csv_path": copied_files.get("invalid"),
        "raw_row_count": row_counts.get("raw"),
        "cleaned_row_count": row_counts.get("cleaned"),
        "invalid_row_count": row_counts.get("invalid"),
        "notes": args.notes,
    }

    manifest_path = archive_dir / "manifest.json"
    manifest_path.write_text(json.dumps(manifest, indent=2), encoding="utf-8")

    print(f"Archived scraper outputs to {archive_dir}")


if __name__ == "__main__":
    main()
