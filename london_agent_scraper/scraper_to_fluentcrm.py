import argparse
import json
import time
import random
import re
from pathlib import Path
import urllib.parse
import urllib.robotparser
import requests
import csv
from bs4 import BeautifulSoup

DEFAULT_CONFIG_PATH = Path(__file__).resolve().parent / "config.json"
EMAIL_RE = re.compile(r'[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}', re.I)
CONTACT_HINTS = ("contact", "get-in-touch", "enquiry", "enquiries", "branch", "office")
AGENT_HINTS   = ("estate", "agent", "letting", "agents", "property", "branch")

def parse_args(argv=None):
    parser = argparse.ArgumentParser(description="Scrape London letting/estate agent contact information.")
    parser.add_argument(
        "--config",
        default=str(DEFAULT_CONFIG_PATH),
        help=f"Path to the scraper configuration file (default: {DEFAULT_CONFIG_PATH.name})",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Load and validate configuration without performing any network requests.",
    )
    return parser.parse_args(argv)


def load_config(config_path):
    with open(config_path, "r", encoding="utf-8") as f:
        return json.load(f)


def validate_config(cfg):
    required_keys = (
        "start_urls",
        "user_agent",
        "request_delay_seconds",
        "timeout_seconds",
        "max_pages",
        "max_emails",
        "stop_when_emails_reach",
        "output_csv",
        "fluentcrm_api_url",
        "fluentcrm_api_user",
        "fluentcrm_api_pass",
        "crm_tag",
        "crm_list",
    )
    missing = [k for k in required_keys if k not in cfg]
    if missing:
        raise ValueError(f"Missing configuration keys: {', '.join(missing)}")

def build_session(user_agent):
    s = requests.Session()
    s.headers.update({"User-Agent": user_agent})
    return s

def can_fetch(rp_cache, ua, url):
    try:
        parsed = urllib.parse.urlparse(url)
        base   = f"{parsed.scheme}://{parsed.netloc}"
        if base not in rp_cache:
            rp = urllib.robotparser.RobotFileParser()
            rp.set_url(urllib.parse.urljoin(base, "/robots.txt"))
            try:
                rp.read()
            except:
                rp = None
            rp_cache[base] = rp
        rp = rp_cache[base]
        if rp is None:
            return True
        return rp.can_fetch(ua, url)
    except Exception:
        return True

def sleep_delay(delay):
    time.sleep(delay + random.uniform(0, delay*0.35))

def fetch_html(session, url, timeout):
    r = session.get(url, timeout=timeout)
    r.raise_for_status()
    return r.text

def extract_emails(html):
    emails = set()
    soup = BeautifulSoup(html, "html.parser")
    for a in soup.select('a[href^="mailto:"]'):
        m = EMAIL_RE.search(a.get("href", ""))
        if m:
            emails.add(m.group(0).lower())
    text = soup.get_text(" ", strip=True)
    for m in EMAIL_RE.findall(text):
        emails.add(m.lower())
    return emails

def find_links(html, base_url, hints):
    links = set()
    soup  = BeautifulSoup(html, "html.parser")
    for a in soup.find_all("a", href=True):
        href = a["href"]
        if href.startswith("#") or href.lower().startswith("javascript:"):
            continue
        full = href if href.startswith("http") else urllib.parse.urljoin(base_url, href)
        low  = full.lower()
        if any(h in low for h in hints):
            links.add(full)
    return links

def sanitize_domain(url):
    parsed = urllib.parse.urlparse(url)
    return parsed.netloc.lower()

def push_to_fluentcrm(api_url, auth, contact_data):
    headers  = {"Content-Type": "application/json"}
    response = requests.post(api_url, auth=auth, headers=headers, json=contact_data, timeout=20)
    response.raise_for_status()
    return response.json()

def main(argv=None):
    args = parse_args(argv)
    cfg  = load_config(args.config)
    validate_config(cfg)

    if args.dry_run:
        print("Dry run successful — configuration loaded and validated.")
        print(f"Start URLs: {len(cfg['start_urls'])}")
        print(f"Output CSV: {cfg['output_csv']}")
        print("Use without --dry-run to begin scraping.")
        return 0

    session    = build_session(cfg["user_agent"])
    rp_cache   = {}
    visited    = set()
    queue      = list(cfg["start_urls"])

    found      = {}  # email → data dict
    pages_seen = 0

    crm_api_url = cfg["fluentcrm_api_url"]
    crm_auth    = (cfg["fluentcrm_api_user"], cfg["fluentcrm_api_pass"])

    while queue and pages_seen < cfg["max_pages"] and len(found) < cfg["max_emails"]:
        url = queue.pop(0)
        if url in visited:
            continue
        visited.add(url)

        if not can_fetch(rp_cache, cfg["user_agent"], url):
            continue

        try:
            html = fetch_html(session, url, cfg["timeout_seconds"])
        except Exception as e:
            print(f"Error fetching {url}: {e}")
            sleep_delay(cfg["request_delay_seconds"])
            continue

        pages_seen += 1

        emails  = extract_emails(html)
        domain  = sanitize_domain(url)
        soup    = BeautifulSoup(html, "html.parser")
        title   = soup.title.string.strip() if soup.title else ""
        agent_name = title.split("|")[0].strip() if "|" in title else title.strip()

        for em in emails:
            if em not in found:
                found[em] = {
                    "agent_name": agent_name,
                    "website": url,
                    "domain": domain,
                    "source": url
                }
                contact_payload = {
                    "email": em,
                    "first_name": "",
                    "last_name": "",
                    "status": "subscribed",
                    "tags": [ cfg["crm_tag"] ],
                    "lists": [ cfg["crm_list"] ],
                    "custom_values": {
                        "agent_name": agent_name,
                        "website": url,
                        "domain": domain,
                        "source_url": url
                    }
                }
                try:
                    resp = push_to_fluentcrm(crm_api_url, crm_auth, contact_payload)
                    print(f"Pushed {em} → FluentCRM: {resp.get('message','')}")
                except Exception as e:
                    print(f"Error pushing {em}: {e}")

        contact_links = find_links(html, url, CONTACT_HINTS)
        for cl in contact_links:
            if cl not in visited:
                queue.append(cl)
        agent_links = find_links(html, url, AGENT_HINTS)
        for al in agent_links:
            if al not in visited:
                queue.append(al)

        if len(found) >= cfg["stop_when_emails_reach"]:
            break

        sleep_delay(cfg["request_delay_seconds"])

    # Write CSV backup
    with open(cfg["output_csv"], "w", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)
        writer.writerow(["email","agent_name","website","domain","source_url"])
        for em, data in sorted(found.items()):
            writer.writerow([em, data["agent_name"], data["website"], data["domain"], data["source"]])

    print(f"Scraping complete: {len(found)} unique emails. CSV saved: {cfg['output_csv']}")
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
