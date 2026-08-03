# Deployment Guide — Hostinger VPS

CI/CD for the Neral Gram Panchayat portal (`www.neralgov.com`).

| Workflow | File | Trigger |
|---|---|---|
| CI (tests + lint) | `.github/workflows/tests.yml` | push to `main` / `soham`, PRs to `main` |
| Deploy to production | `.github/workflows/deploy.yml` | push to `main`, or manual run |

Deploy pipeline: **test → build assets → rsync to VPS → run `scripts/deploy.sh` → health check**.
If any step of the server-side script fails, it **automatically rolls the code back** to the previous commit and brings the site back up.

---

## 1. One-time server setup

SSH into the VPS as root and run the following.

### Create a deploy user

```bash
adduser --disabled-password --gecos "" deploy
usermod -aG www-data deploy
```

### Give the deploy user ownership of the app

Replace `/var/www/neralgov` with your actual application path.

```bash
APP_PATH=/var/www/neralgov
chown -R deploy:www-data "$APP_PATH"
chmod -R 775 "$APP_PATH/storage" "$APP_PATH/bootstrap/cache"
```

### Allow the deploy user to reload PHP-FPM

The deploy script reloads PHP-FPM so OPcache doesn't serve stale code. Grant just that one command passwordless sudo — nothing else:

```bash
# Check your PHP version first: systemctl list-units --type=service | grep fpm
echo 'deploy ALL=(root) NOPASSWD: /bin/systemctl reload php8.3-fpm.service' \
  > /etc/sudoers.d/deploy-fpm
chmod 440 /etc/sudoers.d/deploy-fpm
visudo -c   # verify syntax
```

If you skip this, deploys still succeed — the script warns and continues, but you may need to reload PHP-FPM manually for changes to appear.

### Ensure the repo is a git clone

The deploy script does `git fetch` + `git reset --hard`, so the app directory must be a clone:

```bash
su - deploy
cd /var/www/neralgov
git remote -v      # should point at your GitHub repo
git checkout main
```

If the site was uploaded via FTP rather than cloned, back it up, clone the repo fresh, and copy `.env` and `storage/` across.

### Create the backups directory

```bash
mkdir -p /var/www/backups && chown deploy:deploy /var/www/backups && chmod 700 /var/www/backups
```

### Production `.env`

`.env` lives **only on the server** and is never overwritten by deploys. Verify:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.neralgov.com
```

> `APP_DEBUG=true` in production leaks database credentials and stack traces on error pages. Confirm it is `false`.

---

## 2. Generate the deploy SSH key

Run **locally**, not on the server:

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ./neralgov_deploy -N ""
```

This produces two files:

- `neralgov_deploy.pub` → goes on the **server**
- `neralgov_deploy` (private) → goes into **GitHub Secrets**

Install the public key for the deploy user:

```bash
ssh-copy-id -i ./neralgov_deploy.pub deploy@YOUR_VPS_IP
# or manually: append its contents to /home/deploy/.ssh/authorized_keys
```

Test it:

```bash
ssh -i ./neralgov_deploy deploy@YOUR_VPS_IP "cd /var/www/neralgov && git status"
```

Once the private key is in GitHub Secrets, delete your local copies:

```bash
rm neralgov_deploy neralgov_deploy.pub
```

---

## 3. GitHub Secrets

Add these under **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | Required | Example | Notes |
|---|---|---|---|
| `VPS_HOST` | yes | `82.112.x.x` | VPS IP or hostname |
| `VPS_USER` | yes | `deploy` | SSH user |
| `VPS_SSH_KEY` | yes | `-----BEGIN OPENSSH PRIVATE KEY-----…` | Full contents of the **private** key, including header/footer lines |
| `VPS_APP_PATH` | yes | `/var/www/neralgov` | Absolute path, **no trailing slash** |
| `VPS_PORT` | no | `22` | Only if SSH runs on a non-standard port |
| `HEALTH_CHECK_URL` | no | `https://www.neralgov.com` | Defaults to the production URL |

### Recommended: require approval before production deploys

Under **Settings → Environments → New environment**, create one named `production` and add yourself as a required reviewer. The deploy job already targets this environment, so every production deploy will then wait for a human click.

---

## 4. First deploy

Do the first one manually so you can watch it:

1. **Actions** tab → **Deploy to Hostinger VPS** → **Run workflow**
2. Watch the `deploy` job logs.

Or just push to `main`.

### Verify afterwards

```bash
ssh deploy@YOUR_VPS_IP "cd /var/www/neralgov && git log -1 --oneline && php artisan about | head -20"
ls -lh /var/www/backups/     # a fresh .sql.gz should be present
```

---

## 5. Database safety

Every deploy dumps the database **before** running migrations:

- Written to `/var/www/backups/<database>-<timestamp>.sql.gz`
- The deploy **aborts before migrating** if the dump comes out empty
- The 10 most recent dumps are kept; older ones are pruned automatically

### Restoring a backup

Rollback of *code* is automatic on failure. Restoring *data* is deliberately manual, so a bad migration never triggers an unsupervised database overwrite:

```bash
gunzip -c /var/www/backups/sohamgrampanchayat-20260803-120000.sql.gz \
  | mysql -u YOUR_DB_USER -p YOUR_DB_NAME
```

### Deploying without migrations

**Actions → Deploy → Run workflow**, tick **Skip database migrations**. Useful when shipping a view/CSS-only change during sensitive periods.

---

## 6. Troubleshooting

**Health check fails but the site loads in a browser**
The check expects HTTP 200 at `HEALTH_CHECK_URL`. If your root URL redirects (e.g. to `/en`), point the secret at a URL that returns 200 directly.

**`Host key verification failed`**
The server's SSH fingerprint changed (e.g. VPS rebuilt). The workflow re-runs `ssh-keyscan` each time, so simply re-run the job.

**`Permission denied (publickey)`**
`VPS_SSH_KEY` must contain the entire private key including the `-----BEGIN`/`-----END` lines. Confirm the public key is in `/home/deploy/.ssh/authorized_keys` with `chmod 600` on that file and `700` on `.ssh`.

**Site stuck in maintenance mode after a failed deploy**
```bash
ssh deploy@YOUR_VPS_IP "cd /var/www/neralgov && php artisan up"
```

**Changes deployed but old content still shows**
PHP-FPM wasn't reloaded — see *Allow the deploy user to reload PHP-FPM* above. Manually: `sudo systemctl reload php8.3-fpm`.

**`git reset --hard` wiped an uncommitted server-side change**
Anything edited directly on the server outside `.env` and `storage/` will be overwritten by deploys. Commit changes to the repo instead of editing live files.

---

## 7. Manual deploy (fallback)

If GitHub Actions is unavailable:

```bash
ssh deploy@YOUR_VPS_IP
cd /var/www/neralgov
DEPLOY_REF=origin/main ./scripts/deploy.sh /var/www/neralgov
```

The script is self-contained and performs the same backup, migration, cache and rollback steps.
