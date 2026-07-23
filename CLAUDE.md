# Maintenance mode by Juraj Hamara

Lightweight WordPress maintenance-mode plugin, hosted on WordPress.org.

- WordPress.org slug: `maintenance-mode-by-juraj-hamara`
- GitHub repo: `jurajhamara/wp-maintenance-mode`
- Current version: 1.2.0
- Text domain: `juraj-hamara-maintenance` (Domain Path `/languages`)

## What the plugin does (current design)

An active plugin means maintenance is ON (there is no settings option for it).
Logged-in users who can `delete_published_posts` (editors and admins) keep full
access; everyone else, including bots, gets the maintenance screen. A red notice
in the admin bar ends maintenance by deactivating the plugin (admins only).

## Repository layout

Plugin files live at the REPO ROOT so the deploy action can push them straight
to SVN trunk:

- `maintenance.php` - the whole plugin (single file)
- `readme.txt` - the file WordPress.org reads (=== / == format)
- `README.md` - for GitHub only (excluded from the WP.org deploy)
- `languages/` - .pot template, plus .po and compiled .mo per locale
- `.github/workflows/deploy.yml` - deploy to WordPress.org on tag push
- `.distignore` - files kept OUT of the WordPress.org deploy
- `LICENSE`, `.gitignore`

## Conventions (do not violate)

- ONE version of the code everywhere. Do NOT create a separate minified or
  comment-stripped copy; that only causes drift. Keep docblocks. Before a
  release, remove only temporary TODO/FIXME notes.
- Prefix every PHP function (`jh_maintenance_` or `jurajhamara_`). No
  unprefixed globals.
- NEVER reintroduce a global `error_reporting(0)`. Suppress errors only on the
  maintenance screen itself, scoped to that single response.
- Maintenance must stay universal. Keep all three guards in sync:
  `template_redirect` (front-end, feeds, block themes), `rest_pre_dispatch`
  (REST), and `preprocess_comment` (comment/pingback spam). The comment guard
  is what stops spam bots; do not remove it.
- The maintenance response must stay SEO- and cache-safe: HTTP 503 +
  `Retry-After` + `nocache_headers()`, and purge common caching plugins when
  maintenance toggles.

## User-facing text style (readme.txt, notices, docs)

Professional but accessible to non-technical readers. Simple hyphens `-`, never
em- or en-dashes. No AI cliches, no filler, no emojis, minimal bold.

## Release process (important, easy to get wrong)

1. Bump the version in THREE places, all must match:
   - `Version:` header in `maintenance.php`
   - `Stable tag:` in `readme.txt`
   - the git tag you will push (e.g. `1.2.0`, no `v` prefix)
2. Update `Tested up to:` in `readme.txt` once verified against the latest WP.
3. Commit, then create and push the tag. The GitHub Action deploys to
   WordPress.org SVN automatically. DO NOT commit to SVN by hand.
4. Requires two GitHub repo secrets: `SVN_USERNAME`, `SVN_PASSWORD`
   (the WordPress.org account credentials).

## Known pitfall - packaging

When zipping for manual install, the plugin MUST sit inside a folder named with
the slug (`maintenance-mode-by-juraj-hamara`). Never ship `maintenance.php` at
the zip root or in a folder called `maintenance` - that slug collides with the
popular "Maintenance" plugin by WebFactory (currently v4.x) and WordPress will
offer that unrelated plugin as an "update", which would overwrite this one.

## Translations

Files are named `juraj-hamara-maintenance-{locale}.po/.mo`. Currently shipped:
sk_SK, cs_CZ, de_DE, pl_PL, plus the `.pot` template. When PHP strings change,
update the `.pot`, add the new strings to each `.po`, then recompile the `.mo`.
Because the plugin is WordPress.org-hosted, community translations can also come
from translate.wordpress.org.
