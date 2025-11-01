# Backup & Recovery Workflow

## Overview
The `scripts/wp_site_deploy.sh` script automates the nightly backup routine for the Spear Removals WordPress install.

What is protected:

- **Database export** generated via WP-CLI (`wp db export`) with an automatic `mysqldump` fallback.
- **File snapshot** of `wp-config.php`, `wp-content/themes`, and `wp-content/plugins` stored as a `.tgz` archive.
- **Retention policy:** database dumps (`*.sql`) and file archives (`*.tgz`) older than 7 days are pruned automatically.
- **Logging:** each run appends a summary line to `$BACKUP_DIR/backup-status.log`; detailed failures are appended to `$BACKUP_DIR/export-fail.log` and surfaced through alerts.

Backups are written to `${BACKUP_DIR:-$HOME/backups}` by default. Override `BACKUP_DIR` if a different volume is required.

## Scheduling
Run `scripts/install_backup_cron.sh` once (as the hosting account user or as root) to install a nightly cron job for 03:00 UTC:

```bash
# As the site user
./scripts/install_backup_cron.sh

# Or as root, targeting the hosting account
SITE_USER=u753768407 ./scripts/install_backup_cron.sh
```

The installer rewrites any existing entry for `wp_site_deploy.sh` and stores cron output in `$BACKUP_DIR/wp-site-backup-cron.log`. Verify the entry with:

```bash
crontab -l | grep wp_site_deploy.sh
```

## Monitoring & Alerts
- `backup-status.log` contains a timestamped status with the database and file snapshot results (`OK`, `FAIL`, or `SKIPPED`).
- `export-fail.log` captures the detailed reason whenever a backup component fails.
- `wp-site-backup-cron.log` records cron runtime output for troubleshooting.
- Configure notifications by exporting one (or both) environment variables before invoking the script or within the cron entry:
  - `SLACK_WEBHOOK_URL` – Slack incoming webhook used for JSON alerts.
  - `ALERT_EMAIL` – email recipient used if the `mail` CLI is available.

Any run that results in an empty backup artifact or new `export-fail.log` entries triggers an alert.

## Backup Directory Requirements
Before enabling automation ensure:

1. The backup directory is writable by the hosting account:
   ```bash
   mkdir -p $HOME/backups
   chmod 700 $HOME/backups
   test -w $HOME/backups && echo "Writable"
   ```
2. There is at least 1 GiB of free disk space available where the backups live:
   ```bash
   df -h $HOME/backups
   ```
   Update `MIN_FREE_KB` if the threshold needs to be adjusted.

Document the location and capacity in your hosting runbook so future operators know where to extend storage if required.

## Manual Execution
To run a one-off backup (outside cron):

```bash
cd /home/u753768407/domains/spearremovals.co.uk/public_html
./scripts/wp_site_deploy.sh --verbose
```

Optional flags:

- `--skip-backups` – skip the backup phase.
- `--skip-maintenance` – avoid toggling WordPress maintenance mode.
- `--skip-perms` – skip filesystem permission resets.

Check `$BACKUP_DIR` afterwards for matching `.sql` and `.tgz` files and inspect `backup-status.log` for the latest status line.

## Rollback Procedure
1. **Restore the database**
   ```bash
   # Option A: using WP-CLI
   wp --path=/home/u753768407/domains/spearremovals.co.uk/public_html db import /path/to/db-YYYYmmdd-HHMMSS.sql

   # Option B: using mysql directly
   mysql -h DB_HOST -u DB_USER -pDB_PASSWORD DB_NAME < /path/to/db-YYYYmmdd-HHMMSS.sql
   ```
2. **Restore site files**
   ```bash
   cd /home/u753768407/domains/spearremovals.co.uk/public_html
   tar -xzf /path/to/files-YYYYmmdd-HHMMSS.tgz
   ```
   The archive contains `wp-config.php`, `wp-content/themes`, and `wp-content/plugins`. Review permissions afterwards:
   ```bash
   find wp-content/themes wp-content/plugins -type d -exec chmod 755 {} \;
   find wp-content/themes wp-content/plugins -type f -exec chmod 644 {} \;
   ```
3. Flush caches if WP-CLI is available:
   ```bash
   wp cache flush
   wp litespeed-purge all
   ```

Always confirm the site loads correctly before removing older backup copies.

## Testing Checklist
After any change to the backup workflow:

1. Run `wp_site_deploy.sh` manually.
2. Confirm fresh `.sql` and `.tgz` files exist in `$BACKUP_DIR`.
3. Review `backup-status.log` for an `OK` entry.
4. Confirm old artifacts (>7 days) were pruned (`find $BACKUP_DIR -mtime +7`).
5. Review cron and alert channels for noise-free runs.

Record the test date in your internal change log.
