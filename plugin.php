<?php

/**
 * Plugin Name: Almefy
 * Plugin URI: https://almefy.com/
 * Description: Strong and password-free 2FA-Login, just by scanning a QR-Code with our Almefy App.
 * Author: Almefy GmbH
 * Author URI: https://almefy.com
 * Requires at least: 5.0
 * Version: 0.16.5
 * Licence: GPL v2 or later
 * Licence URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: almefy-me
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';


class AlmefyPlugin
{
    public function __construct()
    {
        // Load constants
        require_once(__DIR__ . '/src/Util/AlmefyConstants.php');
        new AlmefyConstants();
        
        // Almefy
        require_once(__DIR__ . '/src/Almefy/AlmefyManager.php');
        new AlmefyManager();

        // Load
        require_once(__DIR__ . '/src/Util/enqueue.php');

        // Mail
        require_once(__DIR__ . '/src/Mail/AlmefyMailer.php');
        new AlmefyMailer();

        // Notices
        require_once(__DIR__ . '/src/Notices/AlmefyNotices.php');
        // new AlmefyNotices();

        // Pages
        require_once(__DIR__ . '/src/Pages/Login/AlmefyLoginPage.php');
        new AlmefyLoginPage();

        require_once(__DIR__ . '/src/Pages/Settings/AlmefySettingsPage.php');
        new AlmefySettingsPage();

        require_once(__DIR__ . '/src/Pages/Profile/AlmefyDeviceManagerPage.php');
        new AlmefyDeviceManagerPage();

        require_once(__DIR__ . '/src/Pages/Profile/AlmefyProfilePage.php');
        new AlmefyProfilePage();

        require_once(__DIR__ . '/src/Pages/Users/AlmefyUsers.php');
        new AlmefyUsers();

        // require_once(__DIR__ . '/src/Pages/Wizard/AlmefyWizard.php');
        // new AlmefyWizard();

        // Rest
        require_once(__DIR__ . '/src/Rest/AlmefyAuthControllerEndpoint.php');
        new AlmefyAuthController();

        require_once(__DIR__ . '/src/Rest/AlmefyGetQREndpoint.php');
        new AlmefyGetQREndpoint();

        require_once(__DIR__ . '/src/Rest/AlmefyDevicesEndpoints.php');
        new AlmefyDevicesEndpoints();

        require_once(__DIR__ . '/src/Rest/AlmefyRegisterEndpoint.php');
        new AlmefyRegisterEndpoint();

        require_once(__DIR__ . '/src/Rest/AlmefyVerifyCredentialsEndpoint.php');
        new AlmefyVerifyCredentialsEndpoint();

        // // Shortcodes
        require_once(__DIR__ . '/src/Shortcodes/AlmefyDeviceManagerShortcode.php');
        new AlmefyDeviceManagerShortcode();

        require_once(__DIR__ . '/src/Shortcodes/AlmefyConnectDeviceShortcode.php');
        new AlmefyConnectDeviceShortcode();
        
        require_once(__DIR__ . '/src/Shortcodes/AlmefyLoginShortcode.php');
        new AlmefyLoginShortcode();
        
        // require_once(__DIR__ . '/src/Shortcodes/AlmefyProfileShortcode.php');
        // new AlmefyProfileShortcode();
    
        require_once(__DIR__ . '/src/Shortcodes/AlmefyRegisterShortcode.php');
        new AlmefyRegisterShortcode();
        
        // Execute hooks
        require_once(__DIR__ . '/src/Util/hooks.php');


        // load localizations
        add_action('init', function () {
            load_plugin_textdomain('almefy-me', false, dirname(plugin_basename(__FILE__)) . "/languages");
        });
    }
}

new AlmefyPlugin();
