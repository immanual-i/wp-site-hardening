<?php

function wpsh_dequeue_frontend_jquery()
{
    return (bool) apply_filters('wpsh_dequeue_frontend_jquery', true);
}

/**
 * Dequeue bundled jQuery on the front end, except on WooCommerce flows that need it.
 */
add_action('wp_enqueue_scripts', function () {
    if (is_admin() || ! wpsh_dequeue_frontend_jquery()) {
        return;
    }

    wp_dequeue_script('jquery');
    wp_dequeue_script('jquery-migrate');

    if (class_exists('WooCommerce')) {
        if (function_exists('is_product') && (is_product() || is_cart() || is_checkout())) {
            wp_enqueue_script('jquery');
        }
    }
}, 100);
