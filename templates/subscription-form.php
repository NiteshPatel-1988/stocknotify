<?php
/**
 * Front-end back-in-stock subscription form.
 *
 * @var array $args {
 *     @type int  $product_id Product (or parent product) ID.
 *     @type bool $hidden     Whether the form should start hidden (variable products).
 * }
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

$stocknotify_product_id = isset( $args['product_id'] ) ? absint( $args['product_id'] ) : 0;
$stocknotify_hidden     = ! empty( $args['hidden'] );

if ( ! $stocknotify_product_id ) {
	return;
}

$stocknotify_wrap_classes = 'stocknotify-form-wrap';

if ( $stocknotify_hidden ) {
	$stocknotify_wrap_classes .= ' stocknotify-hidden';
}
?>
<div class="<?php echo esc_attr( $stocknotify_wrap_classes ); ?>" data-product-id="<?php echo esc_attr( $stocknotify_product_id ); ?>">
	<p class="stocknotify-heading">
		<span aria-hidden="true">&#128276;</span>
		<?php esc_html_e( 'Notify me when this product is back in stock', 'stocknotify' ); ?>
	</p>

	<?php
	/*
	 * A div, not a form: on variable products this sits inside WooCommerce's
	 * cart form and forms cannot be nested. Inputs have no name attribute so
	 * they are never submitted with the add-to-cart request.
	 */
	?>
	<div class="stocknotify-form">
		<input type="hidden" class="stocknotify-nonce" value="<?php echo esc_attr( wp_create_nonce( 'stocknotify_subscribe' ) ); ?>" />
		<input type="hidden" class="stocknotify-product-id" value="<?php echo esc_attr( $stocknotify_product_id ); ?>" />
		<input type="hidden" class="stocknotify-variation-id" value="0" />

		<label class="screen-reader-text" for="stocknotify-email-<?php echo esc_attr( $stocknotify_product_id ); ?>">
			<?php esc_html_e( 'Your email address', 'stocknotify' ); ?>
		</label>
		<input
			type="email"
			id="stocknotify-email-<?php echo esc_attr( $stocknotify_product_id ); ?>"
			class="stocknotify-email"
			placeholder="<?php echo esc_attr__( 'Enter your email', 'stocknotify' ); ?>"
		/>

		<button type="button" class="stocknotify-submit button">
			<?php esc_html_e( 'Notify Me', 'stocknotify' ); ?>
		</button>

		<span class="stocknotify-message" role="status" aria-live="polite"></span>
	</div>
</div>
