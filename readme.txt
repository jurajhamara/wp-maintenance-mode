=== Maintenance mode by Juraj Hamara ===
Contributors: jurajj
Donate link: https://www.paypal.com/donate/?hosted_button_id=3JNS55YNAWDAY
Tags: maintenance, maintenance mode, maintenance page, 503, under construction
Requires at least: 4.8
Tested up to: 7.0
Requires PHP: 5.6.20
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight maintenance mode plugin. Activate it to put your whole site under maintenance with a proper 503 response. No settings needed.

== Description ==

A lightweight Maintenance Mode plugin for WordPress.

Activate the plugin to switch your site into maintenance mode. Visitors see a simple maintenance notice, while logged in users who can manage content keep full access to the site. A red notice appears in the admin bar to remind you that maintenance is active, and clicking it ends maintenance again.

The plugin is intentionally minimal: there are no settings pages, no builders and no external assets. It just works after install and activation.

**What it covers**

* Classic and block (FSE) themes
* The REST API
* Feeds and sitemaps
* Comment and pingback submissions, so spam bots cannot post while the site is under maintenance

**SEO friendly**

The maintenance screen returns a proper HTTP 503 Service Unavailable status together with a Retry-After header. This tells search engines the state is temporary, so they do not index the maintenance page.

**Caching aware**

A no-cache header is sent with the maintenance screen, and when maintenance is switched on or off the plugin clears the cache of the common caching plugins (WP Rocket, LiteSpeed, W3 Total Cache, WP Super Cache, WP Fastest Cache). Server level caches such as Nginx FastCGI, Varnish or Cloudflare must still be cleared separately.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install it directly from the Plugins screen in your WordPress admin.
2. Activate the plugin through the Plugins screen. Maintenance mode is now on.
3. To end maintenance, click the red notice in the admin bar, which deactivates the plugin.

== Frequently Asked Questions ==

= Who can still see the site while maintenance is on? =

Logged in users who can manage content (editors and administrators) see the site normally. Everyone else, including logged out visitors and bots, sees the maintenance notice.

= How do I turn maintenance off? =

Click the red "maintenance" notice in the admin bar, or deactivate the plugin from the Plugins screen.

= Will search engines index my maintenance page? =

No. The maintenance screen returns a 503 status with a Retry-After header, which search engines treat as a temporary, non indexable state.

= I use a caching plugin. Do visitors still see the maintenance page? =

The plugin clears the cache of the common caching plugins when maintenance is switched on or off. If you use a server level cache (Nginx FastCGI, Varnish, Cloudflare), clear it separately after enabling maintenance.

== Changelog ==

= 1.2.0 =
* Maintenance now also covers block (FSE) themes, the REST API, feeds and comment submissions, so bots can no longer post comments while the site is under maintenance.
* The maintenance screen returns a proper HTTP 503 status with a Retry-After header, which is friendlier for SEO.
* Added a no-cache header and automatic cache clearing for popular caching plugins when maintenance is switched on or off.
* You can now end maintenance by clicking the red notice in the admin bar.
* Removed the site wide error reporting change; errors are now suppressed only on the maintenance screen itself.
* Code clean-up and properly prefixed functions.
* Tested up to WordPress 7.0.

= 1.0.2 =
* Bugfixes

= 1.0.1 =
* Plugin was localized
* Added Slovak translation
* Disabled error reporting when plugin activated

= 1.0.0 =
* Initial release.
