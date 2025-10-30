#!/usr/bin/env python3
"""Archive scraper outputs with metadata for auditability."""

from __future__ import annotations

import argparse
import calendar
import csv
import datetime as dt
import hashlib
import json
import shutil
from collections import Counter
from pathlib import Path
from typing import Dict, Optional


SCRIPT_DIR = Path(__file__).parent

DEFAULT_FILES = {
    "raw": SCRIPT_DIR / "london_agents_companies.csv",
    "cleaned": SCRIPT_DIR / "london_agents_companies_cleaned.csv",
    "invalid": SCRIPT_DIR / "london_agents_companies_invalid.csv",
}

INVALID_REASON_FIELD = "invalid_reason"
MISSING_EMAIL_REASON = "missing-email"
RUN_HISTORY_NAME = "run_history.csv"
RUN_HISTORY_HEADERS = [
    "run_date",
    "raw_rows",
    "cleaned_rows",
    "invalid_rows",
    "missing_email_rows",
    "invalid_ratio_ex_missing",
    "notes",
]


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
        "--history-file",
        type=Path,
        default=None,
        help=(
            "Optional path for the cumulative run history CSV (default: <archive-dir>/"
            f"{RUN_HISTORY_NAME})."
        ),
    )
    parser.add_argument(
        "--overwrite",
        action="store_true",
        help="Allow overwriting an existing archive folder for the same date.",
    )
    parser.add_argument(
        "--prune-older-than-months",
        type=int,
        default=12,
        help=(
            "Prune archive folders older than the specified number of months (default: 12). "
            "Use 0 to disable pruning."
        ),
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


def load_invalid_breakdown(path: Path) -> Counter[str]:
    breakdown: Counter[str] = Counter()
    if not path.exists():
        return breakdown

    with path.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        fieldnames = reader.fieldnames or []
        if INVALID_REASON_FIELD not in fieldnames:
            return breakdown
        for row in reader:
            reason = (row.get(INVALID_REASON_FIELD) or "").strip()
            if reason:
                breakdown[reason] += 1

    return breakdown


def calculate_invalid_ratio(
    row_counts: Dict[str, Optional[int]],
    breakdown: Counter[str],
) -> tuple[float, int]:
    raw_rows = row_counts.get("raw") or 0
    invalid_rows = row_counts.get("invalid") or 0
    missing_count = breakdown.get(MISSING_EMAIL_REASON, 0)

    considered_rows = raw_rows - missing_count
    invalid_with_email = max(invalid_rows - missing_count, 0)
    ratio = (invalid_with_email / considered_rows) if considered_rows else 0.0
    return ratio, missing_count


def update_run_history(
    archive_base: Path,
    history_file: Optional[Path],
    run_date: str,
    row_counts: Dict[str, Optional[int]],
    missing_email: int,
    invalid_ratio: float,
    notes: str,
) -> Path:
    history_path = history_file or (archive_base / RUN_HISTORY_NAME)
    history_path.parent.mkdir(parents=True, exist_ok=True)
    file_exists = history_path.exists()

    with history_path.open("a", newline="", encoding="utf-8") as handle:
        writer = csv.writer(handle)
        if not file_exists:
            writer.writerow(RUN_HISTORY_HEADERS)
        writer.writerow(
            [
                run_date,
                row_counts.get("raw") or 0,
                row_counts.get("cleaned") or 0,
                row_counts.get("invalid") or 0,
                missing_email,
                f"{invalid_ratio:.4f}",
                notes,
            ]
        )

    return history_path


def subtract_months(reference: dt.date, months: int) -> dt.date:
    year = reference.year
    month = reference.month - months
    while month <= 0:
        month += 12
        year -= 1
    day = min(reference.day, calendar.monthrange(year, month)[1])
    return dt.date(year, month, day)


def prune_old_archives(archive_base: Path, months: int) -> list[Path]:
    pruned: list[Path] = []
    if months <= 0:
        return pruned

    today = dt.date.today()
    cutoff = subtract_months(today, months)

    for child in archive_base.iterdir():
        if not child.is_dir():
            continue
        try:
            run_date = dt.date.fromisoformat(child.name)
        except ValueError:
            continue
        if run_date < cutoff:
            shutil.rmtree(child)
            pruned.append(child)

    return pruned


def build_manifest(
    run_date: str,
    copied_files: Dict[str, Optional[str]],
    row_counts: Dict[str, Optional[int]],
    notes: str,
    invalid_breakdown: Counter[str],
    invalid_ratio: float,
    missing_email: int,
    history_path: Path,
) -> dict:
    archived_at_utc = dt.datetime.now(dt.timezone.utc).replace(microsecond=0)
    archived_at_local = archived_at_utc.astimezone().replace(microsecond=0)

    manifest = {
        "scrape_date": run_date,
        "archived_at_utc": archived_at_utc.isoformat().replace("+00:00", "Z"),
        "archived_at_local": archived_at_local.isoformat(),
        "config_company_websites_sha256": file_sha256(
            SCRIPT_DIR / "config_company_websites.json"
        ),
        "raw_csv_path": copied_files.get("raw"),
        "cleaned_csv_path": copied_files.get("cleaned"),
        "invalid_csv_path": copied_files.get("invalid"),
        "raw_row_count": row_counts.get("raw"),
        "cleaned_row_count": row_counts.get("cleaned"),
        "invalid_row_count": row_counts.get("invalid"),
        "missing_email_count": missing_email,
        "invalid_ratio_excluding_missing": round(invalid_ratio, 4),
        "invalid_reason_counts": dict(invalid_breakdown),
        "run_history_csv": str(history_path),
        "notes": notes,
    }
    return manifest


def archive_run(args: argparse.Namespace) -> Path:
    run_date = args.run_date
    archive_base = args.archive_dir
    archive_dir = archive_base / run_date

    if archive_dir.exists() and not args.overwrite:
        raise SystemExit(
            f"Archive folder {archive_dir} already exists. Use --overwrite to replace it."
        )

    archive_dir.mkdir(parents=True, exist_ok=True)

    copied_files: Dict[str, Optional[str]] = {}
    row_counts: Dict[str, Optional[int]] = {}

    for label, path in DEFAULT_FILES.items():
        dest = archive_dir / path.name
        copied = copy_if_exists(path, dest)
        copied_files[label] = str(dest) if copied else None
        row_counts[label] = count_rows(path) if copied else None

    invalid_breakdown = load_invalid_breakdown(DEFAULT_FILES["invalid"])
    invalid_ratio, missing_email = calculate_invalid_ratio(row_counts, invalid_breakdown)

    history_path = update_run_history(
        archive_base,
        args.history_file,
        run_date,
        row_counts,
        missing_email,
        invalid_ratio,
        args.notes,
    )

    manifest = build_manifest(
        run_date,
        copied_files,
        row_counts,
        args.notes,
        invalid_breakdown,
        invalid_ratio,
        missing_email,
        history_path,
    )

    manifest_path = archive_dir / "manifest.json"
    manifest_path.write_text(json.dumps(manifest, indent=2), encoding="utf-8")

    pruned = prune_old_archives(archive_base, args.prune_older_than_months)
    if pruned:
        print(
            "Pruned archives: "
            + ", ".join(str(path) for path in sorted(pruned))
        )

    return archive_dir


def main() -> None:
    args = parse_args()
    archive_dir = archive_run(args)
    print(f"Archived scraper outputs to {archive_dir}")


if __name__ == "__main__":
    main()
