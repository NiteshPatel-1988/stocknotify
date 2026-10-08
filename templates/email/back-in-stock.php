<?php
/**
 * Back-in-stock notification email (HTML).
 *
 * Themes can override this by copying it to
 * yourtheme/stocknotify/email/back-in-stock.php.
 *
 * @var WC_Product|null $product       The product or variation that is back in stock, or null if unavailable (e.g. during an admin preview on a store with no products).
 * @var string     $email_heading      Email heading text.
 * @var string     $additional_content Admin-configured extra text.
 * @var bool       $sent_to_admin      Always false for this email.
 * @var bool       $plain_text         Always false here (this is the HTML template).
 * @var WC_Email   $email              The email object.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- this fires WooCommerce's own existing hook, not a new one defined here.
?>

<p>
	<?php esc_html_e( 'Good news!', 'stocknotify' ); ?>
	&#127881;
</p>

<?php if ( $product instanceof WC_Product ) : ?>

	<p>
		<?php
		printf(
			/* translators: %s: product name, wrapped in <strong> tags */
			esc_html__( 'The product you were waiting for, %s, is back in stock.', 'stocknotify' ),
			'<strong>' . esc_html( $product->get_name() ) . '</strong>'
		);
		?>
	</p>

	<p style="text-align: center; margin: 24px 0;">
		<a
			class="button"
			href="<?php echo esc_url( $product->get_permalink() ); ?>"
			style="background-color:#7f54b3;color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;"
		>
			<?php esc_html_e( 'Shop Now', 'stocknotify' ); ?>
		</a>
	</p>

<?php else : ?>

	<p><?php esc_html_e( 'The product you were waiting for is back in stock.', 'stocknotify' ); ?></p>

<?php endif; ?>

<?php
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- this fires WooCommerce's own existing hook, not a new one defined here.
