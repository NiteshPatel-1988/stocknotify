<?php
/**
 * Main plugin bootstrap.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wires up the plugin: WooCommerce HPOS compatibility and the feature
 * classes (only once WooCommerce is confirmed active).
 */
class Stocknotify {

	/**
	 * Singleton instance.
	 *
	 * @var Stocknotify|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Stocknotify
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set up hooks.
	 */
	private function __construct() {
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
	}

	/**
	 * Declare compatibility with WooCommerce's custom order tables (HPOS).
	 * This plugin never reads or writes order data, so it is compatible by default.
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				STOCKNOTIFY_PLUGIN_FILE,
				true
			);
		}
	}

	/**
	 * Bootstrap the feature classes once WooCommerce has loaded.
	 */
	public function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}

		new Stocknotify_Ajax();
		new Stocknotify_Public();
		new Stocknotify_Notifier();
	}

	/**
	 * Warn admins that WooCommerce is required.
	 */
	public function woocommerce_missing_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p>
				<?php esc_html_e( 'StockNotify requires WooCommerce to be installed and active.', 'stocknotify' ); ?>
			</p>
		</div>
		<?php
	}
}
