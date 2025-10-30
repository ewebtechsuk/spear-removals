import json
import subprocess
import sys
from argparse import Namespace
from pathlib import Path

import pytest

REPO_ROOT = Path(__file__).resolve().parents[1]
SCRIPT_DIR = REPO_ROOT / "london_agent_scraper"

if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

import archive_run  # noqa: E402


def test_archive_manifest_schema(tmp_path, monkeypatch):
    raw = tmp_path / "london_agents_companies.csv"
    cleaned = tmp_path / "london_agents_companies_cleaned.csv"
    invalid = tmp_path / "london_agents_companies_invalid.csv"

    raw.write_text("company_name,website,email\nAcme,https://acme.test,info@acme.test\n", encoding="utf-8")
    cleaned.write_text("company_name,website,email\nAcme,https://acme.test,info@acme.test\n", encoding="utf-8")
    invalid.write_text("company_name,website,email\nBad Co,https://bad.test,bad@example.com\n", encoding="utf-8")

    monkeypatch.setattr(
        archive_run,
        "DEFAULT_FILES",
        {
            "raw": raw,
            "cleaned": cleaned,
            "invalid": invalid,
        },
    )

    args = Namespace(
        run_date="2025-10-30",
        archive_dir=tmp_path / "archives",
        notes="test run",
        overwrite=True,
    )

    archive_dir = archive_run.archive_run(args)
    manifest_path = archive_dir / "manifest.json"
    manifest_text = manifest_path.read_text(encoding="utf-8")
    manifest = json.loads(manifest_text)

    assert manifest["raw_row_count"] == 1
    assert manifest["cleaned_row_count"] == 1
    assert manifest["invalid_row_count"] == 1
    assert "archived_at_utc" in manifest
    assert "archived_at_local" in manifest
    assert manifest_text.count("\"archived_at_utc\"") == 1


def test_scraper_to_fluentcrm_requires_csv():
    script = SCRIPT_DIR / "scraper_to_fluentcrm.py"
    result = subprocess.run(
        [sys.executable, str(script)],
        capture_output=True,
        text=True,
    )
    assert result.returncode == 2
    assert "--csv is required" in result.stderr


@pytest.mark.parametrize("invalid_rows", [5])
def test_cleaner_flags_high_invalid_ratio(tmp_path, invalid_rows):
    input_csv = tmp_path / "input.csv"
    rows = ["company_name,website,email"]
    for i in range(invalid_rows):
        rows.append(f"Invalid Co {i},https://invalid{i}.test,not-an-email")
    rows.append("Valid Co,https://valid.test,valid@example.com")
    input_csv.write_text("\n".join(rows) + "\n", encoding="utf-8")

    script = SCRIPT_DIR / "clean_company_websites_csv.py"
    result = subprocess.run(
        [
            sys.executable,
            str(script),
            str(input_csv),
            "--invalid-report",
            str(tmp_path / "invalid.csv"),
        ],
        capture_output=True,
        text=True,
    )
    assert result.returncode == 1
    assert "High invalid rate" in result.stdout
