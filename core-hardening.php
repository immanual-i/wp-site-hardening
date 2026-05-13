<?php

function wpsh_disable_application_passwords()
{
    return (bool) apply_filters('wpsh_disable_application_passwords', true);
}

function wpsh_disable_comments()
{
    return (bool) apply_filters('wpsh_disable_comments', true);
}

function wpsh_disable_feeds()
{
    return (bool) apply_filters('wpsh_disable_feeds', true);
}

/**
 * Disable XML-RPC to prevent external login attacks and pingbacks.
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Remove emoji detection scripts/styles from the frontend to reduce bloat and fingerprinting.
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/**
 * Remove oEmbed discovery links and JS to reduce unnecessary HTTP requests and information leaks.
 */
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

/**
 * Remove RSD (Really Simple Discovery) and WLW (Windows Live Writer) links from <head>.
 * These are legacy features not used on most sites.
 */
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

/**
 * Remove shortlink output in the header to prevent unnecessary output and potential info leakage.
 */
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('template_redirect', 'wp_shortlink_header', 11);

/**
 * Hide login error messages to prevent attackers from knowing whether a username exists.
 */
add_filter('login_errors', '__return_null');

/**
 * Disable application passwords unless explicitly needed.
 */
add_filter('wp_is_application_passwords_available', function ($available) {
    if (wpsh_disable_application_passwords()) {
        return false;
    }

    return $available;
});

/**
 * Redirect attachment pages to home to prevent useless single attachment pages from being indexed.
 */
add_action('template_redirect', function () {
    if (is_attachment()) {
        wp_redirect(home_url(), 301);
        exit;
    }
});

/**
 * Remove WordPress version generator from <head> to reduce fingerprinting.
 */
remove_action('wp_head', 'wp_generator');

/**
 * Disable pingback methods in XML-RPC to prevent DDoS pingback attacks.
 */
add_filter('xmlrpc_methods', function ($methods) {
    unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
    return $methods;
});

/**
 * Disable self-pings when linking to your own site.
 */
add_action('pre_ping', function (&$links) {
    $home = home_url();
    foreach ($links as $key => $link) {
        if (strpos($link, $home) === 0) {
            unset($links[$key]);
        }
    }
});

/**
 * Disable comments in the admin and remove dashboard/comment-related UI.
 */
add_action('admin_init', function () {
    if (! wpsh_disable_comments()) {
        return;
    }

    global $pagenow;

    // Redirect users away from the comments screen.
    if ($pagenow === 'edit-comments.php') {
        wp_safe_redirect(admin_url());
        exit;
    }

    // Remove the recent comments dashboard widget.
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

    // Disable comment and trackback support for all registered post types.
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

/**
 * Close comments and ping functionality on the front-end.
 */
add_filter('comments_open', function ($open) {
    if (wpsh_disable_comments()) {
        return false;
    }

    return $open;
}, 20, 2);
add_filter('pings_open', function ($open) {
    if (wpsh_disable_comments()) {
        return false;
    }

    return $open;
}, 20, 2);

/**
 * Remove all existing comments from output.
 */
add_filter('comments_array', function ($comments) {
    if (wpsh_disable_comments()) {
        return array();
    }

    return $comments;
}, 10, 2);

/**
 * Remove comments page from the admin menu.
 */
add_action('admin_menu', function () {
    if (! wpsh_disable_comments()) {
        return;
    }

    remove_menu_page('edit-comments.php');
});

/**
 * Remove comments menu from the admin bar for logged-in users.
 */
add_action('init', function () {
    if (! wpsh_disable_comments()) {
        return;
    }

    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});

/**
 * Block REST API endpoints that expose user info.
 * Prevents /wp-json/wp/v2/users enumeration.
 */
add_filter('rest_endpoints', function ($endpoints) {
    if (isset($endpoints['/wp/v2/users'])) {
        unset($endpoints['/wp/v2/users']);
    }
    if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});

/**
 * Disable feed URLs when feed hardening is enabled.
 */
add_action('do_feed', function () {
    if (! wpsh_disable_feeds()) {
        return;
    }

    wp_die(__('No feed available.', 'wp-site-hardening'));
}, 1);
add_action('do_feed_rdf', function () {
    if (! wpsh_disable_feeds()) {
        return;
    }

    wp_die(__('No feed available.', 'wp-site-hardening'));
}, 1);
add_action('do_feed_rss', function () {
    if (! wpsh_disable_feeds()) {
        return;
    }

    wp_die(__('No feed available.', 'wp-site-hardening'));
}, 1);
add_action('do_feed_rss2', function () {
    if (! wpsh_disable_feeds()) {
        return;
    }

    wp_die(__('No feed available.', 'wp-site-hardening'));
}, 1);
add_action('do_feed_atom', function () {
    if (! wpsh_disable_feeds()) {
        return;
    }

    wp_die(__('No feed available.', 'wp-site-hardening'));
}, 1);
add_action('do_feed_rss2_comments', function () {
    if (! wpsh_disable_feeds()) {
        return;
    }

    wp_die(__('No feed available.', 'wp-site-hardening'));
}, 1);
add_action('do_feed_atom_comments', function () {
    if (! wpsh_disable_feeds()) {
        return;
    }

    wp_die(__('No feed available.', 'wp-site-hardening'));
}, 1);

/**
 * Remove RSS feed links from <head> when feeds are disabled.
 */
if (wpsh_disable_feeds()) {
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
}


/**
 * Redirect author query requests to reduce username enumeration.
 */
add_action('init', function () {
    if (isset($_GET['author']) && !is_admin()) {
        wp_safe_redirect(remove_query_arg('author'));
        exit();
    }
});
