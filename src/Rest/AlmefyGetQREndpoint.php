<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyGetQREndpoint
{

    public function __construct()
    {
        $this->api_get_enrollment_qr();
    }

    // allow requesting enrollment qr image for logged in user only
    protected function api_get_enrollment_qr() {
        add_action('rest_api_init', function() {
            register_rest_route(
                'almefy/v1',
                '/device/connect_qr',
                [
                    'methods' => 'GET',
                    'permission_callback' => function() {

                        if (AlmefyManager::$client == null) {
                            return new WP_REST_Response(__('Sorry, the almefy API data is incorrect!', 'almefy-me'), 500);
                        }

                        if (!is_user_logged_in()) {
                            return new WP_REST_Response(__('You must be logged in to perform this action.', 'almefy-me'), 403);
                        }

                         // check plugin settings
                        $api_enabled = get_option('almefy-api-enabled', 1) == 1;
                        if (!$api_enabled) {
                            return new WP_REST_Response(__('Almefy API has been disabled by an administrator.', 'almefy-me'), 500);
                        }

                        return true;
                    },
                    'callback' => function($request) {
                        // send error if almefy client could not be initialized
                        
                         // check plugin settings
                         $api_enabled = get_option('almefy-api-enabled', 1) == 1;
                         if (!$api_enabled) {
                             return new WP_REST_Response(__('Almefy API has been disabled by an administrator.', 'almefy-me'), 500);
                         }

                        if (AlmefyManager::$client == null) {
                            return new WP_REST_Response(__('Sorry, the almefy API data is incorrect!', 'almefy-me'), 500);
                        }

                        // get logged in user
                        $user = wp_get_current_user();
                        $email = $user->user_email;
                        try {                           
                            $enrollment_token = AlmefyManager::$client->enrollIdentity($email, ['sendEmail' => false]);
                            $img = $enrollment_token->getBase64ImageData();
                            $src = "data:image/png;base64,$img";
                            
                            return new WP_REST_Response($src);
                        } catch (\Throwable $th) {
                            // return new WP_REST_Response(print_r($th, true), 500);
                            return new WP_REST_Response(__("Error connecting to Almefy API.", "almefy-me"), 500);
                        }
                    }
                ]
            );
        });
    }
}