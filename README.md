# StockNotify

Let customers subscribe for an email alert when an out-of-stock WooCommerce product or variation is back in stock.

![License](https://img.shields.io/badge/license-GPLv2%2B-blue)
![WordPress](https://img.shields.io/badge/WordPress-6.2%2B-21759b)
![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0%2B-7f54b3)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)

## How it works

1. A product (or a specific variation) goes out of stock.
2. A customer enters their email address on the product page.
3. The product comes back in stock.
4. The customer receives a "back in stock" email.

## Features

- "Notify Me" form on the single product page, shown only when the product or selected variation is out of stock.
- Simple and variable products. On variable products the form sits just above the Add to cart button and appears for the selected out-of-stock variation (for example Red Shirt) only.
- AJAX submission with no page reload.
- Duplicate-subscription protection and per-IP rate limiting.
- Notification email is a standard WooCommerce email, editable under **WooCommerce > Settings > Emails** (subject, heading, extra text, HTML or plain text).
- Emails are sent through WP-Cron shortly after the stock change, so saving a product or processing an order is never slowed down.
- "Waitlist" column on the Products list showing how many customers are waiting for each product (including its variations).
- WordPress privacy tools support: personal data export and erase, plus suggested privacy policy text.
- Subscriptions are stored in a dedicated table, `{prefix}stocknotify_subscribers`.

## Requirements

| Requirement | Minimum |
| --- | --- |
| WordPress | 6.2 |
| WooCommerce | 6.0 (active) |
| PHP | 7.4 |

## Installation

1. Download or clone this repository into `wp-content/plugins/stocknotify`.
2. Activate **StockNotify** under **Plugins**.
3. Make sure WooCommerce is installed and active.
4. Open any out-of-stock product to see the form.

## Customizing

Copy a template into your theme to override it:

| Plugin template | Theme override |
| --- | --- |
| `templates/subscription-form.php` | `yourtheme/stocknotify/subscription-form.php` |
| `templates/email/back-in-stock.php` | `yourtheme/stocknotify/email/back-in-stock.php` |
| `templates/email/plain/back-in-stock.php` | `yourtheme/stocknotify/email/plain/back-in-stock.php` |

## Project structure

```
stocknotify/
├── stocknotify.php            Plugin header and bootstrap
├── uninstall.php              Removes the table and options
├── includes/
│   ├── class-stocknotify.php            Wires up the feature classes
│   ├── class-stocknotify-db.php         Subscriptions table access
│   ├── class-stocknotify-ajax.php       Subscribe request handling
│   ├── class-stocknotify-public.php     Front-end assets and form placement
│   ├── class-stocknotify-notifier.php   Stock change detection and cron sending
│   ├── class-stocknotify-email.php      WooCommerce email class
│   ├── class-stocknotify-admin.php      Waitlist column on the Products list
│   └── class-stocknotify-privacy.php    Privacy export and erase
├── templates/                 Front-end form and email templates
└── frontend/                  CSS and JavaScript
```

## Security

- Subscribe requests are nonce-checked, sanitized, rate limited and only accepted for published products.
- All database access uses `$wpdb->prepare()` or `$wpdb` helper methods.
- All output is escaped.
- The admin column is only shown to users with the `edit_products` capability.

## Known limitations

- If **WooCommerce > Settings > Products > Inventory > Hide out of stock items from the catalog** is enabled, WooCommerce treats out-of-stock variations as unavailable, so the form does not appear for them.
- Block-based product templates may not fire the classic WooCommerce hooks this plugin uses for form placement.
- The notification email has no unsubscribe link. It is sent once per subscription.

## Changelog

See [readme.txt](readme.txt) for the full changelog.

## License

GPL v2 or later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

## Author

[NitsPatel](https://github.com/NiteshPatel-1988)
