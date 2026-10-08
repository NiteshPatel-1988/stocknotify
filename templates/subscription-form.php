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

	<form class="stocknotify-form" novalidate="novalidate">
		<?php wp_nonce_field( 'stocknotify_subscribe', 'stocknotify_nonce' ); ?>
		<input type="hidden" class="stocknotify-product-id" name="product_id" value="<?php echo esc_attr( $stocknotify_product_id ); ?>" />
		<input type="hidden" class="stocknotify-variation-id" name="variation_id" value="0" />

		<label class="screen-reader-text" for="stocknotify-email-<?php echo esc_attr( $stocknotify_product_id ); ?>">
			<?php esc_html_e( 'Your email address', 'stocknotify' ); ?>
		</label>
		<input
			type="email"
			id="stocknotify-email-<?php echo esc_attr( $stocknotify_product_id ); ?>"
			class="stocknotify-email"
			name="email"
			placeholder="<?php echo esc_attr__( 'Enter your email', 'stocknotify' ); ?>"
			required="required"
		/>

		<button type="submit" class="stocknotify-submit button">
			<?php esc_html_e( 'Notify Me', 'stocknotify' ); ?>
		</button>

		<span class="stocknotify-message" role="status" aria-live="polite"></span>
	</form>
</div>
