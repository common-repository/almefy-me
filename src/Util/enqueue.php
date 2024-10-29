<?php
// load css, js on appropriate pages

if (!defined('ABSPATH')) {
    exit;
}


add_action('wp_enqueue_scripts', function () {

    if( ! function_exists('get_plugin_data') ){
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $version = get_plugin_data( __DIR__ . "/../../plugin.php")["Version"];

    // Almefy sdk
    wp_enqueue_script('almefy-sdk-js', plugin_dir_url(__FILE__) . '../../assets/almefy-sdk/almefy-sdk.js', [], $version, true);
    wp_enqueue_style('almefy-sdk-css', plugin_dir_url(__FILE__) .   '../../assets/almefy-sdk/almefy-sdk.css', [], $version);

    // Plugin scripts & styles
    wp_enqueue_style('almefy-helpers', plugin_dir_url(__FILE__) . "../../assets/style/helpers.css", [], $version);
    wp_enqueue_style('almefy-register', plugin_dir_url(__FILE__) . "../../assets/style/register.css", [], $version);
    wp_enqueue_style('almefy-connect-css', plugin_dir_url(__FILE__) .   '../../assets/style/connect_device.css', [], $version);
    wp_enqueue_style('almefy-login-css', plugin_dir_url(__FILE__) .   '../../assets/style/login.css', [], $version);

    wp_enqueue_script('almefy-login-js', AlmefyConstants::$BASE_URL . "assets/scripts/login.js", [], $version, true);
    wp_localize_script('almefy-login-js', 'almefy_local', [
        'mail_sent' => __('Mail has been sent to ', 'almefy-me')
    ]);

    
});

add_action('login_enqueue_scripts', function () {

    if( ! function_exists('get_plugin_data') ){
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	$version = get_plugin_data( __DIR__ . "/../../plugin.php")["Version"];

    // Almefy sdk
    wp_enqueue_script('almefy-sdk-js', plugin_dir_url(__FILE__) . '../../assets/almefy-sdk/almefy-sdk.js', [], $version, true);
    wp_enqueue_style('almefy-sdk-css', plugin_dir_url(__FILE__) .   '../../assets/almefy-sdk/almefy-sdk.css', [], $version);
    
    // Plugin scripts & styles
    wp_enqueue_style('almefy-helpers', plugin_dir_url(__FILE__) . "../../assets/style/helpers.css", [], $version);
    wp_enqueue_style('almefy-login-page-css', plugin_dir_url(__FILE__) .   '../../assets/style/login_page.css', [], $version);
    wp_enqueue_style('almefy-connect-css', plugin_dir_url(__FILE__) .   '../../assets/style/connect_device.css', [], $version);
    wp_enqueue_style('almefy-login-css', plugin_dir_url(__FILE__) .   '../../assets/style/login.css', [], $version);

    wp_enqueue_script('almefy-login-js', AlmefyConstants::$BASE_URL . "assets/scripts/login.js", [], $version, true);
    wp_localize_script('almefy-login-js', 'almefy_local', [
        'mail_sent' => __('Mail has been sent to ', 'almefy-me')
    ]);
});

add_action('admin_enqueue_scripts', function ($hook) {

    if( ! function_exists('get_plugin_data') ){
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	$version = get_plugin_data( __DIR__ . "/../../plugin.php")["Version"];

    // Almefy sdk
    // wp_enqueue_script('almefy-sdk-js', plugin_dir_url(__FILE__) . '../../assets/almefy-sdk/almefy-sdk.js', [], $version, true);
    wp_enqueue_style('almefy-sdk-css', plugin_dir_url(__FILE__) .   '../../assets/almefy-sdk/almefy-sdk.css', [], $version);
    
    // Plugin scripts & styles
    wp_enqueue_style('almefy-helpers', plugin_dir_url(__FILE__) . "../../assets/style/helpers.css", [], $version);
    wp_enqueue_style('almefy-connect-css', plugin_dir_url(__FILE__) .   '../../assets/style/connect_device.css', [], $version);

    // Admin specific scripts & styles
    // wp_enqueue_script('almefy-admin-js', plugin_dir_url(__FILE__) . "scripts/global.js", [], '0.0.1', false);
    wp_add_inline_script('almefy-admin-js', "const almefy_api = '" . rest_url('almefy/v1/') . "';", 'before');

    wp_enqueue_style('almefy-header-css', plugin_dir_url(__FILE__) .   '../../assets/style/almefy_header.css', [], $version);
    wp_enqueue_style('almefy-devices-page-css', plugin_dir_url(__FILE__) .   '../../assets/style/device_manager_page.css', [], $version);
});
