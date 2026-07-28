# Maintenance mode by Juraj Hamara

A lightweight WordPress maintenance-mode plugin. Activate it to put the whole
site under maintenance with a proper HTTP 503 response. No settings needed.

Hosted on WordPress.org:
<https://wordpress.org/plugins/maintenance-mode-by-juraj-hamara/>

## How it works

Activating the plugin turns maintenance on. There is no settings option for it.

- Logged-in users who can `delete_published_posts` (editors and admins) keep
  full access to the site.
- Everyone else, including logged-out visitors and bots, sees the maintenance
  notice.
- A red notice appears in the admin bar. Clicking it deactivates the plugin,
  which ends maintenance (admins only).

## What it covers

- Classic and block (FSE) themes
- The REST API
- Feeds and sitemaps
- Comment and pingback submissions, so spam bots cannot post while the site is
  under maintenance

The maintenance screen returns a 503 Service Unavailable status with a
`Retry-After` header, so search engines treat the state as temporary and do not
index it. A no-cache header is sent, and the cache of common caching plugins
(WP Rocket, LiteSpeed, W3 Total Cache, WP Super Cache, WP Fastest Cache) is
cleared when maintenance is switched on or off. Server-level caches (Nginx
FastCGI, Varnish, Cloudflare) must be cleared separately.

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`, or install it from the
   Plugins screen in your WordPress admin.
2. Activate it through the Plugins screen. Maintenance mode is now on.
3. To end maintenance, click the red notice in the admin bar, which deactivates
   the plugin.

When zipping for a manual install, the plugin must sit inside a folder named
`maintenance-mode-by-juraj-hamara`. Do not ship it in a folder called
`maintenance`, because that slug collides with an unrelated plugin.

## Repository layout

The plugin files live at the repository root so the deploy workflow can push
them straight to the WordPress.org SVN trunk.

- `maintenance.php` - the whole plugin (single file)
- `readme.txt` - the file WordPress.org reads
- `README.md` - this file (GitHub only, excluded from the WordPress.org deploy)
- `languages/` - the `.pot` template plus a `.po` and compiled `.mo` per locale
- `.github/workflows/deploy.yml` - deploy to WordPress.org on tag push
- `.distignore` - files kept out of the WordPress.org deploy

## Releasing

1. Bump the version in three places, all must match: the `Version:` header in
   `maintenance.php`, `Stable tag:` in `readme.txt`, and the git tag (for
   example `1.2.0`, with no `v` prefix).
2. Update `Tested up to:` in `readme.txt` once verified against the latest
   WordPress release.
3. Commit, then create and push the tag. The GitHub Action deploys to the
   WordPress.org SVN automatically. Do not commit to SVN by hand.

The workflow needs two repository secrets: `SVN_USERNAME` and `SVN_PASSWORD`
(the WordPress.org account credentials).

## License

GPLv2 or later. See [LICENSE](LICENSE).
