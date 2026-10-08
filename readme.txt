=== StockNotify ===
Contributors: nitspatel
Tags: woocommerce, back in stock, stock notification, out of stock, email notification
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers subscribe for an email alert when an out-of-stock WooCommerce product or variation is back in stock.

== Description ==

StockNotify adds a "Notify me when this product is back in stock" form to WooCommerce product pages whenever a product, or a selected variation, is out of stock.

**Core flow**

1. A product (or variation) goes out of stock.
2. A customer enters their email address on the product page.
3. The product comes back in stock.
4. The customer receives an email notification.
5. Store owners can track who subscribed and who was notified. *(planned for a future release)*

**Features:**

* A back-in-stock subscription form on the single product page.
* Support for simple products.
* Support for variable products, with the form appearing automatically for the selected out-of-stock variation.
* AJAX submission with no page reload.
* Duplicate-subscription protection and per-IP rate limiting.
* A "Waitlist" column on the WooCommerce Products list showing how many customers are waiting.
* WordPress privacy tools support (personal data export and erase).
* Automatic "back in stock" email sent to every pending subscriber as soon as a product or variation returns to stock.
* The notification email is a standard WooCommerce email, editable under WooCommerce > Settings > Emails (subject, heading, extra text, HTML/plain format) and themeable by copying its template into `yourtheme/stocknotify/`.

= Requirements =

* WooCommerce must be installed and active. StockNotify will show an admin notice if it is not.

== Installation ==

1. Upload the `stocknotify` folder to `/wp-content/plugins/`, or install the zip via Plugins > Add New > Upload Plugin.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Make sure WooCommerce is installed and active.
4. Visit any out-of-stock product to see the subscription form.

== Frequently Asked Questions ==

= Does this work with variable products? =

Yes. When a shopper selects a variation that is out of stock, the subscription form appears automatically for that specific variation. If a different, in-stock variation is selected, the form is hidden again.

= Does this plugin send the "back in stock" email yet? =

Yes. As soon as WooCommerce marks a product or variation as back in stock, every subscriber who is still waiting for it is emailed automatically. Sending happens a short time after the stock change (via WP-Cron) so that saving a product or processing an order is never slowed down by the outgoing emails. Each subscriber is only notified once per subscription.

= Can I customize the notification email? =

Yes. It's a normal WooCommerce email, so it appears under WooCommerce > Settings > Emails, where you can change the subject, heading, additional text, and switch between HTML and plain text. To change the layout itself, copy `templates/email/back-in-stock.php` (and, for plain text, `templates/email/plain/back-in-stock.php`) from the plugin into `yourtheme/stocknotify/email/`.

= Where are subscriptions stored? =

In a dedicated database table (`{prefix}stocknotify_subscribers`) created on activation, separate from WooCommerce's own tables. An admin screen to browse subscribers is planned for a future release.

== Screenshots ==

1. Subscription form on an out-of-stock product page.
2. "Waitlist" column on the Products list showing how many customers are waiting for each product.

== Changelog ==

= 1.1.0 =
* New: "Waitlist" column on the WooCommerce Products list showing the number of customers waiting for each product (including its variations). Visible only to users who can edit products.
* New: WordPress privacy tools support. Subscriber emails are included in Tools > Export Personal Data and removed by Tools > Erase Personal Data, and suggested privacy policy text is added.
* New: The notify box now appears directly above the Add to cart button on variable products.
* Security: Per-IP rate limiting on the subscribe request to prevent bulk enrollment of other people's email addresses.
* Security: Subscriptions are only accepted for published products, and email addresses longer than the stored limit are rejected.
* Fix: A subscriber is now only marked as notified after the email was actually sent, so failed sends are not lost.
* Fix: Deactivating or uninstalling the plugin now removes its scheduled notification events.
* Fix: The notify box is now a plain container instead of a nested form, so it no longer produces invalid HTML inside WooCommerce's add to cart form or sends extra fields with Add to cart.
* Fix: The notify box hides and its message clears correctly when the selected variation changes.
* Improvement: The database table is created or upgraded automatically after a plugin update.
* Improvement: Added the "Requires Plugins: woocommerce" header.
* New: Translation template (languages/stocknotify.pot) and translation loading, so the plugin can be translated.

= 1.0.0 =
* Initial release: back-in-stock subscription form for simple, variable, and individual variation products.
* Automatic email notification, sent to subscribers when a product or variation comes back in stock.

== Upgrade Notice ==

= 1.1.0 =
Adds a Waitlist column, privacy export/erase support, rate limiting and several fixes. Safe to update; no settings change.

= 1.0.0 =
Initial release.
