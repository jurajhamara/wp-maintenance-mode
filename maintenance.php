<?php
/*
Plugin Name: Maintenance mode by Juraj Hamara
Plugin URI: https://jurajhamara.sk/plugin/maintenance-mode/
Description: A lightweight Maintenance Mode plugin
Version: 1.2.0
Author: Juraj Hamara
Author URI: https://jurajhamara.sk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: juraj-hamara-maintenance
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Model: an active plugin means maintenance is ON (matches the original
 * "just install and activate it" behaviour). Clicking the red admin-bar
 * link deactivates the plugin, which ends maintenance. To start maintenance
 * again, activate the plugin from the Plugins screen.
 */

/* -------------------------------------------------------------------------
 * Activation / deactivation - flush caches so no stale page is served
 * when maintenance starts or ends.
 * ---------------------------------------------------------------------- */

function jh_maintenance_activate() {
	jh_maintenance_flush_caches();
}
register_activation_hook( __FILE__, 'jh_maintenance_activate' );

function jh_maintenance_deactivate() {
	jh_maintenance_flush_caches();
}
register_deactivation_hook( __FILE__, 'jh_maintenance_deactivate' );

/* -------------------------------------------------------------------------
 * Translations
 * ---------------------------------------------------------------------- */

/**
 * Load translations. Prefixed to avoid name collisions. Optional for
 * WordPress.org-hosted plugins since WP 4.6, kept for manual .mo files.
 */
function jh_maintenance_load_translation() {
	load_plugin_textdomain(
		'juraj-hamara-maintenance',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'jh_maintenance_load_translation' );

/* -------------------------------------------------------------------------
 * Lockout logic
 * ---------------------------------------------------------------------- */

/**
 * Should the current visitor be locked out of the site?
 *
 * Because the plugin being active already means maintenance is on, this only
 * decides who is exempt: content managers, background processes, and the
 * login flow so admins can still sign in.
 */
function jh_maintenance_should_lock() {
	if ( current_user_can( 'delete_published_posts' ) ) {
		return false;
	}
	if ( wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return false;
	}
	if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
		return false;
	}
	return true;
}

/**
 * Render the maintenance screen and stop the request with a 503 status.
 */
function jh_maintenance_render() {
	// Keep the maintenance page clean without disabling error reporting
	// site-wide: silence on-screen errors for this single response only.
	@ini_set( 'display_errors', '0' );

	// Do not let this temporary page be cached anywhere.
	nocache_headers();
	if ( ! headers_sent() ) {
		header( 'Retry-After: 3600' );
	}

	$title   = __( 'The site is currently under maintenance.', 'juraj-hamara-maintenance' );
	$message = __( 'Please come back later.', 'juraj-hamara-maintenance' );

	$content = '<h1>' . esc_html( $title ) . '</h1><p>' . esc_html( $message ) . '</p>';

	wp_die(
		$content,
		$title,
		array(
			'response'  => 503,
			'back_link' => false,
		)
	);
}

/* -------------------------------------------------------------------------
 * Guards - the three entry points that matter
 * ---------------------------------------------------------------------- */

/**
 * 1) Normal front-end views, feeds and sitemaps, on classic and block themes.
 *    Priority 0 so we win over template-level renderers.
 */
function jh_maintenance_guard_frontend() {
	if ( jh_maintenance_should_lock() ) {
		jh_maintenance_render();
	}
}
add_action( 'template_redirect', 'jh_maintenance_guard_frontend', 0 );

/**
 * 2) REST API requests (headless calls, REST-based comment posting, etc.).
 */
function jh_maintenance_guard_rest( $result ) {
	if ( jh_maintenance_should_lock() ) {
		return new WP_Error(
			'jh_maintenance_mode',
			__( 'The site is currently under maintenance.', 'juraj-hamara-maintenance' ),
			array( 'status' => 503 )
		);
	}
	return $result;
}
add_filter( 'rest_pre_dispatch', 'jh_maintenance_guard_rest', 10, 1 );

/**
 * 3) Comment / pingback submissions. Everything funnels through
 *    wp_new_comment() -> preprocess_comment, so this single guard blocks
 *    wp-comments-post.php, REST comments and XML-RPC pingback spam alike.
 */
function jh_maintenance_guard_comments( $commentdata ) {
	if ( jh_maintenance_should_lock() ) {
		jh_maintenance_render();
	}
	return $commentdata;
}
add_filter( 'preprocess_comment', 'jh_maintenance_guard_comments' );

/* -------------------------------------------------------------------------
 * Admin-bar notice + one-click deactivate
 * ---------------------------------------------------------------------- */

/**
 * Style the admin-bar notice in red.
 */
function jh_maintenance_toolbar_style() {
	wp_register_style( 'jh-maintenance-style', false );
	wp_enqueue_style( 'jh-maintenance-style' );
	wp_add_inline_style(
		'jh-maintenance-style',
		'.jh-maintenance-on, #wpadminbar:not(.mobile) .ab-top-menu>li.jh-maintenance-on:hover>.ab-item, #wpadminbar:not(.mobile) .ab-top-menu>li.jh-maintenance-on>.ab-item:focus { background: #eb3a34 !important; color: #f0f0f1 !important; }'
	);
}
add_action( 'admin_enqueue_scripts', 'jh_maintenance_toolbar_style', 100 );
add_action( 'wp_enqueue_scripts', 'jh_maintenance_toolbar_style', 100 );

/**
 * Add the red admin-bar notice. Clicking it deactivates the plugin (admins only).
 */
function jh_maintenance_toolbar_node( $wp_admin_bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$off_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=jh_maintenance_off' ),
		'jh_maintenance_off'
	);

	$wp_admin_bar->add_node(
		array(
			'id'    => 'jh-maintenance',
			'title' => __( 'Maintenance on - click to turn off', 'juraj-hamara-maintenance' ),
			'href'  => $off_url,
			'meta'  => array( 'class' => 'jh-maintenance-on' ),
		)
	);
}
add_action( 'admin_bar_menu', 'jh_maintenance_toolbar_node', 9999 );

/**
 * Handle the click: verify nonce + capability, then deactivate the plugin.
 * The deactivation hook flushes caches. Send the admin back where they were.
 */
function jh_maintenance_handle_off() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			esc_html__( 'You are not allowed to do this.', 'juraj-hamara-maintenance' ),
			'',
			array( 'response' => 403 )
		);
	}
	check_admin_referer( 'jh_maintenance_off' );

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	deactivate_plugins( plugin_basename( __FILE__ ) );

	$back = wp_get_referer();
	wp_safe_redirect( $back ? $back : admin_url( 'plugins.php' ) );
	exit;
}
add_action( 'admin_post_jh_maintenance_off', 'jh_maintenance_handle_off' );

/* -------------------------------------------------------------------------
 * Cache handling
 * ---------------------------------------------------------------------- */

/**
 * Best-effort cache purge across the common caching plugins, so visitors are
 * not served a stale page when maintenance is switched on or off. Server-level
 * caches (Nginx FastCGI, Varnish, Cloudflare) must be purged separately.
 */
function jh_maintenance_flush_caches() {
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush(); // core object cache
	}
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache(); // WP Super Cache
	}
	if ( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all(); // W3 Total Cache
	}
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain(); // WP Rocket
	}
	if ( function_exists( 'wpfc_clear_all_cache' ) ) {
		wpfc_clear_all_cache(); // WP Fastest Cache
	}
	do_action( 'litespeed_purge_all' ); // LiteSpeed Cache (no-op if absent)
}
