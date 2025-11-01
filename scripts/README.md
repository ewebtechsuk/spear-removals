# Deployment helper scripts

This directory collects utility scripts that support the WordPress deployment
workflow for spearremovals.co.uk.

## `wp_site_deploy.sh`

Automates the pre/post deployment flow that the team previously ran manually in
the shell. The script:

1. Creates timestamped database and file backups (unless `--skip-backups` is
   provided).
2. Keeps the remote set to `https://github.com/ewebtechsuk/spear-removals.git`
   and fast-forwards the target branch, stashing any local changes first.
3. Toggles WordPress maintenance mode via WP-CLI when available, falling back to
   the `.maintenance` flag when WP-CLI is not installed.
4. Flushes WordPress/LiteSpeed caches and normalises filesystem permissions
   unless instructed otherwise.

### Usage

```bash
./wp_site_deploy.sh [options]
```

| Flag | Description |
| --- | --- |
| `-u`, `--user` | Hosting account/UNIX username (defaults to `u753768407`). |
| `-d`, `--dir` | Absolute path to the WordPress install. |
| `-b`, `--branch` | Git branch to deploy (default `main`). |
| `-r`, `--repo` | Remote Git repository URL. |
| `--skip-backups` | Skip database/files backup. |
| `--skip-maintenance` | Do not toggle maintenance mode. |
| `--skip-perms` | Skip filesystem permissions reset. |
| `-v`, `--verbose` | Echo each command before it runs. |

Environment variables `SITE_USER`, `SITE_DIR`, `BRANCH`, and `REPO_URL` offer an
alternative way to override defaults.

> **Note:** The script expects WP-CLI to be in the `PATH` when maintenance mode
> or cache flush commands should be run. When WP-CLI is missing, it falls back
> to creating/removing the `.maintenance` file only.

For a step-by-step checklist that matches the operations run on Hostinger
(including cron, GitHub Actions, rollbacks, and troubleshooting commands), see
[`../docs/hostinger-deployment.md`](../docs/hostinger-deployment.md).
