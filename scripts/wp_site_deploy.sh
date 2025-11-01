#!/usr/bin/env bash
set -euo pipefail

show_help() {
    cat <<'USAGE'
Usage: wp_site_deploy.sh [options]

Automate the standard pre/post deployment workflow for the Spear Removals
WordPress site. The script mirrors the manual checklist shared with the team
and can be customised via flags or environment variables.

Options:
  -u, --user USER           Override the hosting account/UNIX user.
  -d, --dir PATH            Override the absolute path to the WordPress install.
  -b, --branch BRANCH       Git branch to deploy (default: main).
  -r, --repo URL            Remote Git repository URL.
      --skip-backups        Skip the database/files backup step.
      --skip-maintenance    Do not toggle maintenance mode automatically.
      --skip-perms          Skip the filesystem permissions fix.
  -v, --verbose             Echo each command before it runs.
  -h, --help                Show this message and exit.

Environment overrides:
  SITE_USER, SITE_DIR, BRANCH, REPO_URL

Examples:
  SITE_USER="u123" ./wp_site_deploy.sh --branch production
  ./wp_site_deploy.sh -u u123 -d /home/u123/domains/example.com/public_html
USAGE
}

if [[ $# -gt 0 ]]; then
    while [[ $# -gt 0 ]]; do
        case "$1" in
            -u|--user)
                SITE_USER="$2"
                shift 2
                ;;
            -d|--dir)
                SITE_DIR="$2"
                shift 2
                ;;
            -b|--branch)
                BRANCH="$2"
                shift 2
                ;;
            -r|--repo)
                REPO_URL="$2"
                shift 2
                ;;
            --skip-backups)
                SKIP_BACKUPS=1
                shift
                ;;
            --skip-maintenance)
                SKIP_MAINTENANCE=1
                shift
                ;;
            --skip-perms)
                SKIP_PERMS=1
                shift
                ;;
            -v|--verbose)
                VERBOSE=1
                shift
                ;;
            -h|--help)
                show_help
                exit 0
                ;;
            *)
                echo "Unknown option: $1" >&2
                show_help
                exit 1
                ;;
        esac
    done
fi

SITE_USER=${SITE_USER:-u753768407}
SITE_DIR=${SITE_DIR:-"/home/${SITE_USER}/domains/spearremovals.co.uk/public_html"}
BRANCH=${BRANCH:-main}
REPO_URL=${REPO_URL:-"https://github.com/ewebtechsuk/spear-removals.git"}
SKIP_BACKUPS=${SKIP_BACKUPS:-0}
SKIP_MAINTENANCE=${SKIP_MAINTENANCE:-0}
SKIP_PERMS=${SKIP_PERMS:-0}
VERBOSE=${VERBOSE:-0}

run_cmd() {
    if [[ $VERBOSE -eq 1 ]]; then
        echo "+ $*"
    fi
    "$@"
}

ensure_wp_cli_disabled=0

activate_maintenance() {
    if [[ $SKIP_MAINTENANCE -eq 1 ]]; then
        return
    fi

    if command -v wp >/dev/null 2>&1; then
        run_cmd wp --path="$SITE_DIR" maintenance-mode activate || true
        ensure_wp_cli_disabled=1
    else
        echo "Enabling manual maintenance flag (.maintenance)."
        echo "<?php $upgrading = time(); ?>" >"$SITE_DIR/.maintenance"
    fi
}

deactivate_maintenance() {
    if [[ $SKIP_MAINTENANCE -eq 1 ]]; then
        return
    fi

    if command -v wp >/dev/null 2>&1; then
        if [[ $ensure_wp_cli_disabled -eq 1 ]]; then
            run_cmd wp --path="$SITE_DIR" maintenance-mode deactivate || true
        fi
    else
        rm -f "$SITE_DIR/.maintenance"
    fi
}

cleanup() {
    deactivate_maintenance
}

trap cleanup EXIT

if [[ ! -d $SITE_DIR ]]; then
    echo "Directory not found: $SITE_DIR" >&2
    exit 1
fi

run_cmd cd "$SITE_DIR"

date_tag="$(date +%Y%m%d-%H%M%S)"
BACKUP_DIR=${BACKUP_DIR:-"$HOME/backups"}
SQL_FILE="$BACKUP_DIR/db-$date_tag.sql"
FILES_FILE="$BACKUP_DIR/files-$date_tag.tgz"
run_cmd mkdir -p "$BACKUP_DIR"

echo "== Checking git repo =="
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "This folder is not a git repo. Initializing and linking remote..."
    run_cmd git init
    run_cmd git remote add origin "$REPO_URL"
fi

if [[ $SKIP_BACKUPS -eq 0 ]]; then
    echo "== Saving quick backup =="
    activate_maintenance

    DB_NAME=${DB_NAME:-$(php -r "include 'wp-config.php'; echo DB_NAME;" 2>/dev/null || true)}
    DB_USER=${DB_USER:-$(php -r "include 'wp-config.php'; echo DB_USER;" 2>/dev/null || true)}
    DB_PASSWORD=${DB_PASSWORD:-$(php -r "include 'wp-config.php'; echo DB_PASSWORD;" 2>/dev/null || true)}
    DB_HOST=${DB_HOST:-$(php -r "include 'wp-config.php'; echo DB_HOST;" 2>/dev/null || echo localhost)}

    if [[ -z $DB_NAME || -z $DB_USER || -z $DB_PASSWORD ]]; then
        echo "WARNING: Database credentials unavailable; skipping DB export." >>"$BACKUP_DIR/export-fail.log"
    else
        if command -v wp >/dev/null 2>&1; then
            if [[ $VERBOSE -eq 1 ]]; then
                echo "+ wp --path=\"$SITE_DIR\" db export \"$SQL_FILE\" --add-drop-table --no-tablespaces --dbhost=\"$DB_HOST\" --dbuser=\"$DB_USER\" --dbpass=***"
            fi
            if ! wp --path="$SITE_DIR" db export "$SQL_FILE" --add-drop-table --no-tablespaces --dbhost="$DB_HOST" --dbuser="$DB_USER" --dbpass="$DB_PASSWORD"; then
                echo "DB export failed via WP-CLI. Falling back to mysqldump."
            fi
        fi

        if [[ ! -s $SQL_FILE ]]; then
            if ! mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" --no-tablespaces >"$SQL_FILE"; then
                echo "ERROR: DB export failed at $date_tag" >>"$BACKUP_DIR/export-fail.log"
            fi
        fi

        if [[ ! -s $SQL_FILE ]]; then
            echo "ERROR: DB export failed at $date_tag" >>"$BACKUP_DIR/export-fail.log"
        fi
    fi

    if ! tar -czf "$FILES_FILE" \
        wp-config.php \
        wp-content/themes \
        wp-content/plugins 2>/dev/null; then
        echo "Files snapshot skipped."
    fi

    if [[ -s $FILES_FILE ]]; then
        echo "Files backup stored at $FILES_FILE"
    else
        echo "WARNING: Files backup appears empty" >>"$BACKUP_DIR/export-fail.log"
    fi

    if [[ -s $SQL_FILE ]]; then
        echo "Database backup stored at $SQL_FILE"
    fi

    echo "== Pruning backups older than 7 days =="
    find "$BACKUP_DIR" -type f \( -name '*.sql' -o -name '*.tgz' \) -mtime +7 -delete
else
    echo "== Skipping backups (per flag) =="
fi

echo "== Ensuring correct remote & branch =="
run_cmd git remote set-url origin "$REPO_URL"
run_cmd git fetch --all --prune

git remote -v

if ! git diff --quiet || ! git diff --staged --quiet; then
    echo "Local changes detected. Stashing..."
    run_cmd git stash push -u -m "pre-deploy-$date_tag"
fi

echo "== Checkout & fast-forward =="
if ! git checkout "$BRANCH"; then
    run_cmd git checkout -b "$BRANCH"
fi
if ! git pull --ff-only origin "$BRANCH"; then
    echo "Fast-forward failed (history diverged). Performing hard reset to origin/$BRANCH."
    run_cmd git fetch origin "$BRANCH"
    run_cmd git reset --hard "origin/$BRANCH"
fi

echo "== Running post-deploy steps =="
if command -v wp >/dev/null 2>&1; then
    run_cmd wp --path="$SITE_DIR" cache flush || true
    run_cmd wp --path="$SITE_DIR" litespeed-purge all || true
fi

deactivate_maintenance

if [[ $SKIP_PERMS -eq 0 ]]; then
    run_cmd find "$SITE_DIR" -type d -exec chmod 755 {} \;
    run_cmd find "$SITE_DIR" -type f -exec chmod 644 {} \;
else
    echo "== Skipping permissions reset =="
fi

echo "== DONE =="
echo "Backups:"
run_cmd ls -lh "$HOME/backups"
