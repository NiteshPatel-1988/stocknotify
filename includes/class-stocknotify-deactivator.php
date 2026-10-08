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
		// Events carry arguments, so wp_clear_scheduled_hook() without them would match nothing.
		wp_unschedule_hook( Stocknotify_Notifier::CRON_HOOK );
	}
}
