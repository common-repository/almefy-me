<?php

if (!defined('ABSPATH')) {
    exit;
}

// Cookie Session Timeout adjustment
add_filter ( 'auth_cookie_expiration', function ( $expire ) { 
    $timeout_in_hours = get_option('almefy-session-timeout', 12);
    return 60 * 60 * $timeout_in_hours;
});

// Quick Access for settings
// Settings link in plugin overview on plugins page
add_filter('plugin_row_meta', function ($links, $file_name) {
    if ($file_name == 'almefy-me/plugin.php') {
        return array_merge($links, ['settings' => "<a href='" . admin_url('admin.php?page=almefy') . "'>" . __('Settings', 'almefy-me') . "</a>"]);
    }
    return $links;
}, 10, 2);


// Delete identity for when deleting wordpress accounts
add_action( 'delete_user', function ($user_id) {
    global $wpdb;

    $user = get_userdata( $user_id );
    $email = $user->user_email;

    try {
        @AlmefyManager::$client->deleteIdentity($email);
    } catch (\Throwable $th) {
        // ignore since some users might not have an Almefy Identity
        // TODO: Check for specific "identity does not exist error" and only ignore those and alert on others.
    }
});

// Plugin Update
add_action( 'admin_init', function() {
    if ( ! defined( 'IFRAME_REQUEST' ) ) {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        if ( ($plugin_data = get_plugin_data( AlmefyConstants::$PLUGIN_FILE )) && isset( $plugin_data['Version'] ) ) {    
            if ( $plugin_data['Version'] != get_option( 'almefy_plugin_version', "-1" ) ) {
    
                $new_version = $plugin_data['Version'];
                
                // Update almefy configuration on plugin update
                try {
                    $configuration = AlmefyManager::$client->setConfiguration([
                        'authenticationUrl' => AlmefyConstants::AUTH_CONTROLLER()
                    ]);
    
                    // Only update version if configuration was successful
                    // This prevents on the sync failing if something is wrongly configured
                    update_option( 'almefy_plugin_version', $new_version );
                } catch (\Throwable $th) {
                    // This should only fail when the key or secret are incorrect. 
                    // In other rare cases there is nothing we can do here.
                }
            }
        }   
    }
} );

// Registration ///////////////////////////////////////////////////////////////////

// Send enrollment mail when registering on wordpress login page
add_action("register_new_user", function($user_id) {

    // TODO: Option to be logged in when registering an account
    // wp_set_current_user($user_id);
    // wp_set_auth_cookie($user_id);
    // $user = wp_get_current_user();
    // wp_logout();

    $api_enabled = get_option('almefy-api-enabled', 1) == 1;
    if (!$api_enabled) {
        return;
    }

    $send_connect_on_register = get_option('almefy-mail-connect-on-register', 0) == 1;
    if (!$send_connect_on_register) {
        return;
    }
    
    $user = get_user_by('id', $user_id);
    AlmefyMailer::send_enrollment($user->user_email); 
});

// user created by administrator, send connect email
add_action("edit_user_created_user", function($user_id, $notify) {

    $api_enabled = get_option('almefy-api-enabled', 1) == 1;
    if (!$api_enabled) {
        return;
    }

    $send_connect_on_register = get_option('almefy-mail-connect-on-register', 0) == 1;
    if (!$send_connect_on_register) {
        return;
    }

    if($notify != "both") {
        return;
    }
    
    $user = get_user_by('id', $user_id);
    AlmefyMailer::send_enrollment($user->user_email); 
}, 10, 2);

// WIZARD ///////////////////////////////////////////////////////////////////

// Run wizard on first activation
add_action('activated_plugin', function ($plugin) {
    if (strpos($plugin, 'almefy') !== false) {
        if (!get_option('almefy-api-key', false)) {
            update_option('almefy-redirect-wizard', true);
        }
    }
}, 10, 1);

// Make sure wizard is not being started when plugin gets re-enabled
add_action('admin_init', function () {
    if (get_option('almefy-redirect-wizard', false)) {
        delete_option('almefy-redirect-wizard');
        wp_redirect(admin_url('admin.php?page=almefy'));
    }
});


// Mail Sending ///////////////////////////////////////////////////////////////////

// disable "welcome to wordpress site" email.
add_filter( 'wp_new_user_notification_email', function($wp_new_user_notification_email, $user, $blogname) {

    $api_enabled = get_option('almefy-api-enabled', 1) == 1;
    if (!$api_enabled) {
        return $wp_new_user_notification_email;
    }

    $disable_welcome_mail = get_option('almefy-mail-disable-welcome', 0) == 1;
    if (!$disable_welcome_mail) {
        return $wp_new_user_notification_email;
    }

    $wp_new_user_notification_email['to'] = '';
        return $wp_new_user_notification_email;
}, 10, 3);

// replaces "set new password" email with almefy connect device mail
// add_filter('send_retrieve_password_email', function($send,  $user_login,  $user) {
//     $created_by_console = get_user_meta( $user->ID, 'created_by_almefy_console', true) === true;
//     $exists_on_almefy = false;
//     try {
//         AlmefyManager::$client->getIdentity($user->user_email);
//         $exists_on_almefy = true;
//     } catch (\Throwable $th) {
//         // TODO: Make sure it's an 404 "identity does not exist" to avoid resetting passwords when almefy is down? 
//         $exists_on_almefy = false;
//     }

//     if($created_by_console && !$exists_on_almefy) {
//         wp_delete_user($user->ID);
//         return false;
//     } else if($created_by_console || $exists_on_almefy) {
//         AlmefyMailer::send_enrollment($user->user_email, true);
//         return false;
//     } else {
//         return true;
//     }

// }, 10, 3 );

// Almefy Login activated for this account - Password Login not possible  ///////////////////////////

add_filter('wp_authenticate_user', function($user, $password) {
    
    // Check if password is correct before checking almefy
    if (wp_check_password($password, $user->user_pass, $user->ID)) {
        try {
            $identity = AlmefyManager::$client->getIdentity($user->user_email);
            $tokens = $identity->getTokens();

            // Identity exists and there is at least one connected device. This account is Almefy secured.
            if(sizeof($tokens) > 0) {
                ob_start();
                ?>
                    <div style="display: flex; gap: .75rem;">
                        <img width="48px" height="48px" src="<?php echo AlmefyConstants::$LOGO_ICON_URL?>" alt="">
                        <div>
                            <div style="font-weight: bold;"><?php _e("This account is enabled for Almefy Authentication.","almefy-me"); ?></div>
                            <?php _e("Due to security reasons you can't login with username and password anymore. <br>Please use the <a href='https://almefy.com/products/almefyapp' target='_blank'>Almefy App</a> to login.", "almefy-me"); ?>
                        </div>
                    </div>
                <?php
                $message = ob_get_clean();
                
                return new WP_Error("almefy_required", $message);
            }
        } catch (\Throwable $th) {
            // Almefy throws 404 if the identity could not be found.
            // This account is not almefy secured and regular password auth is allowed.
        } 
    }
    return $user;
}, 10, 2);

