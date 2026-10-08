<?php
/**
 * Plugin Name:       StockNotify
 * Description:       Let customers subscribe to be notified by email when an out-of-stock WooCommerce product, or a specific variation, is back in stock.
 * Version:           1.1.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            NitsPatel
 * Author URI:        https://github.com/NiteshPatel-1988
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       stocknotify
 * Domain Path:       /languages
 *
 * WC requires at least: 6.0
 * WC tested up to:       11.0.1
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

define( 'STOCKNOTIFY_VERSION', '1.1.0' );
define( 'STOCKNOTIFY_PLUGIN_FILE', __FILE__ );
define( 'STOCKNOTIFY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'STOCKNOTIFY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify-db.php';
require STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify-activator.php';
require STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify-deactivator.php';
require STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify-ajax.php';
require STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify-public.php';
require STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify-notifier.php';
require STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify-admin.php';
require STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify-privacy.php';
require STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify.php';

register_activation_hook( __FILE__, array( 'Stocknotify_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Stocknotify_Deactivator', 'deactivate' ) );

Stocknotify::instance();
