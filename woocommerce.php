<?php

/**
 * Check whether the current request is for a WooCommerce page.
 */
function wpsh_is_woocommerce_page()
{
    if (function_exists('is_woocommerce') && is_woocommerce()) {
        return true;
    }

    $wc_pages = array(
        'woocommerce_shop_page_id',
        'woocommerce_terms_page_id',
        'woocommerce_cart_page_id',
        'woocommerce_checkout_page_id',
        'woocommerce_pay_page_id',
        'woocommerce_thanks_page_id',
        'woocommerce_myaccount_page_id',
        'woocommerce_edit_address_page_id',
        'woocommerce_view_order_page_id',
        'woocommerce_change_password_page_id',
        'woocommerce_logout_page_id',
        'woocommerce_lost_password_page_id',
    );

    foreach ($wc_pages as $page_id) {
        if (get_the_ID() == get_option($page_id, 0)) {
            return true;
        }
    }

    return false;
}

/**
 * Dequeue WooCommerce scripts and styles away from WooCommerce pages.
 */
function wpsh_dequeue_woocommerce_assets_on_non_woo_pages()
{
    if (! function_exists('is_woocommerce')) {
        return;
    }

    // Only run away from WooCommerce pages.
    if (! wpsh_is_woocommerce_page()) {

        // Remove the WooCommerce meta generator tag.
        remove_action('wp_head', array($GLOBALS['woocommerce'], 'generator'));

        // Styles to dequeue.
        $styles = array(
            'woocommerce-layout',
            'woocommerce-smallscreen',
            'woocommerce-general',
            'wc-block-style',
            'woocommerce-inline',
            'select2',
        );

        foreach ($styles as $style) {
            wp_dequeue_style($style);
        }

        // Scripts to dequeue.
        $scripts = array(
            'flexslider',
            'js-cookie',
            'jquery-blockui',
            'jquery-cookie',      // deprecated
            'jquery-payment',
            'photoswipe',
            'photoswipe-ui-default',
            'prettyPhoto',        // deprecated
            'prettyPhoto-init',   // deprecated
            'select2',
            'selectWoo',
            'wc-address-i18n',
            'wc-add-payment-method',
            'wc-cart',
            'wc-cart-fragments',
            'wc-checkout',
            'wc-country-select',
            'wc-credit-card-form',
            'wc-add-to-cart',
            'wc-add-to-cart-variation',
            'wc-geolocation',
            'wc-lost-password',
            'wc-password-strength-meter',
            'wc-single-product',
            'woocommerce',
            'zoom',
        );

        foreach ($scripts as $script) {
            wp_dequeue_script($script);
        }
    }
}
add_action('wp_enqueue_scripts', 'wpsh_dequeue_woocommerce_assets_on_non_woo_pages', 99);
