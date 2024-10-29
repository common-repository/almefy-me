<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyRegisterEndpoint
{

    public function __construct()
    {
        $this->api();
    }

    private function api()
    {
        add_action('rest_api_init', function () {
            register_rest_route('almefy/v1', '/register', [
                'methods' => 'POST',
                'permission_callback' => '__return_true',
                'callback' => function ($request) {

                    // API Has been disabled in backend
                    $api_enabled = get_option('almefy-api-enabled', 1) == 1;
                    // $registration_enabled = get_option('almefy-mail-connect-on-register') == 1;
                    if (!$api_enabled) {
                        return new WP_Error('disabled', __('Registration via Almefy has been disabled by an administrator.', 'almefy-me'));
                    }

                    $client = AlmefyManager::$client;
                    // Check if API set up correctly
                    if ($client == null) {
                        return new WP_REST_Response(__('Sorry, the almefy API data is incorrect!', 'almefy-me'), 500);
                    }

                    // body params
                    $username = sanitize_user($request['username']);
                    $email = sanitize_email($request['email']);

                    // Check if the email is valid
                    if (empty($email) || !is_email($email)) {
                        wp_send_json_error(__('Sorry, email missing or invalid!', 'almefy-me'), 400);
                        return;
                    }
                
                    // Generate a random string if the username is null or empty
                    if (empty($username)) {
                        $username = wp_generate_password(12, false, false);
                    }

                    try {
                        $success = AlmefyMailer::send_enrollment($email, $username);
                        
                    } catch (\Almefy\Exception\TransportException $e) {
                        return new WP_REST_Response(__('Could not connect to Almefy service.', 'almefy-me'), 500);
                    }

                    // wp_set_current_user($user_id);
                    // wp_set_auth_cookie($user_id, true, is_ssl());
                    // wp_redirect("/", 301);

                    // TODO: return redirect url?
                    // return admin_url('/users.php?page=almefy-device-manager');
                }
            ]);
        });
    }
}
