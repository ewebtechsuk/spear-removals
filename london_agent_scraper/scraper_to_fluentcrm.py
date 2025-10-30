import json
import time
import random
import re
import urllib.parse
import urllib.robotparser
import requests
import csv
from bs4 import BeautifulSoup

EMAIL_RE = re.compile(r'[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}', re.I)
CONTACT_HINTS = ("contact", "get-in-touch", "enquiry", "enquiries", "branch", "office")
AGENT_HINTS   = ("estate", "agent", "letting", "agents", "property", "branch")

def load_config():
    with open("config.json", "r", encoding="utf-8") as f:
        return json.load(f)

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

def main():
    cfg        = load_config()
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

if __name__ == "__main__":
    main()
