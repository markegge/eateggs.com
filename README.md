# eateggs.com

Custom code for [www.eateggs.com](https://www.eateggs.com) — a WordPress blog on a
Linode (Ubuntu) VPS running nginx + MySQL + PHP, fronted by Cloudflare.

This repo tracks **only custom code**, not WordPress core, plugins, uploads, or
secrets (see `.gitignore`). Right now that's the `eateggs` theme.

```
wp-content/themes/eateggs/   The theme (converted from the static design handoff)
.github/workflows/deploy.yml CI: lint on PRs, deploy to staging then production
phpcs.xml                    WordPress Coding Standards config for the linter
DEPLOY.md                    One-time server + GitHub setup, and how deploys work
```

## How changes ship

1. Branch, edit the theme, open a pull request.
2. CI lints the PR (`php -l` + PHPCS). Fix anything it flags.
3. Merge to `main` → auto-deploys to **staging** (`staging.eateggs.com`).
4. Verify staging, then approve the **production** deploy in the GitHub Actions
   run. Production keeps a timestamped backup of the previous theme for rollback.

Database content (posts, pages, settings) is **not** in this repo — that lives in
MySQL and is edited in wp-admin as usual.

See **[DEPLOY.md](DEPLOY.md)** for the full setup, including how to find the
origin server IP behind Cloudflare.
