<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyLoginShortcode
{
    public function __construct()
    {
        add_shortcode('almefy-login', function ($attributes = [], $content = null) {

            // API disabled via settings?
            $api_enabled = get_option('almefy-api-enabled', 1);
            if ($api_enabled != 1) {
                return '';
            }

            // SETTINGS
            $attributes = shortcode_atts([
                'show_when_logged_in' => "false",
                'redirect' => '', 
                'layout' => 'column', 
                'login_page' => 'false',
            ], $attributes, 'almefy-login');

            $showWhenLoggedIn = $attributes['show_when_logged_in'] == 'true';
            $redirect = $attributes['redirect'];
            $layout_column = $attributes['layout'] == 'column';
            $login_page = $attributes['login_page'] == 'true';

            if (!$showWhenLoggedIn && is_user_logged_in()) {
                return '';
            }

            // RENDER
            if (!$login_page) {
                return $this->html($redirect, $layout_column);
            } else {
                return $this->login_page_html($redirect);
            }
        });
    }

    // We can't insert HTML into the login page regularly, so we must use js.
    public function login_page_html($redirect) {
        ob_start();
        ?>
        
        <script>
            /** @type {HTMLButtonElement} */
            let form = document.querySelector("#loginform");

            if (form) {
                const div = document.createElement("div");
                div.classList.add('almefy-login-page-container');
                div.innerHTML += `
                    <?php echo $this->html($redirect, true) ?>
                `;
                form.after(div);
            } else {
                console.error("Almefy: Could not add qr code login page. Form not found to attach to.")
            }
        </script>

        <?php
        return ob_get_clean();
    }

    // HTML & CSS form qr code display
    public function html($redirect, $layout_column)
    {
        $api_key = get_option('almefy-api-key');
        $auth_url = rest_url("almefy/v1/login-controller");

        if($redirect == 'stayonpage') {
            global $wp;
            $redirect = home_url( add_query_arg( array(), $wp->request ) );
        }

        // append "redirect" parameter to plain wordpress permalinks. 
        if(strpos($auth_url, "rest_route=") !== false) {
            $auth_url .= "&redirect=$redirect";
        } else {
            $auth_url .= "?redirect=$redirect";
        }

        $nonce = wp_create_nonce('wp_rest');
        $login_id = "almefy-login-" . mt_rand();

        ob_start(); ?>
        <div data-nonce="<?php echo $nonce ?>" data-api="<?php echo AlmefyConstants::PLUGIN_API() ?>" class="almefy-me-login-widget almefy-login-card  <?php echo $layout_column ? "almefy-login-col" :  "" ?>">
        
            <!-- <div class="flex flex-col align-center"> -->
                <a class="almefy-login-logo" target="_blank" href="https://almefy.com">
                    <img src="<?php echo AlmefyConstants::$LOGO_URL ?>" alt="Almefy">
                </a>
                <div></div>
    
                <div data-almefy-auth
                    data-almefy-api="<?php echo esc_attr(AlmefyManager::$api_url) ?>"
                    data-almefy-key="<?php echo esc_attr($api_key) ?>"
                    data-almefy-auth-url="<?php echo esc_url($auth_url) ?>">
                </div>
            <!-- </div> -->
            
            <div class="almefy-login-explainer">
                <?php _e('This is Almefy "Scan to Login".', 'almefy-me') ?><br>
                
                <?php if (get_option("almefy-connect-in-login", 0) == 1): ?>
                    <div style="margin-top: .5rem;">
                        <a class="almefy-login-btn-connect" href="#"><?php _e('Connect </a> existing account.', 'almefy-me') ?>
                    </div>
                <?php endif; ?>

                <!-- TODO: remove if feature should be scrapped entirely -->
                <?php if ( false ): ?>
                    <!-- <li> -->
                        <a class="almefy-login-btn-register" href="#"><?php _e('Register</a> password free.', 'almefy-me') ?><br>
                    <!-- </li> -->
                <?php endif; ?>

                <span style="display: block; margin-top: 1rem;"><?php _e('Use the <a target="_blank" href="https://almefy.com/products/almefyapp/">Almefy App</a> to scan the code.', 'almefy-me') ?></span>
            </div>

            <!-- Connect -->
            <?php if (get_option("almefy-connect-in-login", 0) == 1): ?>
                <div class="almefy-login-overlay almefy-login-connect flex flex-col">
                    <div class="flex justify-between align-center" style="margin-bottom: 1rem;">
                        <a class="almefy-login-logo" target="_blank" href="https://almefy.com">
                            <img src="<?php echo AlmefyConstants::$LOGO_URL ?>" alt="Almefy">
                        </a>

                        <a href="#" class="almefy-login-close" >
                            <img src="<?php echo AlmefyConstants::$BASE_URL . "assets/img/icons/x.svg" ?>" alt="X">
                        </a>
                    </div>

                    <div class="flex flex-col justify-between flex-grow" style="gap: 1rem">
                        <div style="display: block;">
                            <div style="display: block; margin-bottom: 1rem;">
                                <?php _e('Connect your account to the <a href="https://almefy.com/products/almefyapp/" target="_blank">Almefy App</a>.', 'almefy-me') ?>
                            </div>
            
                            <div style="display: block;">
                                <div class="flex">
                                    <input style="width: 100%;" class="input almefy-login-connect-input" required placeholder="Email" type="email">
                                </div>
                                <button class="button almefy-request-connect-btn"><?php _e("Connect", "almefy-me") ?></button>                
                            </div>
            
                        </div>

                        <div style="font-size: .75rem; color: gray; font-weight: light;">
                            <?php _e("By activating Almefy on your account you enable secure 2-Factor-Authentication in one step. <br>To do so, the login through username and password will be disabled. <br>Just use the <a href='https://almefy.com/products/almefyapp' target='_blank'>Almefy App</a> to login. <br>Secure, Simple, Fast.", "almefy-me") ?>
                        </div>

                        <?php if (false): ?>
                            <div >
                                <div style="display: inline-block;"><?php _e("Don't have an account?", "almefy-me") ?></div>
                                <div style="display: inline-block;">
                                    <a class="almefy-login-btn-register" href="#"><?php _e("Register", "almefy-me") ?></a> <?php _e("password-free!", "almefy-me") ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Register -->
            <div class="almefy-login-overlay almefy-login-register flex flex-col">

                <div class="flex justify-between align-center" style="margin-bottom: 1rem;">
                    <a class="almefy-login-logo" target="_blank" href="https://almefy.com">
                        <img src="<?php echo AlmefyConstants::$LOGO_URL ?>" alt="Almefy">
                    </a>

                    <a href="#" class="almefy-login-close" >
                        <img src="<?php echo AlmefyConstants::$BASE_URL . "assets/img/icons/x.svg" ?>" alt="X">
                    </a>
                </div>

                <div>
                    <div style="margin-bottom: 1rem;">
                        <?php _e('Password-free Registration', 'almefy-me') ?>
                    </div>

                    <input class="input almefy-login-register-username" required type="text" placeholder="Username">
                    <input class="input almefy-login-register-email" required type="email" placeholder="Email">

                    <button class="button almefy-register-button"><?php echo _e("Register", "almefy-me") ?></button>
                </div>
            </div>

            <!-- Message -->
            <div class="almefy-login-overlay almefy-login-message flex flex-col">

                <div class="flex justify-between align-center" style="margin-bottom: 1rem;">
                    <a class="almefy-login-logo" target="_blank" href="https://almefy.com">
                        <img src="<?php echo AlmefyConstants::$LOGO_URL ?>" alt="Almefy">
                    </a>

                    <a href="#" class="almefy-login-close" >
                        <img src="<?php echo AlmefyConstants::$BASE_URL . "assets/img/icons/x.svg" ?>" alt="X">
                    </a>
                </div>

                <div>
                    <div class="almefy-login-banner">...</div>
                </div>
            </div>
        </div>
<?php

        return ob_get_clean();
    }
}
