<?php
/**
 * Back-in-stock notification email.
 *
 * Registered with WooCommerce via the `woocommerce_email_classes` filter,
 * so it appears (and is configurable) under WooCommerce > Settings > Emails.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

/**
 * Sent to a single subscriber once the product/variation they subscribed
 * to is back in stock.
 */
class Stocknotify_Email extends WC_Email {

	/**
	 * Theme override lookup path, relative to the theme root.
	 * Declared explicitly because some WooCommerce versions don't declare
	 * this property on WC_Email, which would otherwise trigger a
	 * "creation of dynamic property" deprecation notice on PHP 8.2+.
	 *
	 * @var string
	 */
	public $template_path;

	/**
	 * Set up the email defaults.
	 */
	public function __construct() {
		$this->id             = 'stocknotify_back_in_stock';
		$this->customer_email = true;
		$this->title          = __( 'Back in Stock Notification', 'stocknotify' );
		$this->description    = __( 'Sent to a customer who asked to be notified once a product (or variation) they wanted is back in stock.', 'stocknotify' );
		$this->heading        = __( 'Good news, it&#8217;s back in stock!', 'stocknotify' );
		$this->subject        = __( '[{site_title}] {product_name} is back in stock!', 'stocknotify' );

		$this->template_html  = 'email/back-in-stock.php';
		$this->template_plain = 'email/plain/back-in-stock.php';
		$this->template_base  = STOCKNOTIFY_PLUGIN_DIR . 'templates/';
		$this->template_path  = 'stocknotify/';

		parent::__construct();
	}

	/**
	 * Default text for the "additional content" setting.
	 *
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Thanks for your patience, we hope you enjoy it!', 'stocknotify' );
	}

	/**
	 * Send the notification to one subscriber.
	 *
	 * @param string     $recipient_email Subscriber email address.
	 * @param WC_Product $product         The product (or variation) that is back in stock.
	 * @return bool Whether the email was sent.
	 */
	public function trigger( $recipient_email, $product ) {
		$this->object    = $product;
		$this->recipient = $recipient_email;

		if ( ! $this->get_recipient() || ! $this->is_enabled() ) {
			return false;
		}

		$this->setup_locale();

		$sent = $this->send(
			$this->get_recipient(),
			$this->get_subject(),
			$this->get_content(),
			$this->get_headers(),
			$this->get_attachments()
		);

		$this->restore_locale();

		return $sent;
	}

	/**
	 * WooCommerce's "Preview" screen (WooCommerce > Settings > Emails) sets
	 * `$this->object` to a generic order-based preview object for every
	 * registered email, since it has no way to know this email expects a
	 * product. Fall back to a real sample product in that case so the
	 * preview renders instead of fatal-ing on a missing method.
	 *
	 * @return WC_Product|null
	 */
	protected function get_display_product() {
		if ( $this->object instanceof WC_Product ) {
			return $this->object;
		}

		$products = wc_get_products(
			array(
				'limit'   => 1,
				'orderby' => 'date',
				'order'   => 'DESC',
				'status'  => 'publish',
			)
		);

		return ! empty( $products ) ? $products[0] : null;
	}

	/**
	 * Keep the {product_name}/{site_title} placeholders in sync with
	 * whatever product is actually being displayed (real subscription
	 * product, or the preview fallback).
	 *
	 * @param WC_Product|null $product Product to source the placeholder from.
	 */
	protected function sync_placeholders( $product ) {
		$this->placeholders['{product_name}'] = $product instanceof WC_Product ? $product->get_name() : __( 'Sample Product', 'stocknotify' );
		$this->placeholders['{site_title}']   = $this->get_blogname();
	}

	/**
	 * Get the email subject, with placeholders resolved.
	 *
	 * @return string
	 */
	public function get_subject() {
		$this->sync_placeholders( $this->get_display_product() );

		return parent::get_subject();
	}

	/**
	 * Get the email heading, with placeholders resolved.
	 *
	 * @return string
	 */
	public function get_heading() {
		$this->sync_placeholders( $this->get_display_product() );

		return parent::get_heading();
	}

	/**
	 * Build the HTML body.
	 *
	 * @return string
	 */
	public function get_content_html() {
		$product = $this->get_display_product();

		$this->sync_placeholders( $product );

		return wc_get_template_html(
			$this->template_html,
			array(
				'product'             => $product,
				'email_heading'       => $this->get_heading(),
				'additional_content'  => $this->get_additional_content(),
				'sent_to_admin'       => false,
				'plain_text'          => false,
				'email'               => $this,
			),
			$this->template_path,
			$this->template_base
		);
	}

	/**
	 * Build the plain-text body.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		$product = $this->get_display_product();

		$this->sync_placeholders( $product );

		return wc_get_template_html(
			$this->template_plain,
			array(
				'product'             => $product,
				'email_heading'       => $this->get_heading(),
				'additional_content'  => $this->get_additional_content(),
				'sent_to_admin'       => false,
				'plain_text'          => true,
				'email'               => $this,
			),
			$this->template_path,
			$this->template_base
		);
	}

	/**
	 * Admin-configurable settings shown under WooCommerce > Settings > Emails.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'             => array(
				'title'   => __( 'Enable/Disable', 'stocknotify' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'stocknotify' ),
				'default' => 'yes',
			),
			'subject'             => array(
				'title'       => __( 'Subject', 'stocknotify' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of available placeholder tags */
				'description' => sprintf( __( 'Available placeholders: %s', 'stocknotify' ), '{site_title}, {product_name}' ),
				'placeholder' => $this->subject,
				'default'     => '',
			),
			'heading'             => array(
				'title'       => __( 'Email heading', 'stocknotify' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of available placeholder tags */
				'description' => sprintf( __( 'Available placeholders: %s', 'stocknotify' ), '{site_title}, {product_name}' ),
				'placeholder' => $this->heading,
				'default'     => '',
			),
			'additional_content'  => array(
				'title'       => __( 'Additional content', 'stocknotify' ),
				'description' => __( 'Text to appear below the main email content.', 'stocknotify' ),
				'css'         => 'width:400px; height: 75px;',
				'placeholder' => __( 'N/A', 'stocknotify' ),
				'type'        => 'textarea',
				'default'     => $this->get_default_additional_content(),
				'desc_tip'    => true,
			),
			'email_type'          => array(
				'title'       => __( 'Email type', 'stocknotify' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'stocknotify' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}
}
