<?php
/**
 * Front-end rendering: assets and the subscription form.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues front-end assets and renders the subscription form on the
 * single product page for simple, variable, and individual variations.
 */
class Stocknotify_Public {

	/**
	 * Register front-end hooks.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_form' ), 35 );
	}

	/**
	 * Enqueue CSS/JS on single product pages only.
	 */
	public function enqueue_assets() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'stocknotify-public',
			STOCKNOTIFY_PLUGIN_URL . 'frontend/css/stocknotify-public.css',
			array(),
			STOCKNOTIFY_VERSION
		);

		wp_enqueue_script(
			'stocknotify-public',
			STOCKNOTIFY_PLUGIN_URL . 'frontend/js/stocknotify-public.js',
			array( 'jquery' ),
			STOCKNOTIFY_VERSION,
			true
		);

		wp_localize_script(
			'stocknotify-public',
			'stocknotifyPublic',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'emailRequired' => __( 'Please enter your email address.', 'stocknotify' ),
					'submitting'    => __( 'Submitting…', 'stocknotify' ),
					'genericError'  => __( 'Something went wrong. Please try again.', 'stocknotify' ),
				),
			)
		);
	}

	/**
	 * Render the subscription form for the current product.
	 *
	 * For simple products the form only appears when the product is out
	 * of stock. For variable products the form is rendered hidden and is
	 * shown/hidden by JavaScript as the shopper selects a variation.
	 */
	public function render_form() {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$is_variable = $product->is_type( 'variable' );

		if ( ! $is_variable && $product->is_in_stock() ) {
			return;
		}

		$this->render_template(
			'subscription-form.php',
			array(
				'product_id' => $product->get_id(),
				'hidden'     => $is_variable,
			)
		);
	}

	/**
	 * Load a template from templates/, falling back to a theme override
	 * at stocknotify/{template} if one exists.
	 *
	 * @param string $template File name inside templates/.
	 * @param array  $args     Variables made available to the template as $args.
	 */
	private function render_template( $template, $args = array() ) {
		$theme_override = locate_template( 'stocknotify/' . $template );
		$path           = $theme_override ? $theme_override : STOCKNOTIFY_PLUGIN_DIR . 'templates/' . $template;

		if ( ! file_exists( $path ) ) {
			return;
		}

		include $path;
	}
}
