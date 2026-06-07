# CLAUDE.md — eateggs.com

Guidance for Claude Code working in this repo. Read this first.

## What this is

Custom code for **www.eateggs.com**, a WordPress blog running on a Linode
(Ubuntu) VPS — **nginx + MySQL + PHP**, fronted by **Cloudflare**. The repo
tracks **only custom code** (currently the `eateggs` theme). WordPress core,
plugins, uploads, and secrets are intentionally **not** in git (see
`.gitignore`). Post/page content lives in MySQL and is edited in wp-admin — it is
**not** in this repo and does not flow through the pipeline.

## Layout

```
wp-content/themes/eateggs/   The theme (the only tracked code today)
.github/workflows/deploy.yml CI: lint on PRs → deploy staging → deploy prod (manual approval)
phpcs.xml                    WordPress Coding Standards config the CI linter uses
README.md                    Short overview
DEPLOY.md                    One-time server + GitHub setup, and how deploys work
CLAUDE.md                    This file
```

## The theme — how it was built and why

The theme was converted from a **static HTML/CSS design** (the original handoff,
`eateggs.com update.zip`, kept locally but gitignored). Two design pages
(`index.html`, `post.html`) became the classic-theme template set. The markup and
class names are preserved so the CSS keeps working; WordPress logic was threaded
in. Non-obvious mechanics you must respect:

- **Asset enqueue order matters** (`functions.php` → `eateggs_assets()`):
  `tokens.css` defines CSS custom properties **and** the `@font-face` rules, and
  `styles.css` consumes them — so tokens is enqueued first. `tokens.css`
  references the local fonts with **relative** `url("fonts/...")`, so it **must
  stay in `assets/` next to `assets/fonts/`**. Don't move it.
- The CSS variables are named `--gtfs-*` and the tokens file header says "GTFS
  Builder" — leftover from the design template. Cosmetic; don't be confused by it.
- **Single-post table of contents** is generated, not hand-written.
  `eateggs_inject_heading_ids()` (filter on `the_content`, priority 7) slugs
  `<h2>/<h3>` into ids and collects them; `single.php` runs the content filters
  into a variable **before** rendering the sidebar, then calls
  `eateggs_render_toc()`. `assets/scroll-spy.js` (enqueued only on single posts)
  highlights the active section.
- **Read time** is computed from word count (`eateggs_read_time()`, ~200 wpm).
- **Nav**: header renders a flat `<a>` list (the design styles direct children of
  `.nav-links`). A user-assigned **"Primary Menu"** drives it via a custom
  `Eateggs_Link_Walker` (no `<ul>/<li>`); with no menu assigned it falls back to a
  single "Latest" link. The Subscribe button points at `/subscribe/` (a page that
  doesn't exist yet).
- **Images**: posts without a featured image show a styled `.ph` placeholder.
  An optional **`distance`** post custom field shows in the single-post sidebar.
- `index.php` is also the fallback for category/archive/tag listings (no dedicated
  `archive.php`/`category.php` yet), so those reuse the featured-+-grid layout.

## Conventions (CI enforces these)

- **WordPress Coding Standards** via `phpcs.xml` (WordPress-Core/-Docs/-Extra).
  Escape all output (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`),
  internationalize strings with the **`eateggs`** text domain, and add
  `translators:` comments for any `printf`/`sprintf` placeholders.
- Prefix all theme functions/globals with `eateggs_` / classes with `Eateggs_`.
- Target **PHP 8.1** (matches the linter; keep it in sync with the server's PHP —
  see `DEPLOY.md` §1).
- Note: there's no PHP CLI on the host, but **Docker is available** — run the
  CI lint locally with a PHP 8.1 + PHPCS image (matches CI exactly). Build:
  `docker build -f /tmp/phpcs.Dockerfile -t eateggs-phpcs /tmp` (see the recipe
  in Claude's project memory), then from the repo root:
  `docker run --rm -v "$PWD":/app -w /app eateggs-phpcs phpcbf` to auto-fix and
  `… phpcs -q --report=summary` to check. **Run this before pushing** — the lint
  gate blocks deploys. For WordPress-specific review (Theme Check) use a local WP.

## Deploy flow

Branch → edit theme → open PR. CI lints (`php -l` + PHPCS). Merge to `main` →
CI lints, then the **production** job waits for a **manual approval** (the
`production` GitHub Environment's required reviewer), backs up the live theme to
a timestamped `.bak-*` dir, then rsyncs and flushes cache. **Production only —
there is no staging.** The runner reaches the server over a **Cloudflare Tunnel**
(`ssh.eateggs.com`) via `cloudflared access ssh`; set `CF_ACCESS_CLIENT_ID` /
`CF_ACCESS_CLIENT_SECRET` secrets if the hostname is behind Cloudflare Access.
Rollback = restore a `.bak-*` dir (see `DEPLOY.md` → Rollback).

If you rename the theme directory, update **all** of: `.gitignore` (the
`!wp-content/themes/eateggs/` whitelist), `phpcs.xml` (`<file>`), and
`deploy.yml` (`THEME_DIR` + the `PROD_PATH`/`PROD_WP_PATH` secrets on the server).

## Outstanding work (the actual handoff)

Setup status — repo and GitHub-side config are done; server side is the owner's:

1. ✅ **GitHub repo** created: `github.com/markegge/eateggs.com` (public).
2. **GitHub secrets + Environment** — `DEPLOY.md` §5–6. Done: the `production`
   Environment (required reviewer) and the `SSH_PRIVATE_KEY`, `PROD_HOST`,
   `PROD_PATH`, `PROD_WP_PATH` secrets. Still needed: `PROD_USER`, and
   `CF_ACCESS_CLIENT_ID`/`CF_ACCESS_CLIENT_SECRET` if the SSH host is behind
   Cloudflare Access.
3. **Server prep** — `DEPLOY.md` §2–4: create the restricted `deploy` user,
   install `eateggs_deploy.pub`, and stand up the cloudflared tunnel for
   `ssh.eateggs.com`. (No origin IP needed — access is via the tunnel.)
4. **Activate + configure in wp-admin** — `DEPLOY.md` §7: activate "eateggs", set
   Reading to show latest posts, assign a Primary Menu, save Permalinks.

Theme refinements worth considering (design was a 2-page prototype):

- The `index.php` `.idx-head` heading/lede and the "Subscribe"/`/subscribe/`
  target are **hardcoded editorial copy/links** — wire to options/a real page if
  desired. The Instagram footer link is a `#` placeholder.
- No `comments.php` (uses WP default), no dedicated `archive.php`/`category.php`,
  no `screenshot.png` for the theme picker. Add if/when wanted.
- Decide branch strategy. Current assumption: PRs into `main`; `main` is the
  deploy branch.

## Boundaries

- Never commit `wp-config.php`, uploads, plugins, or core (all gitignored). Don't
  add secrets to the repo — they belong in GitHub Actions secrets.
- This pipeline ships **theme code only**. Database content and server/OS/WordPress
  patching are out of scope here (see `DEPLOY.md` → Maintenance reality check).
- Cowork-sandbox note: the mounted folder blocks file deletes until granted, which
  breaks git operations; this does not affect a normal Mac/Claude Code checkout.
