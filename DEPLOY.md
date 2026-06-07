# Deployment & server setup

One-time setup to get the CI/CD pipeline live, plus how day-to-day deploys work.
Do the steps in order. Items marked **(you)** need your credentials/console
access; everything else is in this repo already.

---

## 0. Find the origin IP behind Cloudflare **(you)**

`eateggs.com` resolves to Cloudflare's proxy IPs, so you can't `dig` the real
server. Get the origin address one of these ways:

- **Cloudflare dashboard → your domain → DNS → Records.** Even with the orange
  proxy cloud on, the `A` record's *Content* column shows the origin IP.
- **Linode/Akamai Cloud Manager → your instance.** The public IPv4 is listed on
  the instance page.

You'll SSH to that IP (or to a non-proxied hostname like a `direct.eateggs.com`
A-record set to "DNS only"). Don't put the origin IP in a proxied public record.

> Tip: keep the origin IP out of git. It only needs to live in GitHub secrets.

---

## 1. Pick paths and PHP version on the server **(you)**

SSH in and confirm where WordPress lives and which PHP runs:

```bash
# Find the WordPress root (look for wp-config.php)
sudo find /var/www /srv /usr/share/nginx -maxdepth 3 -name wp-config.php 2>/dev/null

# PHP version the site uses
php -v
```

Note the theme target path, e.g. `/var/www/eateggs.com/wp-content/themes/eateggs`.
If the server's PHP differs from 8.1, change `php-version` in
`.github/workflows/deploy.yml` to match (the linter should mirror production).

---

## 2. Create a restricted deploy user **(you)**

A dedicated user that can only write the web directory — not your personal login.

```bash
sudo adduser --disabled-password --gecos "" deploy
# Give it ownership of (only) the theme/web dir it needs to write:
sudo chown -R deploy:www-data /var/www/eateggs.com/wp-content/themes
sudo find /var/www/eateggs.com/wp-content/themes -type d -exec chmod 2775 {} \;
```

Adjust the path to match step 1. The goal: `deploy` can rsync into the themes
directory and nothing more sensitive.

---

## 3. Generate the deploy SSH key **(you)**

Make a key pair used *only* by CI. Run locally:

```bash
ssh-keygen -t ed25519 -f ./eateggs_deploy -N "" -C "github-actions-deploy"
```

Install the **public** key on the server for the `deploy` user:

```bash
# copy eateggs_deploy.pub contents, then on the server:
sudo -u deploy mkdir -p /home/deploy/.ssh
sudo -u deploy bash -c 'cat >> /home/deploy/.ssh/authorized_keys' < eateggs_deploy.pub
sudo -u deploy chmod 700 /home/deploy/.ssh
sudo -u deploy chmod 600 /home/deploy/.ssh/authorized_keys
```

The **private** key (`eateggs_deploy`) goes into a GitHub secret (step 5). Delete
your local copies afterward.

Get the server's host key for `known_hosts` (prevents man-in-the-middle):

```bash
ssh-keyscan -H <ORIGIN_IP_OR_HOST> 2>/dev/null
```

Copy that output — it becomes the `SSH_KNOWN_HOSTS` secret.

---

## 4. Stand up staging **(you)**

A staging copy so merges are verified before the live site. Two common options:

- **Same box, separate vhost:** an nginx server block for `staging.eateggs.com`
  with its own web root, its own DB (or a copy), and its own theme dir
  (`/var/www/staging.eateggs.com/wp-content/themes/eateggs`). Add a Cloudflare DNS
  record for `staging` (consider "DNS only" + HTTP auth so it isn't indexed).
- **Cheap second Linode:** fuller isolation, a few dollars a month.

Whichever you choose, note the staging theme path and (optionally) the WordPress
root for `wp cache flush`.

> If you want to skip staging at first, delete the `deploy-staging` job from
> `deploy.yml` and remove `needs: deploy-staging` from `deploy-production`. Not
> recommended for a live blog, but it's your call.

---

## 5. Add GitHub secrets **(you)**

Repo → Settings → Secrets and variables → Actions → *New repository secret*:

| Secret | Value |
|---|---|
| `SSH_PRIVATE_KEY` | Contents of `eateggs_deploy` (the private key) |
| `SSH_KNOWN_HOSTS` | Output of `ssh-keyscan` from step 3 |
| `STAGING_HOST` | Origin IP / hostname of staging |
| `STAGING_USER` | `deploy` |
| `STAGING_PATH` | e.g. `/var/www/staging.eateggs.com/wp-content/themes/eateggs` |
| `STAGING_WP_PATH` | WordPress root for staging (for `wp cache flush`) |
| `PROD_HOST` | Origin IP / hostname of production |
| `PROD_USER` | `deploy` |
| `PROD_PATH` | e.g. `/var/www/eateggs.com/wp-content/themes/eateggs` |
| `PROD_WP_PATH` | WordPress root for production |

---

## 6. Configure GitHub Environments **(you)**

Repo → Settings → Environments:

- Create **`staging`** (no protection needed).
- Create **`production`** and add a **Required reviewer** (yourself). This is the
  manual gate: after staging deploys, the production job waits for your approval
  in the Actions run before touching the live site.

---

## 7. Activate the theme in WordPress **(you)**

After the first deploy lands the theme on the server:

1. wp-admin → **Appearance → Themes → activate "eateggs"**.
2. **Settings → Reading:** set the homepage to show your latest posts (the
   `index.php` template is the blog home), or a static page if you prefer.
3. **Appearance → Menus:** create a menu and assign it to **"Primary Menu"** to
   drive the header nav. Without one, the header shows a sensible default.
4. **Settings → Permalinks:** click Save once to flush rewrite rules.
5. Featured images become post/card hero images; posts without one show the
   styled placeholder. An optional **`distance`** custom field shows in the
   single-post sidebar when set.

---

## Day-to-day

1. Branch → edit theme → open PR. CI runs `php -l` + PHPCS.
2. Merge to `main` → deploys to staging automatically.
3. Check `staging.eateggs.com`, then approve the production job in the Actions run.

### Rollback

Production keeps a timestamped backup before each deploy:

```bash
ssh deploy@<PROD_HOST>
ls -d /var/www/eateggs.com/wp-content/themes/eateggs.bak-*
# restore one:
rm -rf .../themes/eateggs && cp -a .../themes/eateggs.bak-<stamp> .../themes/eateggs
```

### Optional: Theme Check

CI covers syntax and coding standards. For WordPress-specific theme review, run
the [Theme Check](https://wordpress.org/plugins/theme-check/) plugin once on
staging — it needs a live WordPress runtime, so it isn't part of CI.

---

## Maintenance reality check

Self-managed VPS means **you** own OS patching, PHP/nginx/MySQL upgrades, WordPress
core/plugin updates, and backups. This pipeline only handles theme code. Put OS
and WordPress updates on a real cadence (e.g. `unattended-upgrades` for security
patches, plus a monthly manual pass), and make sure you have automated DB +
uploads backups independent of this repo.
