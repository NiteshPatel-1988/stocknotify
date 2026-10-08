<?php
/**
 * Admin-facing additions: waitlist column on the Products list.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shows how many customers are waiting for a back-in-stock alert.
 */
class Stocknotify_Admin {

	/**
	 * Column key.
	 *
	 * @var string
	 */
	const COLUMN = 'stocknotify_waitlist';

	/**
	 * Set up hooks.
	 */
	public function __construct() {
		add_filter( 'manage_edit-product_columns', array( $this, 'add_column' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Give the Waitlist column a fixed width so the header does not wrap.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( 'edit.php' !== $hook_suffix || ! current_user_can( 'edit_products' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'edit-product' !== $screen->id ) {
			return;
		}

		wp_register_style( 'stocknotify-admin', false, array(), STOCKNOTIFY_VERSION );
		wp_enqueue_style( 'stocknotify-admin' );
		wp_add_inline_style(
			'stocknotify-admin',
			'.wp-list-table th.column-' . self::COLUMN . ',.wp-list-table td.column-' . self::COLUMN . '{width:90px;white-space:nowrap;}'
		);
	}

	/**
	 * Add the Waitlist column after the stock column when present.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_column( $columns ) {
		if ( ! current_user_can( 'edit_products' ) ) {
			return $columns;
		}

		$label   = __( 'Waitlist', 'stocknotify' );
		$updated = array();
		$added   = false;

		foreach ( $columns as $key => $value ) {
			$updated[ $key ] = $value;
			if ( 'is_in_stock' === $key ) {
				$updated[ self::COLUMN ] = $label;
				$added                   = true;
			}
		}

		if ( ! $added ) {
			$updated[ self::COLUMN ] = $label;
		}

		return $updated;
	}

	/**
	 * Output the pending subscriber count for a row.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Product ID.
	 */
	public function render_column( $column, $post_id ) {
		if ( self::COLUMN !== $column || ! current_user_can( 'edit_products' ) ) {
			return;
		}

		$count = Stocknotify_DB::count_pending_for_product( absint( $post_id ) );

		if ( 0 === $count ) {
			echo '<span aria-hidden="true">&ndash;</span>';
			return;
		}

		echo '<strong>' . esc_html( number_format_i18n( $count ) ) . '</strong>';
	}
}
