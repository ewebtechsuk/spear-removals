import argparse
import csv
import json
import os
import random
import re
import sys
import time
import urllib.parse
import urllib.robotparser
from typing import Dict, List, Optional

import requests
from bs4 import BeautifulSoup

from clean_company_websites_csv import assess_mailbox, email_is_valid

EMAIL_RE = re.compile(r"\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b", re.I)
ZERO_WIDTH_RE = re.compile(r"[\u200b-\u200d\ufeff]")
CONTACT_HINTS = ("contact", "get-in-touch", "enquiries", "enquiry", "office", "team")
REQUIRED_CONFIG_KEYS = (
    "company_websites",
    "user_agent",
    "request_delay_seconds",
    "timeout_seconds",
    "output_csv",
)

SCRIPT_DIR = os.path.dirname(__file__)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Scrape company websites for email addresses"
    )
    parser.add_argument(
        "--config",
        default=os.path.join(SCRIPT_DIR, "config_company_websites.json"),
        help="Path to config JSON (default: config_company_websites.json)",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Run without actual requests; just print what would be done",
    )
    return parser.parse_args()


def load_config(path: str) -> Dict[str, object]:
    if not os.path.isfile(path):
        print(f"[ERROR] Config file not found: {path}")
        sys.exit(1)
    try:
        with open(path, "r", encoding="utf-8") as f:
            cfg = json.load(f)
    except json.JSONDecodeError as exc:
        print(f"[ERROR] Error parsing config JSON: {exc}")
        sys.exit(1)
    missing = [key for key in REQUIRED_CONFIG_KEYS if key not in cfg]
    if missing:
        print(f"[ERROR] Config missing required keys: {missing}")
        sys.exit(1)
    return cfg


def build_session(user_agent: str) -> requests.Session:
    session = requests.Session()
    session.headers.update({"User-Agent": user_agent})
    return session


def can_fetch(rp_cache: Dict[str, Optional[urllib.robotparser.RobotFileParser]], ua: str, url: str) -> bool:
    try:
        parsed = urllib.parse.urlparse(url)
        base = f"{parsed.scheme}://{parsed.netloc}"
        if base not in rp_cache:
            robot_parser = urllib.robotparser.RobotFileParser()
            robot_parser.set_url(urllib.parse.urljoin(base, "/robots.txt"))
            try:
                robot_parser.read()
            except Exception:
                robot_parser = None
            rp_cache[base] = robot_parser
        robot_parser = rp_cache[base]
        if robot_parser is None:
            return True
        return robot_parser.can_fetch(ua, url)
    except Exception:
        return True


def sleep_delay(delay: float) -> None:
    time.sleep(delay + random.uniform(0, delay * 0.35))


def _pick_best_email(candidates: set[str]) -> Optional[str]:
    if not candidates:
        return None

    best_allowed: Optional[tuple[int, str]] = None
    best_valid: Optional[tuple[int, str]] = None

    for candidate in sorted({c.lower() for c in candidates}):
        if not email_is_valid(candidate):
            continue

        allowed, score = assess_mailbox(candidate)
        current = (score, candidate)

        if allowed and (best_allowed is None or current > best_allowed):
            best_allowed = current

        if best_valid is None or current > best_valid:
            best_valid = current

    if best_allowed is not None:
        return best_allowed[1]
    if best_valid is not None:
        return best_valid[1]
    return None


def extract_email_from_url(session: requests.Session, url: str, timeout: float) -> Optional[str]:
    try:
        response = session.get(url, timeout=timeout)
        response.raise_for_status()
    except Exception as exc:
        print(f"[WARN] Could not fetch {url}: {exc}")
        return None
    html = response.text
    emails = set(re.findall(EMAIL_RE, html))
    return _pick_best_email(emails)


def find_contact_page(
    session: requests.Session,
    base_url: str,
    timeout: float,
    rp_cache: Dict[str, Optional[urllib.robotparser.RobotFileParser]],
    ua: str,
) -> Optional[str]:
    """Visit base_url, look for links pointing to contact page hints, then return url."""

    try:
        response = session.get(base_url, timeout=timeout)
        response.raise_for_status()
    except Exception as exc:
        print(f"[WARN] Could not fetch home page {base_url}: {exc}")
        return None
    soup = BeautifulSoup(response.text, "html.parser")
    for anchor in soup.find_all("a", href=True):
        href = anchor["href"]
        full_url = href if href.startswith("http") else urllib.parse.urljoin(base_url, href)
        lower_url = full_url.lower()
        if any(hint in lower_url for hint in CONTACT_HINTS):
            if can_fetch(rp_cache, ua, full_url):
                return full_url
    return None


def _clean_text(value: str) -> str:
    return ZERO_WIDTH_RE.sub("", value).strip()


def get_company_name_from_page(html: str) -> str:
    soup = BeautifulSoup(html, "html.parser")

    og_site = soup.find("meta", attrs={"property": "og:site_name"})
    if og_site and og_site.get("content"):
        content = _clean_text(og_site["content"])
        if content:
            return content

    h1 = soup.find("h1")
    if h1:
        heading = _clean_text(h1.get_text(" ", strip=True))
        if heading:
            return heading

    if soup.title and soup.title.string:
        title = _clean_text(soup.title.string)
        for separator in ("|", "-", "–", "•"):
            if separator in title:
                candidate = _clean_text(title.split(separator)[0])
                if candidate:
                    return candidate
        return title

    return ""


def write_results(path: str, rows: List[Dict[str, str]]) -> None:
    keys = ["company_name", "website", "email"]
    with open(path, "w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=keys)
        writer.writeheader()
        for row in rows:
            writer.writerow({key: row.get(key, "") for key in keys})


def main() -> None:
    args = parse_args()
    config_path = os.path.abspath(os.path.expanduser(args.config))
    print(f"[INFO] Using config file: {config_path}")

    config = load_config(config_path)

    if args.dry_run:
        print("[DRY-RUN] No network requests will be made.")
        for url in config["company_websites"]:
            print(f"[DRY-RUN] Would process: {url}")
        return

    session = build_session(config["user_agent"])
    robots_cache: Dict[str, Optional[urllib.robotparser.RobotFileParser]] = {}
    results: List[Dict[str, str]] = []

    for website in config["company_websites"]:
        print(f"[INFO] Processing website: {website}")
        if not can_fetch(robots_cache, config["user_agent"], website):
            print(f"[WARN] Fetch not allowed by robots.txt: {website}")
            continue

        contact_url = find_contact_page(
            session,
            website,
            config["timeout_seconds"],
            robots_cache,
            config["user_agent"],
        )
        target_url = contact_url or website
        email = extract_email_from_url(session, target_url, config["timeout_seconds"])
        try:
            home_response = session.get(website, timeout=config["timeout_seconds"])
            home_response.raise_for_status()
            company_name = get_company_name_from_page(home_response.text)
        except Exception:
            company_name = website

        results.append(
            {
                "company_name": company_name,
                "website": website,
                "email": email or "",
            }
        )

        sleep_delay(config["request_delay_seconds"])

    write_results(config["output_csv"], results)

    print(f"[INFO] Completed. {len(results)} records written to {config['output_csv']}")


if __name__ == "__main__":
    main()
