<?php
// Link/Unlink devices

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyDevicesEndpoints
{

    public function __construct()
    {
        $this->api_add_device();
        $this->api_reconnect_device();
        $this->api_get_devices();
        $this->api_remove_device();
    }

    // allow sending enrollment mail to any user_id
    protected function api_reconnect_device()
    {
        add_action('rest_api_init', function () {
            register_rest_route(
                'almefy/v1',
                '/device/reconnect',
                [
                    'methods' => 'POST',
                    'permission_callback' => '__return_true',
                    'callback' => function ($request) {
                        // send error if almefy client could not be initialized
                        if (AlmefyManager::$client == null) {
                            return new WP_REST_Response(__('Sorry, the almefy API data is incorrect!', 'almefy-me'), 500);
                        }

                        $user_login = null;
                        if (isset($request['user_login']) && is_string( $request['user_login'])) {
                            $user_login = wp_unslash( $request['user_login'] );
                        }

                        $user = get_user_by( 'login', $user_login );
                        if (!$user && strpos( $user_login, '@')) {
                            $user = get_user_by( 'email', $user_login );
                        }

                        if(!$user) {
                            return new WP_REST_Response(__('Account does not exist.', 'almefy-me'), 400);
                        }

                        // $email = $user->user_email;

                        // send mail
                        try {
                            $success = AlmefyMailer::send_enrollment($user->user_email, $user->user_login);
                            
                            if(!$success) {
                                return new WP_REST_Response(__('Wordpress could not send this email. Is Wordpress configured correctly?', 'almefy-me'), 500);
                            }
                            return new WP_REST_Response(['success' => "true"]);
                        } catch (\Almefy\Exception\TransportException $e) {
                            return new WP_REST_Response(__('Could not connect to Almefy service.', 'almefy-me'), 500);
                        }
                    }
                ]
            );
        });
    }

    // allow sending enrollment mail to any user_id
    protected function api_add_device()
    {
        add_action('rest_api_init', function () {
            register_rest_route(
                'almefy/v1',
                '/device/add',
                [
                    'methods' => 'POST',
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
                    'callback' => function ($request) {
                        // send error if almefy client could not be initialized
                        if (AlmefyManager::$client == null) {
                            return new WP_REST_Response(__('Sorry, the almefy API data is incorrect!', 'almefy-me'), 500);
                        }

                        $user_id = null;
                        if (isset($request['user_id']) && $request['user_id'] != '' && current_user_can('edit_users')) {
                            $user_id = $request['user_id'];
                        } else {
                            $user_id = get_current_user_id();
                        }
                        $user = get_user_by('id', $user_id);
                        $email = $user->user_email;

                        // https://developer.wordpress.org/reference/functions/wp_tempnam/
                        

                        // send mail
                        try {
                            $success = AlmefyMailer::send_enrollment($email, true);
                            
                            if(!$success) {
                                return new WP_REST_Response(__('Wordpress could not send this email. Is Wordpress configured correctly?', 'almefy-me'), 500);
                            }
                            return new WP_REST_Response(['sent_to' => $email]);
                        } catch (\Almefy\Exception\TransportException $e) {
                            return new WP_REST_Response(__('Could not connect to Almefy service.', 'almefy-me'), 500);
                        }
                    }
                ]
            );
        });
    }

    protected function api_get_devices()
    {
        add_action('rest_api_init', function () {
            register_rest_route(
                'almefy/v1',
                '/devices',
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
                    },                    'callback' => function ($request) {
                        // send error if almefy client could not be initialized
                        if (AlmefyManager::$client == null) {
                            return new WP_REST_Response(__('Sorry, the almefy API data is incorrect!', 'almefy-me'), 500);
                        }

                        $user_id = get_current_user_id();
                        $user = get_user_by('id', $user_id);
                        $email = $user->user_email;

                        // Build readable device array
                        try {
                            $identity = AlmefyManager::$client->getIdentity($email);
                            $tokens = $identity->getTokens();

                            $devices = [];

                            foreach ($tokens as $token) {
                                $devices[] = [
                                    'id' => esc_html($token->getId()),
                                    'created_at' => esc_html($token->getCreatedAt()),
                                    'name' => esc_html($token->getName()),
                                    'label' => esc_html($token->getLabel()),
                                    'model' => esc_html($token->getModel()),
                                ];
                            }

                            return new WP_REST_Response(__($devices, 'almefy'));
                        } catch (\Throwable $th) {
                            return new WP_REST_Response(__('An error occurred fetching devices.', 'almefy-me'), 500);
                        }
                    }
                ]
            );
        });
    }

    protected function api_remove_device()
    {
        add_action('rest_api_init', function () {
            register_rest_route(
                'almefy/v1',
                '/device/remove',
                [
                    'methods' => 'POST',
                    // TODO: PERMISSIONS
                    'permission_callback' => '__return_true',
                    'callback' => function ($request) {
                        // send error if almefy client could not be initialized
                        if (AlmefyManager::$client == null) {
                            return new WP_REST_Response(__('Sorry, the almefy API data is incorrect!', 'almefy-me'), 500);
                        }

                        // get logged in user

                        if (!is_user_logged_in()) {
                            return new WP_REST_Response(__('You must be logged in to perform this action.', 'almefy-me'), 403);
                        }

                        $user_id = get_current_user_id();
                        $user = get_user_by('id', $user_id);
                        $email = $user->user_email;

                        // body params
                        $device_id = $request['device_id'];

                        if ($device_id == null || $device_id == '') {
                            return new WP_REST_Response(__('Missing device_id parameter in body.', 'almefy-me'), 400);
                        }

                        // Build readable device array
                        try {
                            $identity = AlmefyManager::$client->getIdentity($email);
                            $tokens = $identity->getTokens();

                            // check if id is actually bound to this user
                            $device_found = false;
                            foreach ($tokens as $token) {
                                if ($device_id === $token->getId()) {
                                    $device_found = true;
                                    break;
                                }
                            }

                            if ($device_found) {
                                AlmefyManager::$client->deleteToken($device_id);
                            } else {
                                return new WP_REST_Response(__('Device id does not belong to this identity.', 'almefy-me'), 400);
                            }

                            return new WP_REST_Response(true);
                        } catch (\Throwable $th) {
                            // TODO: Proper error message
                            // return new WP_REST_Response(print_r($th, true), 500);
                            return new WP_REST_Response(__("An error occurred deleting this device. Please try again later or contact support.", 'almefy-me'), 500);
                        }
                    }
                ]
            );
        });
    }
}
