<?php

if (!defined('ABSPATH')) {
    exit;
}

// Init Almefy Client and trigger errors if necessary 
class AlmefyManager {
    
    public static $client = null;
    public static $api_url = "NOT INITIALIZED";
    
    public function __construct() {
        $sandbox_enabled = false;

        self::$api_url = AlmefyConstants::$API;
        if($sandbox_enabled) {
            self::$api_url = AlmefyConstants::$API_DEV;
        }

        $this->init_client();
    }

    // Initialize Almefy SDK client
    private function init_client() {
        $api_key = get_option('almefy-api-key');
        $api_secret = get_option('almefy-api-secret');

        // Don't try connecting if key & secret are not set.
        if($api_key == '' && $api_secret == '') {
            return;
        }

        // TODO: does not check if server is reachable
        try {
            self::$client = new \Almefy\Client($api_key, $api_secret, self::$api_url);
        } catch (\Throwable $th) {
            // TODO: Notices file 
            add_action('admin_notices', function () {
                ?>
                    <div class='error'>
                        <p>
                            Almefy key or secret invalid.<br>
                            <a href='<?php echo esc_attr(admin_url('?page=almefy')) ?>'>Change key & secret</a>
                        </p>
                    </div>
                <?php
            });
        }
    }

    // Get current user device count
    public static function current_user_uses_almefy() {
        if (self::$client == null) {
            return false;
        }

        $user_id = get_current_user_id();

        // No user logged in
        if($user_id == 0) {
            return false;
        }

        $user = get_user_by('id', $user_id);
        $email = $user->user_email;

        // Build readable device array
        try {
            $identity = self::$client->getIdentity($email);
            $tokens = $identity->getTokens();

            return count($tokens) > 0;
        } catch (\Throwable $th) {
          return false;
        }
    }

}