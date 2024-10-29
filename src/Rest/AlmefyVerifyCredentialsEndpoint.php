<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyVerifyCredentialsEndpoint
{
    public function __construct()
    {
        $this->api_verify_license();
    }

    private function api_verify_license()
    {
        add_action('rest_api_init', function () {
            register_rest_route('almefy/v1', '/verify_credentials', [
                'methods' => 'POST',
                'permission_callback' => function() {

                    if (AlmefyManager::$client == null) {
                        return new WP_REST_Response(__('Sorry, the almefy API data is incorrect!', 'almefy-me'), 500);
                    }

                    if (!is_user_logged_in()) {
                        return new WP_REST_Response(__('You must be logged in to perform this action.', 'almefy-me'), 403);
                    }

                    // user is admin
                    if (!current_user_can('manage_options')) {
                        return new WP_REST_Response(__('You must be an administrator to perform this action.', 'almefy-me'), 403);
                    }

                    return true;
                },                
                'callback' => function ($request) {

                    // body params
                    $key = $request['key'];
                    $secret = $request['secret'];

                    if($secret == AlmefyConstants::$SECRET_PLACEHOLDER) {
                        $secret = get_option('almefy-api-secret');
                    }

                    $client = null;
                    try {
                        $client = new \Almefy\Client($key, $secret, esc_attr(AlmefyManager::$api_url));
                        $client->check();

                        update_option('almefy-api-key', $key);
                        update_option('almefy-api-secret', $secret);

                        return new WP_REST_Response('ok', 200);
                    } catch (\Throwable $th) {
                        return new WP_REST_Response("Almefy error: " . $th->getMessage(), 400);
                    }
                }
            ]);
        });
    }
}
