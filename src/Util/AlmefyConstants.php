<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyConstants {

    // error messages, paths, etc, settings

    // Almefy API 

    public static $API = "https://api.almefy.com";
    public static $API_DEV = "https://api.dev.almefy.com";

    // TODO: https://app.almefy.com/?c=

    // Local API

    public static function PLUGIN_API() {
        return rest_url('almefy/v1/');
    }

    public static function AUTH_CONTROLLER() {
        return self::PLUGIN_API() . 'login-controller';
    }

    // Assets

    public static $BASE_DIR = __DIR__ . "/../..";
    public static $BASE_URL = "NULL";
    public static $PLUGIN_FILE = __DIR__ . "/../.." . "/plugin.php";

    public static $LOGO_URL = "NULL";
    public static $LOGO_ICON_URL = "NULL";
    public static $PLAY_LOGO_URL = "NULL";
    public static $APP_LOGO_URL = "NULL";
    public static $PLAY_QR_URL = "NULL";
    public static $APP_QR_URL = "NULL";

    // Pages

    // Options

    public static $SECTION_KEY_SECRET = "almefy-keysecret";
    public static $SECTION_SETTINGS = "almefy-settings";
    public static $SECTION_HOW_TO = "almefy-howtouse";

    public static $SECRET_PLACEHOLDER = "#ALMEFY-SECRET-PLACEHOLDER#";

    // Short Codes

    // Messages

    // Init dynamic values
    public function __construct() {
        self::$BASE_URL = plugin_dir_url(__FILE__) . "../../";

        self::$LOGO_URL = plugin_dir_url(__FILE__) . "../../assets/img/logo.svg";
        self::$LOGO_ICON_URL = plugin_dir_url(__FILE__) . "../../assets/img/logo_icon.svg";
        self::$PLAY_LOGO_URL = plugin_dir_url(__FILE__) . "../../assets/img/getgoogle.png";
        self::$APP_LOGO_URL = plugin_dir_url(__FILE__) . "../../assets/img/getapple.png";
        self::$PLAY_QR_URL = plugin_dir_url(__FILE__) . "../../assets/img/qr-play-store.png";
        self::$APP_QR_URL = plugin_dir_url(__FILE__) . "../../assets/img/qr-app-store.png";
    }

}



