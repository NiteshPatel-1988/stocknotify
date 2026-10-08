<?php
/**
 * AJAX handling for the subscription form.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Receives and validates subscription submissions.
 */
class Stocknotify_Ajax {

	/**
	 * Register AJAX hooks.
	 */
	public function __construct() {
		add_action( 'wp_ajax_stocknotify_subscribe', array( $this, 'subscribe' ) );
		add_action( 'wp_ajax_nopriv_stocknotify_subscribe', array( $this, 'subscribe' ) );
	}

	/**
	 * Handle a subscription request.
	 */
	public function subscribe() {
		check_ajax_referer( 'stocknotify_subscribe', 'nonce' );

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		if ( ! $product_id || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'stocknotify' ) ) );
		}

		$target  = $variation_id ? $variation_id : $product_id;
		$product = wc_get_product( $target );

		if ( ! $product instanceof WC_Product ) {
			wp_send_json_error( array( 'message' => __( 'This product could not be found.', 'stocknotify' ) ) );
		}

		if ( $variation_id && (int) $product->get_parent_id() !== $product_id ) {
			wp_send_json_error( array( 'message' => __( 'This product could not be found.', 'stocknotify' ) ) );
		}

		if ( $product->is_in_stock() ) {
			wp_send_json_error( array( 'message' => __( 'This product is already back in stock.', 'stocknotify' ) ) );
		}

		if ( Stocknotify_DB::subscription_exists( $product_id, $variation_id, $email ) ) {
			wp_send_json_success( array( 'message' => __( 'You are already subscribed to be notified about this product.', 'stocknotify' ) ) );
		}

		$saved = Stocknotify_DB::save_subscription( $product_id, $variation_id, $email );

		if ( ! $saved ) {
			wp_send_json_error( array( 'message' => __( 'Something went wrong. Please try again.', 'stocknotify' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'You will be notified by email when this product is back in stock.', 'stocknotify' ) ) );
	}
}
