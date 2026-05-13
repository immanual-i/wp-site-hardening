<?php

/*
	Plugin Name: WP Site Hardening
    Plugin URI: https://github.com/immanual-i/wp-site-hardening
    Description: A collection of security hardening measures for WordPress to reduce attack surface and improve security.
	Version: 1.0.0
	Author: Immanual
	Author URI: 
	License: GPLv2 or later
*/

if (! defined('ABSPATH')) {
	exit;
}


require_once plugin_dir_path(__FILE__) . 'core-hardening.php';
require_once plugin_dir_path(__FILE__) . 'same-site-cookies.php';
require_once plugin_dir_path(__FILE__) . 'admin.php';
require_once plugin_dir_path(__FILE__) . 'frontend-assets.php';
require_once plugin_dir_path(__FILE__) . 'password-policy.php';

add_action('plugins_loaded', function () {
	if (class_exists('WooCommerce')) {
		require_once plugin_dir_path(__FILE__) . 'woocommerce.php';
	}
});
