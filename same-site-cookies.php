<?php

/**
 * Add SameSite attributes to WordPress auth cookies without overriding pluggable core functions.
 */

function wpsh_auth_cookie_same_site()
{
    $same_site = apply_filters('wp_auth_cookie_same_site', 'Strict');
    $same_site = ucfirst(strtolower((string) $same_site));

    if (! in_array($same_site, array('Strict', 'Lax', 'None'), true)) {
        return 'Strict';
    }

    return $same_site;
}

function wpsh_set_cookie($name, $value, $expire, $path, $domain, $secure, $httponly, $same_site)
{
    if ('None' === $same_site && ! $secure) {
        $same_site = 'Lax';
    }

    if (version_compare(PHP_VERSION, '7.3.0') >= 0) {
        setcookie(
            $name,
            $value,
            array(
                'expires'  => $expire,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httponly' => $httponly,
                'samesite' => $same_site,
            )
        );

        return;
    }

    setcookie(
        $name,
        $value,
        $expire,
        $path . '; samesite=' . strtolower($same_site),
        $domain,
        $secure,
        $httponly
    );
}

function wpsh_capture_auth_cookie($auth_cookie, $expire, $expiration, $user_id, $scheme, $token)
{
    $GLOBALS['wpsh_auth_cookie'] = array(
        'value'      => $auth_cookie,
        'expire'     => $expire,
        'expiration' => $expiration,
        'user_id'    => $user_id,
        'scheme'     => $scheme,
        'token'      => $token,
    );
}
add_action('set_auth_cookie', 'wpsh_capture_auth_cookie', 10, 6);

function wpsh_capture_logged_in_cookie($logged_in_cookie, $expire, $expiration, $user_id, $scheme, $token)
{
    $GLOBALS['wpsh_logged_in_cookie'] = array(
        'value'      => $logged_in_cookie,
        'expire'     => $expire,
        'expiration' => $expiration,
        'user_id'    => $user_id,
        'scheme'     => $scheme,
        'token'      => $token,
    );
}
add_action('set_logged_in_cookie', 'wpsh_capture_logged_in_cookie', 10, 6);

function wpsh_send_auth_cookies($send, $expire = 0, $expiration = 0, $user_id = 0, $scheme = '', $token = '')
{
    if (! $send || empty($GLOBALS['wpsh_auth_cookie']) || empty($GLOBALS['wpsh_logged_in_cookie'])) {
        return $send;
    }

    $user_id = $GLOBALS['wpsh_auth_cookie']['user_id'];
    $secure = 'secure_auth' === $GLOBALS['wpsh_auth_cookie']['scheme'];
    $secure_logged_in_cookie = $secure && 'https' === parse_url(get_option('home'), PHP_URL_SCHEME);
    $secure_logged_in_cookie = apply_filters('secure_logged_in_cookie', $secure_logged_in_cookie, $user_id, $secure);
    $same_site = wpsh_auth_cookie_same_site();
    $auth_cookie_name = $secure ? SECURE_AUTH_COOKIE : AUTH_COOKIE;

    wpsh_set_cookie(
        $auth_cookie_name,
        $GLOBALS['wpsh_auth_cookie']['value'],
        $GLOBALS['wpsh_auth_cookie']['expire'],
        PLUGINS_COOKIE_PATH,
        COOKIE_DOMAIN,
        $secure,
        true,
        $same_site
    );
    wpsh_set_cookie(
        $auth_cookie_name,
        $GLOBALS['wpsh_auth_cookie']['value'],
        $GLOBALS['wpsh_auth_cookie']['expire'],
        ADMIN_COOKIE_PATH,
        COOKIE_DOMAIN,
        $secure,
        true,
        $same_site
    );
    wpsh_set_cookie(
        LOGGED_IN_COOKIE,
        $GLOBALS['wpsh_logged_in_cookie']['value'],
        $GLOBALS['wpsh_logged_in_cookie']['expire'],
        COOKIEPATH,
        COOKIE_DOMAIN,
        $secure_logged_in_cookie,
        true,
        $same_site
    );

    if (COOKIEPATH !== SITECOOKIEPATH) {
        wpsh_set_cookie(
            LOGGED_IN_COOKIE,
            $GLOBALS['wpsh_logged_in_cookie']['value'],
            $GLOBALS['wpsh_logged_in_cookie']['expire'],
            SITECOOKIEPATH,
            COOKIE_DOMAIN,
            $secure_logged_in_cookie,
            true,
            $same_site
        );
    }

    unset($GLOBALS['wpsh_auth_cookie'], $GLOBALS['wpsh_logged_in_cookie']);

    return false;
}
add_filter('send_auth_cookies', 'wpsh_send_auth_cookies', PHP_INT_MAX, 6);

function wpsh_set_samesite_lax_for_resetpass_cookie()
{
    if (! isset($_GET['action']) || 'rp' !== $_GET['action']) {
        return;
    }

    $rp_cookie = 'wp-resetpass-' . COOKIEHASH;

    if (isset($_COOKIE[$rp_cookie])) {
        $path = wp_parse_url(wp_login_url(), PHP_URL_PATH);

        wpsh_set_cookie(
            $rp_cookie,
            $_COOKIE[$rp_cookie],
            0,
            $path ? $path : '/',
            COOKIE_DOMAIN,
            is_ssl(),
            true,
            'Lax'
        );
    }
}
add_action('init', 'wpsh_set_samesite_lax_for_resetpass_cookie');
