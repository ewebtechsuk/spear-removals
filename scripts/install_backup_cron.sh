#!/usr/bin/env bash
set -euo pipefail

SCHEDULE=${CRON_SCHEDULE:-"0 3 * * *"}
SITE_USER=${SITE_USER:-$(whoami)}
SCRIPT_PATH=${SCRIPT_PATH:-$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/wp_site_deploy.sh}

if [[ $(id -u) -ne 0 ]]; then
    SITE_USER=$(whoami)
fi

USER_HOME=$(eval echo "~$SITE_USER" 2>/dev/null || echo "$HOME")
BACKUP_TARGET=${BACKUP_DIR:-"$USER_HOME/backups"}
LOG_PATH=${LOG_PATH:-"$BACKUP_TARGET/wp-site-backup-cron.log"}
mkdir -p "$(dirname "$LOG_PATH")"
CRON_CMD="$SCHEDULE SITE_USER=$SITE_USER BACKUP_DIR=$BACKUP_TARGET VERBOSE=0 $SCRIPT_PATH >> $LOG_PATH 2>&1"

ensure_cron_entry() {
    local tmp_file
    tmp_file=$(mktemp)
    trap 'rm -f "$tmp_file"' EXIT

    if crontab -u "$SITE_USER" -l >/dev/null 2>&1; then
        crontab -u "$SITE_USER" -l >"$tmp_file"
    fi

    if [[ -s $tmp_file ]]; then
        grep -vF "$SCRIPT_PATH" "$tmp_file" >"${tmp_file}.filtered" || true
        mv "${tmp_file}.filtered" "$tmp_file"
    fi

    echo "$CRON_CMD" >>"$tmp_file"
    crontab -u "$SITE_USER" "$tmp_file"
    echo "Cron entry installed for $SITE_USER: $CRON_CMD"
}

ensure_cron_entry
