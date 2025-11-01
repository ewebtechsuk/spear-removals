# Hostinger Deployment & Maintenance Guide

This guide mirrors the operational checklist for managing the Spear Removals
production site that lives on Hostinger. Each section matches the numbered
procedure shared by the team so you can run the same commands locally or from
GitHub Actions.

> **Prerequisites**
>
> * SSH access to the Hostinger account (`u753768407`).
> * The `wp_site_deploy.sh` script stored in
>   `/home/u753768407/domains/spearremovals.co.uk/public_html/scripts/`.
> * WP-CLI installed on the target host for maintenance-mode and cache steps.

## 1. SSH into Hostinger & prepare the script

```bash
ssh u753768407@YOUR_HOST
cd /home/u753768407/domains/spearremovals.co.uk/public_html
git pull origin main
chmod +x scripts/wp_site_deploy.sh
```

## 2. Quick dry run (skip backups & maintenance)

```bash
SITE_USER="u753768407" \
SITE_DIR="/home/u753768407/domains/spearremovals.co.uk/public_html" \
BRANCH="main" \
REPO_URL="https://github.com/ewebtechsuk/spear-removals.git" \
./scripts/wp_site_deploy.sh --skip-backups --skip-maintenance --verbose
```

## 3. Full deploy (with backups, maintenance, cache flush, permissions)

```bash
SITE_USER="u753768407" \
SITE_DIR="/home/u753768407/domains/spearremovals.co.uk/public_html" \
BRANCH="main" \
REPO_URL="https://github.com/ewebtechsuk/spear-removals.git" \
./scripts/wp_site_deploy.sh --verbose
```

## 4. Optional: make the script globally accessible

```bash
sudo ln -sf /home/u753768407/domains/spearremovals.co.uk/public_html/scripts/wp_site_deploy.sh /usr/local/bin/wp_site_deploy
wp_site_deploy --help
```

## 5. Rollback using the latest backups

```bash
ls -lt ~/backups

DBTS="YYYYMMDD-HHMMSS"
FILESTS="YYYYMMDD-HHMMSS"

wp --path="/home/u753768407/domains/spearremovals.co.uk/public_html" maintenance-mode activate || true
wp --path="/home/u753768407/domains/spearremovals.co.uk/public_html" db import "$HOME/backups/db-$DBTS.sql"
wp --path="/home/u753768407/domains/spearremovals.co.uk/public_html" maintenance-mode deactivate || true

tar -xzf "$HOME/backups/files-$FILESTS.tgz" -C \
"/home/u753768407/domains/spearremovals.co.uk/public_html"
find "/home/u753768407/domains/spearremovals.co.uk/public_html" -type d -exec chmod 755 {} \;
find "/home/u753768407/domains/spearremovals.co.uk/public_html" -type f -exec chmod 644 {} \;
```

> Replace the placeholder timestamps with the latest values shown by `ls -lt`.

## 6. GitHub Actions auto-deploy on push to `main`

Save the workflow below as `.github/workflows/deploy.yml` in the repository and
add the required secrets (`HOST`, `USERNAME`, `SSH_KEY`) via the GitHub project
settings.

```yaml
name: Deploy to Hostinger
on:
  push:
    branches: [ "main" ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: SSH deploy
        uses: appleboy/ssh-action@v1.2.0
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}          # e.g. u753768407
          key: ${{ secrets.SSH_KEY }}                # your private key
          script: |
            SITE_USER="${{ secrets.USERNAME }}"
            SITE_DIR="/home/${{ secrets.USERNAME }}/domains/spearremovals.co.uk/public_html"
            cd "$SITE_DIR"
            chmod +x scripts/wp_site_deploy.sh
            SITE_USER="${{ secrets.USERNAME }}" \
            SITE_DIR="$SITE_DIR" \
            BRANCH="main" \
            REPO_URL="https://github.com/ewebtechsuk/spear-removals.git" \
            ./scripts/wp_site_deploy.sh --verbose
```

## 7. Nightly backup + sync via cron

```bash
crontab -e

15 3 * * * SITE_USER="u753768407" SITE_DIR="/home/u753768407/domains/spearremovals.co.uk/public_html" BRANCH="main" REPO_URL="https://github.com/ewebtechsuk/spear-removals.git" /home/u753768407/domains/spearremovals.co.uk/public_html/scripts/wp_site_deploy.sh >> /home/u753768407/deploy.log 2>&1
```

## 8. WP-CLI & cache checks

```bash
which wp || echo "WP-CLI not found in PATH"
wp --path="/home/u753768407/domains/spearremovals.co.uk/public_html" core version
wp --path="/home/u753768407/domains/spearremovals.co.uk/public_html" litespeed-purge all || true
```

## 9. Quick troubleshooting commands

```bash
find "/home/u753768407/domains/spearremovals.co.uk/public_html" -type d -exec chmod 755 {} \;
find "/home/u753768407/domains/spearremovals.co.uk/public_html" -type f -exec chmod 644 {} \;

cd /home/u753768407/domains/spearremovals.co.uk/public_html
git fetch origin main && git reset --hard origin/main
```

## 10. One-liner deploy (uses script defaults)

```bash
cd /home/u753768407/domains/spearremovals.co.uk/public_html && ./scripts/wp_site_deploy.sh --verbose
```

Refer back to `scripts/wp_site_deploy.sh --help` for additional flags or
behaviour tweaks.
