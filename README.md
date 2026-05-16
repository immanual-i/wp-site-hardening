# WP Site Hardening

A small WordPress plugin that applies security hardening defaults to reduce common attack surface and information leakage.

## What It Does

- Disables XML-RPC and pingback methods.
- Hides detailed login errors.
- Disables application passwords by default.
- Removes common WordPress metadata from the document head.
- Blocks REST API user enumeration endpoints.
- Disables comments and feeds by default.
- Adds SameSite attributes to WordPress auth cookies.
- Enforces a basic password policy with a common-password dictionary.
- Dequeues some WooCommerce assets on non-WooCommerce pages.

## File Structure

- `wp-site-hardening.php` loads the plugin modules.
- `core-hardening.php` contains the default WordPress hardening rules.
- `same-site-cookies.php` handles auth-cookie SameSite attributes.
- `frontend-assets.php` manages front-end asset dequeueing.
- `password-policy.php` enforces password rules.
- `admin.php` contains admin-area cleanup.
- `woocommerce.php` contains WooCommerce-specific asset cleanup.

## Configuration

The plugin uses secure defaults, but the more opinionated features can be disabled with filters:

```php
add_filter('wpsh_disable_application_passwords', '__return_false');
add_filter('wpsh_disable_comments', '__return_false');
add_filter('wpsh_disable_feeds', '__return_false');
```

WooCommerce stores with global mini carts, AJAX carts, or cart fragments may need WooCommerce assets and jQuery on every page:

```php
add_filter('wpsh_dequeue_woocommerce_assets', '__return_false');
add_filter('wpsh_dequeue_frontend_jquery', '__return_false');
```

The auth cookie SameSite mode defaults to `Strict`. To use `Lax` instead:

```php
add_filter('wp_auth_cookie_same_site', function () {
    return 'Lax';
});
```

## Notes

This plugin is intentionally small and opinionated. Test checkout, SSO, membership, and external login flows before using strict cookie settings on production sites.

## License

GPL-2.0-or-later.
