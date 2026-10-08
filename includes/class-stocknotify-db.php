<?php
/**
 * Data access for back-in-stock subscriptions.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Thin wrapper around the subscribers table.
 */
class Stocknotify_DB {

	/**
	 * Table name without the $wpdb prefix.
	 *
	 * @var string
	 */
	const TABLE = 'stocknotify_subscribers';

	/**
	 * Get the fully-prefixed table name.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . self::TABLE;
	}

	/**
	 * Check whether a pending subscription already exists.
	 *
	 * @param int    $product_id   Product (or parent product) ID.
	 * @param int    $variation_id Variation ID, or 0 for a simple product.
	 * @param string $email        Subscriber email address.
	 * @return bool
	 */
	public static function subscription_exists( $product_id, $variation_id, $email ) {
		global $wpdb;

		$table = self::table_name();

		// Table name is derived from $wpdb->prefix + a hardcoded constant, not user input.
		// A dedicated plugin table has no core caching API; results change on every subscribe request, so caching adds complexity without benefit here.
		$id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE product_id = %d AND variation_id = %d AND email = %s AND status = 'pending'", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$product_id,
				$variation_id,
				$email
			)
		);

		return (bool) $id;
	}

	/**
	 * Save a pending subscription for a product/variation/email combo.
	 *
	 * The table has a unique key on (product_id, variation_id, email), so a
	 * subscriber who was already notified once (or unsubscribed) for this
	 * product still has a row here. Re-subscribing must reset that existing
	 * row to `pending` rather than attempt a second insert, which would
	 * violate the unique key.
	 *
	 * @param int    $product_id   Product (or parent product) ID.
	 * @param int    $variation_id Variation ID, or 0 for a simple product.
	 * @param string $email        Subscriber email address.
	 * @return bool
	 */
	public static function save_subscription( $product_id, $variation_id, $email ) {
		global $wpdb;

		$table = self::table_name();

		// Table name is derived from $wpdb->prefix + a hardcoded constant, not user input.
		// A dedicated plugin table has no core caching API; results change on every subscribe request, so caching adds complexity without benefit here.
		$existing_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE product_id = %d AND variation_id = %d AND email = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$product_id,
				$variation_id,
				$email
			)
		);

		if ( $existing_id ) {
			$result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$table,
				array(
					'status'      => 'pending',
					'created_at'  => current_time( 'mysql', true ),
					'notified_at' => null,
				),
				array( 'id' => $existing_id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);

			return false !== $result;
		}

		$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$table,
			array(
				'product_id'   => $product_id,
				'variation_id' => $variation_id,
				'email'        => $email,
				'status'       => 'pending',
				'created_at'   => current_time( 'mysql', true ),
			),
			array( '%d', '%d', '%s', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Get pending (not yet notified) subscribers for a product or variation.
	 *
	 * @param int $product_id   Product (or parent product) ID.
	 * @param int $variation_id Variation ID, or 0 for a simple product.
	 * @return object[] Rows with at least `id` and `email` columns.
	 */
	public static function get_pending_subscriptions( $product_id, $variation_id ) {
		global $wpdb;

		$table = self::table_name();

		// Table name is derived from $wpdb->prefix + a hardcoded constant, not user input.
		// A dedicated plugin table has no core caching API; results change on every stock update, so caching adds complexity without benefit here.
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT id, email FROM {$table} WHERE product_id = %d AND variation_id = %d AND status = 'pending'", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$product_id,
				$variation_id
			)
		);
	}

	/**
	 * Count pending subscribers for a product, including all its variations.
	 *
	 * @param int $product_id Product (or parent product) ID.
	 * @return int
	 */
	public static function count_pending_for_product( $product_id ) {
		global $wpdb;

		$table = self::table_name();

		// Table name is derived from $wpdb->prefix + a hardcoded constant, not user input.
		// A dedicated plugin table has no core caching API; the count changes on every subscribe/notify, so caching adds complexity without benefit here.
		$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE product_id = %d AND status = 'pending'", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$product_id
			)
		);

		return (int) $count;
	}

	/**
	 * Get every subscription stored for an email address (privacy export).
	 *
	 * @param string $email Subscriber email address.
	 * @return object[]
	 */
	public static function get_subscriptions_by_email( $email ) {
		global $wpdb;

		$table = self::table_name();

		// Table name is derived from $wpdb->prefix + a hardcoded constant, not user input.
		// Privacy exports are rare, one-off requests; caching adds nothing here.
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT product_id, variation_id, status, created_at, notified_at FROM {$table} WHERE email = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$email
			)
		);
	}

	/**
	 * Delete every subscription stored for an email address (privacy erasure).
	 *
	 * @param string $email Subscriber email address.
	 * @return int Number of rows removed.
	 */
	public static function delete_by_email( $email ) {
		global $wpdb;

		// A dedicated plugin table has no core caching API; this write doesn't need one.
		$result = $wpdb->delete( self::table_name(), array( 'email' => $email ), array( '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $result ? (int) $result : 0;
	}

	/**
	 * Mark a subscription as notified so it is not emailed again.
	 *
	 * @param int $id Subscription row id.
	 * @return bool
	 */
	public static function mark_notified( $id ) {
		global $wpdb;

		// A dedicated plugin table has no core caching API; this write doesn't need one.
		$result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			self::table_name(),
			array(
				'status'      => 'notified',
				'notified_at' => current_time( 'mysql', true ),
			),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return false !== $result;
	}
}
