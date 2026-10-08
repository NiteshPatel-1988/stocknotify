<?php
/**
 * Watches for out-of-stock -> in-stock transitions and queues notifications.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hooks into WooCommerce's stock status hooks and, once a product or
 * variation becomes available again, schedules a WP-Cron event that emails
 * every pending subscriber. Sending is deferred (rather than done inline on
 * the hook) so that saving a product, processing an order, or restoring
 * stock never has to wait on a batch of outgoing emails.
 */
class Stocknotify_Notifier {

	/**
	 * Cron hook used to actually send the batch of emails.
	 *
	 * @var string
	 */
	const CRON_HOOK = 'stocknotify_send_notifications';

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email' ) );

		add_action( 'woocommerce_product_set_stock_status', array( $this, 'maybe_queue_notifications' ), 10, 3 );
		add_action( 'woocommerce_variation_set_stock_status', array( $this, 'maybe_queue_notifications' ), 10, 3 );

		add_action( self::CRON_HOOK, array( $this, 'send_notifications' ), 10, 2 );
	}

	/**
	 * Add our custom email to WooCommerce's registered emails.
	 *
	 * @param WC_Email[] $email_classes Existing registered emails.
	 * @return WC_Email[]
	 */
	public function register_email( $email_classes ) {
		require_once STOCKNOTIFY_PLUGIN_DIR . 'includes/class-stocknotify-email.php';

		$email_classes['stocknotify_back_in_stock'] = new Stocknotify_Email();

		return $email_classes;
	}

	/**
	 * Fired whenever WooCommerce updates a product's or variation's stock
	 * status. Schedules a single deferred send when the new status is
	 * "in stock" and there are subscribers waiting.
	 *
	 * @param int        $object_id Product or variation ID.
	 * @param string     $status    New stock status.
	 * @param WC_Product $product   The product or variation object.
	 */
	public function maybe_queue_notifications( $object_id, $status, $product ) {
		if ( 'instock' !== $status || ! $product instanceof WC_Product ) {
			return;
		}

		list( $product_id, $variation_id ) = $this->resolve_ids( $product );

		if ( wp_next_scheduled( self::CRON_HOOK, array( $product_id, $variation_id ) ) ) {
			return;
		}

		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, self::CRON_HOOK, array( $product_id, $variation_id ) );
	}

	/**
	 * Cron callback: email every pending subscriber for a product/variation.
	 *
	 * @param int $product_id   Product (or parent product) ID.
	 * @param int $variation_id Variation ID, or 0 for a simple product.
	 */
	public function send_notifications( $product_id, $variation_id ) {
		$product = wc_get_product( $variation_id ? $variation_id : $product_id );

		if ( ! $product instanceof WC_Product || ! $product->is_in_stock() ) {
			return;
		}

		$subscribers = Stocknotify_DB::get_pending_subscriptions( $product_id, $variation_id );

		if ( empty( $subscribers ) ) {
			return;
		}

		$emails = WC()->mailer()->get_emails();

		if ( empty( $emails['stocknotify_back_in_stock'] ) ) {
			return;
		}

		$email = $emails['stocknotify_back_in_stock'];

		foreach ( $subscribers as $subscriber ) {
			// Only mark as notified when the mail was actually handed off, so failures can be retried on the next restock.
			if ( $email->trigger( $subscriber->email, $product ) ) {
				Stocknotify_DB::mark_notified( $subscriber->id );
			}
		}
	}

	/**
	 * Work out the (product_id, variation_id) pair matching how
	 * subscriptions are stored for a given product or variation object.
	 *
	 * @param WC_Product $product Product or variation object.
	 * @return int[] [ product_id, variation_id ]
	 */
	private function resolve_ids( $product ) {
		if ( $product->is_type( 'variation' ) ) {
			return array( $product->get_parent_id(), $product->get_id() );
		}

		return array( $product->get_id(), 0 );
	}
}
