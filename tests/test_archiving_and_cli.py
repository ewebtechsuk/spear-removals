import csv
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
import clean_company_websites_csv  # noqa: E402
import scraper_to_fluentcrm  # noqa: E402


def test_archive_manifest_schema(tmp_path, monkeypatch):
    raw = tmp_path / "london_agents_companies.csv"
    cleaned = tmp_path / "london_agents_companies_cleaned.csv"
    invalid = tmp_path / "london_agents_companies_invalid.csv"

    raw.write_text("company_name,website,email\nAcme,https://acme.test,info@acme.test\n", encoding="utf-8")
    cleaned.write_text("company_name,website,email\nAcme,https://acme.test,info@acme.test\n", encoding="utf-8")
    invalid.write_text(
        "company_name,website,email,invalid_reason\n"
        "Bad Co,https://bad.test,bad@example.com,missing-email\n",
        encoding="utf-8",
    )

    monkeypatch.setattr(
        archive_run,
        "DEFAULT_FILES",
        {
            "raw": raw,
            "cleaned": cleaned,
            "invalid": invalid,
        },
    )

    history_file = tmp_path / "history.csv"
    args = Namespace(
        run_date="2025-10-30",
        archive_dir=tmp_path / "archives",
        notes="test run",
        history_file=history_file,
        overwrite=True,
        prune_older_than_months=0,
    )

    archive_dir = archive_run.archive_run(args)
    manifest_path = archive_dir / "manifest.json"
    manifest_text = manifest_path.read_text(encoding="utf-8")
    manifest = json.loads(manifest_text)

    assert manifest["raw_row_count"] == 1
    assert manifest["cleaned_row_count"] == 1
    assert manifest["invalid_row_count"] == 1
    assert manifest["missing_email_count"] == 1
    assert manifest["invalid_reason_counts"] == {"missing-email": 1}
    assert manifest["invalid_ratio_excluding_missing"] == 0
    assert manifest["run_history_csv"] == str(history_file)
    assert "archived_at_utc" in manifest
    assert "archived_at_local" in manifest
    assert manifest_text.count("\"archived_at_utc\"") == 1
    assert history_file.exists()


def test_scraper_to_fluentcrm_requires_csv():
    script = SCRIPT_DIR / "scraper_to_fluentcrm.py"
    result = subprocess.run(
        [sys.executable, str(script)],
        capture_output=True,
        text=True,
    )
    assert result.returncode == 2
    assert "--csv is required" in result.stderr


def test_push_contacts_dry_run(monkeypatch, capsys):
    contacts = [
        {
            "company_name": "Acme",
            "website": "https://acme.test",
            "email": "info@acme.test",
        }
    ]
    called = False

    def fake_post(*args, **kwargs):
        nonlocal called
        called = True
        raise AssertionError("requests.post should not be called during dry-run")

    monkeypatch.setattr(scraper_to_fluentcrm.requests, "post", fake_post)

    config = {
        "fluentcrm_api_url": "https://crm.test/api",
        "fluentcrm_api_user": "user",
        "fluentcrm_api_pass": "pass",
        "crm_tag": "tag",
        "crm_list": "list",
    }

    success = scraper_to_fluentcrm.push_contacts(
        contacts,
        config,
        dry_run=True,
    )

    captured = capsys.readouterr()
    assert "[DRY-RUN] Would import" in captured.out
    assert success
    assert called is False


def test_load_config_reads_password_from_env(tmp_path, monkeypatch):
    config_path = tmp_path / "config.json"
    config_path.write_text(
        json.dumps(
            {
                "fluentcrm_api_url": "https://crm.test/api",
                "fluentcrm_api_user": "user",
                "fluentcrm_api_pass": "",
                "fluentcrm_api_pass_env": "TEST_FLUENT_PASS",
                "crm_tag": "tag",
                "crm_list": "list",
            }
        ),
        encoding="utf-8",
    )

    monkeypatch.setenv("TEST_FLUENT_PASS", "secret")

    config = scraper_to_fluentcrm.load_config(config_path)
    assert config["fluentcrm_api_pass"] == "secret"


@pytest.mark.parametrize("invalid_rows", [5])
def test_cleaner_flags_high_invalid_ratio(tmp_path, invalid_rows):
    input_csv = tmp_path / "input.csv"
    rows = ["company_name,website,email"]
    for i in range(invalid_rows):
        rows.append(f"Invalid Co {i},https://invalid{i}.test,not-an-email")
    rows.append("Valid Co,https://valid.test,valid@agentmail.test")
    input_csv.write_text("\n".join(rows) + "\n", encoding="utf-8")

    script = SCRIPT_DIR / "clean_company_websites_csv.py"
    invalid_report = tmp_path / "invalid.csv"
    result = subprocess.run(
        [
            sys.executable,
            str(script),
            str(input_csv),
            "--invalid-report",
            str(invalid_report),
        ],
        capture_output=True,
        text=True,
    )
    assert result.returncode == 1
    assert "High invalid rate" in result.stdout
    with invalid_report.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        reasons = {row.get("invalid_reason") for row in reader}
    assert "invalid-format" in reasons


def test_cleaner_missing_email_excluded_from_ratio(tmp_path):
    input_csv = tmp_path / "input.csv"
    rows = ["company_name,website,email"]
    for i in range(4):
        rows.append(f"Missing {i},https://missing{i}.test,")
    rows.append("Valid Co,https://valid.test,valid@agentmail.test")
    input_csv.write_text("\n".join(rows) + "\n", encoding="utf-8")

    script = SCRIPT_DIR / "clean_company_websites_csv.py"
    invalid_report = tmp_path / "invalid.csv"
    cleaned_csv = tmp_path / "cleaned.csv"
    result = subprocess.run(
        [
            sys.executable,
            str(script),
            str(input_csv),
            "--output-csv",
            str(cleaned_csv),
            "--invalid-report",
            str(invalid_report),
        ],
        capture_output=True,
        text=True,
    )
    assert result.returncode == 0
    assert "missing-email=4" in result.stdout

    with invalid_report.open(newline="", encoding="utf-8") as handle:
        reader = csv.DictReader(handle)
        reasons = [row.get("invalid_reason") for row in reader]
    assert reasons.count("missing-email") == 4
