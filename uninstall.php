<?php
/**
 * Fired when the plugin is uninstalled (not just deactivated).
 *
 * @package StockNotify
 */

// If uninstall.php is not called by WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$stocknotify_table = $wpdb->prefix . 'stocknotify_subscribers';

// %i (identifier placeholder, WP 6.2+) safely escapes the table name; this
// is a one-time schema cleanup on uninstall, not a query needing caching.
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $stocknotify_table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

delete_option( 'stocknotify_db_version' );

wp_unschedule_hook( 'stocknotify_send_notifications' );
