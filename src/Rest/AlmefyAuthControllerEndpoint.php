<?php

if (!defined('ABSPATH')) {
    exit;
}

// TODO: use global client

class AlmefyAuthController
{

    public function __construct()
    {
        $this->login_controller();
    }

    private function login_controller()
    {
        add_action('rest_api_init', function () {
            register_rest_route('almefy/v1', '/login-controller', [
                // GET so it works with the "on-device" login requests in the app.
                'methods' => 'GET',
                // Public endpoint
                'permission_callback' => '__return_true',
                'callback' => function ($request) {
                    
                    // 
                    nocache_headers();
                    
                    // API Has been disabled in backend
                    $api_enabled = get_option('almefy-api-enabled', 1);
                    if ($api_enabled != 1) {
                        return new WP_Error('disabled', __('Login via Almefy has been disabled by an administrator.', 'almefy-me'));
                    }
                    
                    $token = AlmefyAuthController::extract_token(AlmefyManager::$client, $request);

                    if($token == null) {
                        return new WP_REST_Response(__("Authorization header missing.", 'almefy-me'), 400);
                    }
                    
                    $auth_result = AlmefyManager::$client->authenticate($token);
                    if ($auth_result == false) {
                        return new WP_REST_Response(__("Invalid authentication request.", 'almefy-me'), 403);
                    }

                    $email = $auth_result->getIdentifier();
                    $user = get_user_by('email', $email);
                    $almefy_role = $auth_result->getRole();
                    // $sessionId = $auth_result->getSession()->getId();

                    // Create user if they do not exist
                    if ($user == false) {
                        try {
                            $username = $email;
                            $identity = AlmefyManager::$client->getIdentity($email);
                            $nickname = $identity->getNickname(); 
                            if($nickname && $nickname != "") {
                                $username = $nickname;
                            }
                            $username = sanitize_user($username);

                            $id = wp_create_user( $username, wp_generate_password(64), $email );
                            $user = get_user_by('id', $id);
                            update_user_meta( $user->id, 'created_by_almefy_console', true);

                            $ROLE_ADMIN = "ROLE_ADMIN";
                            $ROLE_USER = "ROLE_USER";

                            if($almefy_role == $ROLE_ADMIN) {
                                $user->set_role('administrator');
                            }

                        } catch (\Throwable $th) {
                            return new WP_Error('create_user', __('Failed creating user.','almefy-me'), 500);
                        }
                    }

                    // create wordpress session for existing or newly created user.
                    wp_set_current_user($user->id);
                    wp_set_auth_cookie($user->id, true, is_ssl());
                    $redirect = AlmefyAuthController::get_redirect($request);

                    if (AlmefyAuthController::is_desktop($request)) {
                        return ['location' => $redirect];
                    }
                    wp_redirect($redirect, 301);
                    exit;
                }
            ]);
        });
    }

    // We need to know if the user is logging in inside the almefy app. If so they need another redirect method.
    public function is_desktop($request) {
        $x_requested_with = $request->get_headers()['x_requested_with'];
        if ($x_requested_with != null && $x_requested_with[0] == 'XMLHttpRequest') {
            return true;
        }
        return false;
    }

    // Extract the jwt from the request and decode it with the almefy-sdk
    public static function extract_token($client, $request) {
        $auth_header = $request->get_header('X-Almefy-Auth');
        if ($auth_header == null) {
            // TODO: is this header even in use?
            $auth_header = $request->get_header('authorization');
            if ($auth_header == null) {
                return null;
            }
            $auth_header = [mb_substr($auth_header, 7)][0];
        }
        
        $jwt = $auth_header;
        return $client->decodeJwt($jwt);
    }

    // Redirect can be set via the plugin options but also be overwritten by shortcode settings on each request.
    public static function get_redirect($request) {
        // Redirect set in plugin settings
        $redirect = get_option('almefy-api-redirect', admin_url());
        
        // Overwrite with redirect set in shortcode
        $redirect_param = $request->get_param('redirect');
        if($redirect_param && $redirect_param != '') {
            $redirect = $redirect_param;
        }

        $redirect = trim($redirect);

        if ($redirect == '') {
            $redirect = admin_url();
        }

        return $redirect;
    }
}
