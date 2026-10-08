<?php
/**
 * Runs on plugin deactivation.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clears any pending scheduled notifications so they don't fire after
 * the plugin has been switched off. Subscription data itself is kept
 * (only uninstall.php removes it).
 */
class Stocknotify_Deactivator {

	/**
	 * Clear scheduled cron events.
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( Stocknotify_Notifier::CRON_HOOK );
	}
}
