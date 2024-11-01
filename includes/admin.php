<?php

namespace yoycol\Admin;

const MENU_TITLE_TOP = 'Yoycol';
const PAGE_TITLE_DASHBOARD = 'Dashboard';
const MENU_TITLE_DASHBOARD = 'Dashboard';
const MENU_SLUG_DASHBOARD = 'yoycol-dashboard';
const CAPABILITY = 'manage_options';
const YOYCOL_NAMESPACE = 'yoycol/v1';
const API_HOST = 'https://www.yoycol.com';
// const API_HOST = 'https://test.novel3d.com';
// const API_HOST = 'http://192.168.3.26:8080';

function init()
{
    add_action('admin_enqueue_scripts', 'yoycol\Admin\load_admin_script');
    add_action('admin_menu', 'yoycol\Admin\register_menu');
    add_action('rest_api_init', 'yoycol\Admin\register_api');
}

function register_menu()
{
    add_menu_page(
        'yoycol',
        MENU_TITLE_TOP,
        CAPABILITY,
        MENU_SLUG_DASHBOARD,
        '\yoycol\Admin\admin_render',
        "data:image/svg+xml;base64,PHN2ZyAgdmlld0JveD0iMCAwIDEwMjQgMTAyNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHAtaWQ9IjczMCI+PHBhdGggZD0iTTY3My42IDM2My4yYzAgMS42IDAgMS42IDEuNiAxLjYgNDMuMi00LjggODYuNC0xLjYgMTMxLjIgMTEuMiA0OCAxNC40IDg5LjYgMzYuOCAxMjMuMiA2Ny4yLTgwLTE0LjQtMTYwIDMuMi0yMjcuMiA0My4yIDEuNiAyNy4yIDAgNDkuNi00LjggNjguOC04IDQwLTI1LjYgNzItNDEuNiAxMTItNi40IDE2LTE0LjQgMzMuNi0xOS4yIDU0LjQtNi40IDIyLjQtOCA2NS42LTYuNCA5Ny42IDAgOCAxLjYgMTQuNCAxLjYgMjAuOCA4IDUyLjggMjAuOCA4OS42IDU5LjIgMTIxLjYtNzAuNCAzLjItMTEwLjQtMjUuNi0xMzcuNi00OS42LTQuOC00LjgtMTEuMi05LjYtMTcuNi0xNi0xNi0xNy42LTMyLTQwLTQ0LjgtNjIuNC0xMi44LTIyLjQtMjQtNDMuMi0yOC44LTU5LjItNi40LTIwLjgtMTEuMi01Ni0xNC40LTg2LjQtMS42LTkuNi0xLjYtMTcuNi0xLjYtMjUuNnYtMTEuMmMtMS42LTI1LjYgMC01MS4yLTMuMi03Ni44LTI3LjItMjExLjItMjA4LTM2NC44LTQxNC40LTM2My4yaC0zLjJjLTEuNiAwLTIyLjQtMS42LTI0LTEuNkMzMC40IDE4MC44IDk2IDEyMy4yIDEyOCAxMDguOGM2LjQtMy4yIDE0LjQtNi40IDIyLjQtOCAyNC04IDUxLjItMTIuOCA3NS4yLTE0LjQgMjUuNi0zLjIgNjguOC0xLjYgODQuOC0xLjYgMTA1LjYgOCAxNTMuNiA1Ny42IDE3Mi44IDY3LjIgMzAuNCAxNC40IDU3LjYgMzMuNiA4My4yIDU2IDgxLjYtODkuNiAyMDkuNi0xMzEuMiAzMzQuNC05NC40IDQ4IDE0LjQgODkuNiAzNi44IDEyMy4yIDY3LjItMTQyLjQtMjQtMjg2LjQgNTEuMi0zNTAuNCAxODIuNHoiIGZpbGw9IiNFRTc2MUMiIHAtaWQ9IjczMSI+PC9wYXRoPjwvc3ZnPg==",
        56
    );
}

function load_admin_script()
{
    $plugin_path = dirname(__DIR__);
    $static_path = $plugin_path . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . "static";
    $js_path = $static_path . DIRECTORY_SEPARATOR . 'js';
    $css_path = $static_path . DIRECTORY_SEPARATOR . 'css';

    $js_url_path = plugins_url('build/static/js/', dirname(__FILE__));
    foreach (scandir($js_path) as $key => $path) {
        if (endsWith($path, '.js')) {
            wp_enqueue_script('yoycol_js_' . $key, $js_url_path . $path, [], null, true);
        }
    }

    $css_url_path = plugins_url('build/static/css/', dirname(__FILE__));
    foreach (scandir($css_path) as $key => $path) {
        if (endsWith($path, '.css')) {
            $name = 'yoycol_css_' . $key;
            wp_register_style($name, $css_url_path . $path, false, null);
            wp_enqueue_style($name);
        }
    }
}

function domain_need_update()
{
    $domain = get_option('yoycol_domain');
    $currentDomain = get_site_url();
    if (!$domain) {
        add_option('yoycol_domain', $currentDomain);
        return false;
    }
    return $domain != $currentDomain;
}

function get_store_data()
{
    $salt = get_option('yoycol_salt');
    if (!$salt) {
        $salt = (string)wp_generate_uuid4();
        add_option('yoycol_salt', $salt);
    }
    $accessKey = get_option('yoycol_access_key');
    $accessToken = get_option('yoycol_access_token');
    $storeId = get_option('yoycol_store_id');
    return array(
        'website'   => get_site_url(),
        'updateDomain' => domain_need_update(),
        'version'   => WC()->version,
        'name'      => get_bloginfo('title', 'display'),
        'apiHost'  => API_HOST,
        'salt'      => $salt,
        'accessKey' => $accessKey ? $accessKey :  '',
        'accessToken' => $accessToken ? $accessToken : '',
        'storeId'   => $storeId ? $storeId : '',
        // 'woocommerceConnect' => '/api/dashboard/woocommerce/plugin_connect'
        'woocommerceConnect' => '/api/store/common/woocommerce/connect'
    );
}

function admin_render()
{
    echo '<script> window.yoycolStoreData = ' . json_encode(get_store_data()) . '</script>';
    echo '<div id=\'yoycol-root\'></div>';
    // echo '<iframe width="100%" height="800" src="http://vcing.net:3000?yoycol-data=' . urlencode(json_encode(get_store_data())) . '" />';
}

function register_api()
{
    register_rest_route(YOYCOL_NAMESPACE, '/set_access_key', array(
        'methods' => \WP_REST_Server::EDITABLE,
        'callback' => "yoycol\Admin\set_access_key",
        'show_in_index' => false,
        'permission_callback' => '__return_true',
        'args' => array(
            'accessKey' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'Yoycol access key',
            ),
            'token' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'validation token',
            ),
            'storeId' => array(
                'required' => true,
                'type' => 'integer',
                'description' => 'Store Identifier'
            ),
        ),
    ));
    register_rest_route(YOYCOL_NAMESPACE, '/clear_all', array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => "yoycol\Admin\clear_all",
        'show_in_index' => false,
        'permission_callback' => '__return_true'
    ));

    register_rest_route(YOYCOL_NAMESPACE, '/set_access_token', array(
        'methods' => \WP_REST_Server::EDITABLE,
        'callback' => "yoycol\Admin\set_access_token",
        'show_in_index' => false,
        'permission_callback' => '__return_true',
        'args' => array(
            'verification' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'Yoycol verification',
            ),
            'token' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'Yoycol access token',
            ),
            'storeId' => array(
                'required' => true,
                'type' => 'integer',
                'description' => 'Store Identifier'
            ),
        ),
    ));
    register_rest_route(YOYCOL_NAMESPACE, '/clear_all_token', array(
        'methods' => \WP_REST_Server::READABLE,
        'callback' => "yoycol\Admin\clear_all_token",
        'show_in_index' => false,
        'permission_callback' => '__return_true',
    ));
    // register_rest_route(YOYCOL_NAMESPACE, '/test', array(
    //         'methods' => \WP_REST_Server::READABLE,
    //         'callback' => 'yoycol\Admin\test',
    //         'show_in_index' => false
    //         'permission_callback' => '__return_true',
    // ));
}

function set_access_key($request)
{
    $token = $request['token'];
    $salt = get_option('yoycol_salt');
    if ($token != md5($request['accessKey'] . $request['storeId'] . $salt)) {
        return new \WP_Error('invalid token', 'invalid token');
    }
    add_option('yoycol_access_key', $request['accessKey']);
    add_option('yoycol_store_id', $request['storeId']);
    return rest_ensure_response('ok');
}

function clear_all($request)
{
    $accessKey = get_option('yoycol_access_key');
    if ($accessKey != $request['token']) {
        return new \WP_Error('invalid token', 'invalid token');
    }
    delete_option('yoycol_salt');
    delete_option('yoycol_access_key');
    delete_option('yoycol_access_token');
    delete_option('yoycol_store_id');
    return rest_ensure_response('ok');
}

function set_access_token($request)
{
    $verification = $request['verification'];
    $salt = get_option('yoycol_salt');
    if ($verification != md5($request['token'] . $request['storeId'] . $salt)) {
        return new \WP_Error('invalid verification', 'invalid verification');
    }
    add_option('yoycol_access_token', $request['token']);
    add_option('yoycol_store_id', $request['storeId']);
    return rest_ensure_response('ok');
}

function clear_all_token($request)
{
    $accessKey = get_option('yoycol_access_token');
    if ($accessKey != $request['token']) {
        return new \WP_Error('invalid token', 'invalid token');
    }
    delete_option('yoycol_salt');
    delete_option('yoycol_access_key');
    delete_option('yoycol_access_token');
    delete_option('yoycol_store_id');
    return rest_ensure_response('ok');
}

// function test($request)
// {
//     return rest_ensure_response(md5("+"));
// }

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if (!$length) {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}
