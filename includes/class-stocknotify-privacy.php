<?php
/**
 * WordPress privacy tools integration (policy text, export, erase).
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers StockNotify with Tools > Export/Erase Personal Data, since the
 * plugin stores subscriber email addresses.
 */
class Stocknotify_Privacy {

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'add_policy_content' ) );
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
	}

	/**
	 * Suggest privacy policy text on Settings > Privacy.
	 */
	public function add_policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		wp_add_privacy_policy_content(
			'StockNotify',
			wp_kses_post(
				wpautop(
					__( 'When you ask to be notified about an out-of-stock product, we store your email address, the product you are waiting for and the date of your request. We use it only to email you once when the product is back in stock. You can request export or deletion of this data at any time.', 'stocknotify' )
				)
			)
		);
	}

	/**
	 * Register the personal data exporter.
	 *
	 * @param array $exporters Registered exporters.
	 * @return array
	 */
	public function register_exporter( $exporters ) {
		$exporters['stocknotify'] = array(
			'exporter_friendly_name' => __( 'StockNotify subscriptions', 'stocknotify' ),
			'callback'               => array( $this, 'export' ),
		);

		return $exporters;
	}

	/**
	 * Register the personal data eraser.
	 *
	 * @param array $erasers Registered erasers.
	 * @return array
	 */
	public function register_eraser( $erasers ) {
		$erasers['stocknotify'] = array(
			'eraser_friendly_name' => __( 'StockNotify subscriptions', 'stocknotify' ),
			'callback'             => array( $this, 'erase' ),
		);

		return $erasers;
	}

	/**
	 * Export callback.
	 *
	 * @param string $email_address Email being exported.
	 * @return array
	 */
	public function export( $email_address ) {
		$items = array();

		foreach ( Stocknotify_DB::get_subscriptions_by_email( sanitize_email( $email_address ) ) as $index => $row ) {
			$target = get_the_title( $row->variation_id ? $row->variation_id : $row->product_id );

			$data = array(
				array(
					'name'  => __( 'Product', 'stocknotify' ),
					'value' => $target,
				),
				array(
					'name'  => __( 'Status', 'stocknotify' ),
					'value' => $row->status,
				),
				array(
					'name'  => __( 'Subscribed on', 'stocknotify' ),
					'value' => $row->created_at,
				),
			);

			if ( $row->notified_at ) {
				$data[] = array(
					'name'  => __( 'Notified on', 'stocknotify' ),
					'value' => $row->notified_at,
				);
			}

			$items[] = array(
				'group_id'    => 'stocknotify',
				'group_label' => __( 'Back-in-stock subscriptions', 'stocknotify' ),
				'item_id'     => 'stocknotify-' . $index,
				'data'        => $data,
			);
		}

		return array(
			'data' => $items,
			'done' => true,
		);
	}

	/**
	 * Eraser callback.
	 *
	 * @param string $email_address Email being erased.
	 * @return array
	 */
	public function erase( $email_address ) {
		$removed = Stocknotify_DB::delete_by_email( sanitize_email( $email_address ) );

		return array(
			'items_removed'  => $removed > 0,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}
}
