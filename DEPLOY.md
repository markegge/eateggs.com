# Deployment & server setup

One-time setup to get the CI/CD pipeline live, plus how day-to-day deploys work.
Items marked **(you)** need your credentials/console access; everything else is
in this repo already.

The pipeline lints every PR, then deploys to **production only** on merge to
`main`, gated by a manual approval. Production is reached over a **Cloudflare
Tunnel** at `ssh.eateggs.com` — no origin IP is exposed and port 22 stays closed
to the internet. There is **no staging environment**.

---

## 1. Server: paths and PHP **(you)**

SSH to the box (through the tunnel — see §4) and confirm where WordPress lives
and which PHP runs:

```bash
sudo find /var/www -maxdepth 4 -name wp-config.php 2>/dev/null
php -v
```

Current values (already baked into the GitHub secrets):

- WordPress root: `/var/www/eateggs.com/html`
- Theme dir (rsync target): `/var/www/eateggs.com/html/wp-content/themes/eateggs`

The rsync uses `--delete`, so `PROD_PATH` must be the **theme-specific** dir
above — never the `themes/` parent, or it would delete sibling themes. If the
server's PHP differs from 8.4, change `php-version` in
`.github/workflows/deploy.yml` to match.

---

## 2. Create a restricted deploy user **(you)**

A dedicated user that can only write the theme dir — not your personal login.

```bash
sudo adduser --disabled-password --gecos "" deploy
sudo chown -R deploy:www-data /var/www/eateggs.com/html/wp-content/themes
sudo find /var/www/eateggs.com/html/wp-content/themes -type d -exec chmod 2775 {} \;
```

The goal: `deploy` can rsync into the themes directory and nothing more sensitive.
Set the `PROD_USER` secret to whatever user you choose.

---

## 3. Install the deploy SSH key **(you)**

A CI-only key pair already exists locally (`eateggs_deploy` / `eateggs_deploy.pub`,
gitignored). The **private** key is already loaded into the `SSH_PRIVATE_KEY`
GitHub secret. Install the **public** key on the server for the deploy user:

```bash
sudo -u deploy mkdir -p /home/deploy/.ssh
sudo -u deploy bash -c 'cat >> /home/deploy/.ssh/authorized_keys' < eateggs_deploy.pub
sudo -u deploy chmod 700 /home/deploy/.ssh
sudo -u deploy chmod 600 /home/deploy/.ssh/authorized_keys
```

Delete your local `eateggs_deploy` afterwards — it lives in the secret and on the
server now. Host-key verification uses TOFU (`accept-new`), so no `known_hosts`
secret is needed.

---

## 4. Cloudflare Tunnel for SSH **(you)**

The runner connects with `ProxyCommand cloudflared access ssh --hostname
ssh.eateggs.com` (the workflow installs `cloudflared` itself). That requires:

1. A named tunnel on the server with an **ingress rule** routing
   `ssh.eateggs.com` → `ssh://localhost:22`, plus a DNS record for
   `ssh.eateggs.com` pointing at the tunnel.
2. **(Recommended) A Cloudflare Access** SSH/self-hosted application protecting
   `ssh.eateggs.com`. For headless CI, create an Access **service token**, add a
   policy that allows it, and set the two secrets below — the workflow passes
   them automatically when present:
   - `CF_ACCESS_CLIENT_ID`
   - `CF_ACCESS_CLIENT_SECRET`

   With no Access policy in front of the hostname, leave those unset and the
   workflow connects through the tunnel directly.

Use wrangler for the Cloudflare DNS/tunnel pieces (see `../CLAUDE.md`).

---

## 5. GitHub secrets **(you / done where noted)**

Repo → Settings → Secrets and variables → Actions:

| Secret | Value | Status |
|---|---|---|
| `SSH_PRIVATE_KEY` | Contents of `eateggs_deploy` (private key) | ✅ set |
| `PROD_HOST` | `ssh.eateggs.com` | ✅ set |
| `PROD_USER` | the deploy user (e.g. `deploy`) | ⬜ you |
| `PROD_PATH` | `/var/www/eateggs.com/html/wp-content/themes/eateggs` | ✅ set |
| `PROD_WP_PATH` | `/var/www/eateggs.com/html` | ✅ set |
| `CF_ACCESS_CLIENT_ID` | Access service-token id | ⬜ only if using Access |
| `CF_ACCESS_CLIENT_SECRET` | Access service-token secret | ⬜ only if using Access |

Set one from the CLI: `gh secret set PROD_USER --body "deploy"`.

---

## 6. GitHub Environment **(done)**

Repo → Settings → Environments → **`production`** exists with a **required
reviewer** (you). That is the manual gate: after lint passes on a merge to
`main`, the production job waits for your approval before touching the live site.

---

## 7. Activate the theme in WordPress **(you)**

After the first deploy lands the theme:

1. wp-admin → **Appearance → Themes → activate "eateggs"**.
2. **Settings → Reading:** show your latest posts.
3. **Appearance → Menus:** assign a menu to **"Primary Menu"** for the header nav.
4. **Settings → Permalinks:** Save once to flush rewrite rules.
5. Featured images become post/card hero images; posts without one show the
   styled placeholder. An optional **`distance`** custom field shows in the
   single-post sidebar when set.

---

## Day-to-day

1. Branch → edit theme → open PR. CI runs `php -l` + PHPCS. (Lint locally first —
   see the README.)
2. Merge to `main` → lint runs, then the production job **waits for your approval**.
3. Approve in the Actions run. It backs up the live theme to a timestamped
   `.bak-*` dir, then rsyncs and flushes cache.

### Rollback

Production keeps a timestamped backup before each deploy:

```bash
ssh deploy@ssh.eateggs.com    # via the tunnel (cloudflared ProxyCommand in ~/.ssh/config)
THEME=/var/www/eateggs.com/html/wp-content/themes/eateggs
ls -d "$THEME".bak-*
# restore one:
rm -rf "$THEME" && cp -a "$THEME.bak-<stamp>" "$THEME"
```

### Optional: Theme Check

CI covers syntax and coding standards. For WordPress-specific theme review, run
the [Theme Check](https://wordpress.org/plugins/theme-check/) plugin once in
wp-admin — it needs a live WordPress runtime, so it isn't part of CI.

---

## Maintenance reality check

Self-managed VPS means **you** own OS patching, PHP/nginx/MySQL upgrades, WordPress
core/plugin updates, and backups. This pipeline only handles theme code. Put OS
and WordPress updates on a real cadence (e.g. `unattended-upgrades` for security
patches, plus a monthly manual pass), and keep automated DB + uploads backups
independent of this repo.
