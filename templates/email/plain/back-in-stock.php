<?php
/**
 * Back-in-stock notification email (plain text).
 *
 * Themes can override this by copying it to
 * yourtheme/stocknotify/email/plain/back-in-stock.php.
 *
 * @var WC_Product|null $product       The product or variation that is back in stock, or null if unavailable (e.g. during an admin preview on a store with no products).
 * @var string     $email_heading      Email heading text.
 * @var string     $additional_content Admin-configured extra text.
 *
 * @package StockNotify
 */

defined( 'ABSPATH' ) || exit;

echo esc_html( wp_strip_all_tags( $email_heading ) ) . "\n\n";

echo esc_html__( 'Good news!', 'stocknotify' ) . "\n\n";

if ( $product instanceof WC_Product ) {
	printf(
		/* translators: %s: product name */
		esc_html__( 'The product you were waiting for, %s, is back in stock.', 'stocknotify' ) . "\n\n",
		esc_html( $product->get_name() )
	);

	echo esc_html__( 'Shop now:', 'stocknotify' ) . ' ' . esc_url( $product->get_permalink() ) . "\n\n";
} else {
	echo esc_html__( 'The product you were waiting for is back in stock.', 'stocknotify' ) . "\n\n";
}

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) ) . "\n\n";
}

echo "----------------------------------------\n";
