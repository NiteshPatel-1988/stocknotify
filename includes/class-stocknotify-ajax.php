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
	 * Max subscribe requests per IP inside the rate-limit window.
	 *
	 * @var int
	 */
	const RATE_LIMIT = 5;

	/**
	 * Rate-limit window, in seconds.
	 *
	 * @var int
	 */
	const RATE_WINDOW = 600;

	/**
	 * Maximum stored email length (matches the table column).
	 *
	 * @var int
	 */
	const MAX_EMAIL_LENGTH = 100;

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

		if ( $this->is_rate_limited() ) {
			wp_send_json_error( array( 'message' => __( 'Too many requests. Please try again in a few minutes.', 'stocknotify' ) ), 429 );
		}

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		if ( ! $product_id || ! is_email( $email ) || strlen( $email ) > self::MAX_EMAIL_LENGTH ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'stocknotify' ) ) );
		}

		// Only published products can be subscribed to.
		if ( 'publish' !== get_post_status( $product_id ) ) {
			wp_send_json_error( array( 'message' => __( 'This product could not be found.', 'stocknotify' ) ) );
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

	/**
	 * Throttle requests per IP address to stop the form being used to
	 * enroll other people's addresses in bulk.
	 *
	 * @return bool True when the caller has exceeded the limit.
	 */
	private function is_rate_limited() {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'stocknotify_rl_' . md5( $ip );

		$count = (int) get_transient( $key );

		if ( $count >= self::RATE_LIMIT ) {
			return true;
		}

		set_transient( $key, $count + 1, self::RATE_WINDOW );

		return false;
	}
}
