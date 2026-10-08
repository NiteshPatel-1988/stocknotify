<?php
/**
 * Runs on plugin activation.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Creates the database table used to store subscriptions.
 */
class Stocknotify_Activator {

	/**
	 * Create/upgrade the subscribers table.
	 */
	public static function activate() {
		global $wpdb;

		$table           = Stocknotify_DB::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			product_id BIGINT UNSIGNED NOT NULL,
			variation_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			email VARCHAR(100) NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			created_at DATETIME NOT NULL,
			notified_at DATETIME NULL,
			PRIMARY KEY  (id),
			KEY product_id (product_id),
			KEY variation_id (variation_id),
			KEY email (email),
			UNIQUE KEY product_variation_email (product_id, variation_id, email)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'stocknotify_db_version', STOCKNOTIFY_VERSION );
	}
}
