<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyRegisterShortcode
{
    public function __construct()
    {
        add_shortcode('almefy-register', function ($attributes = [], $content = null) {
            $api_enabled = get_option('almefy-api-enabled', 1);
            if ($api_enabled != 1) {
                return false;
            }

            // SETTINGS
            $attributes = shortcode_atts([
                'require_username' => "true",
                'show_button' => "true",
                'button_text' => __("Register", 'almefy-me'),
                'show_when_logged_in' => "false",
                'redirect' => 'true',
            ], $attributes, 'almefy-register');

            $show_when_logged_in = $attributes['show_when_logged_in'] == 'true';
            $require_username = $attributes['require_username'] == 'true';
            $show_button = $attributes['show_button'] == 'true';
            $button_text = $attributes['button_text'];
            // TODO: show enrollment code on site
            // $redirect = $attributes['redirect'] == 'true';

            if (!$show_when_logged_in && is_user_logged_in()) {
                return '';
            }

            // RENDER
            ob_start(); ?>

            <div class="almefy-me-register">

                <?php if ($require_username) { ?>
                    <label>
                        <span class="almefy-label"><?php _e("Username", 'almefy-me') ?></span>
                        <input type="text" required name="user_login" id="visible_name">
                    </label>
                <?php } else { ?>

                    <input type="hidden" id="hidden_name" name="user_login" id="hidden_name">

                <?php }?>

                <label>
                    <span class="almefy-label"><?php _e("Email", 'almefy-me') ?></span>
                    <input type="email" required class="almefy-email" name="user_email" id="email">
                </label>

                <?php if ($show_button) { ?>
                    <button class="button" id="register-button"><?php echo esc_attr($button_text) ?></button>
                <?php } ?>

                <p style="display: none;" class="almefy-me-register-info error"></p>
                <p style="display: none;" class="almefy-me-register-info success"></p>
                </div>

            <script>

                {
                    const rest = "<?php echo rest_url('almefy/v1/') ?>";
                    const nonce = "<?php echo wp_create_nonce('wp_rest') ?>";

                    // const form = document.querySelector('.almefy-me-register');
                    // form.addEventListener('submit', () => {
                    //     const name = document.querySelector('#hidden_name');
                    //     if(name) {
                    //         const email = document.querySelector('.almefy-email');
                    //         name.value = email.value;
                    //     }
                    // });

                    const name_input = document.querySelector('#visible_name');
                    const email_input = document.querySelector('#email');
                    const button = document.querySelector('#register-button');
                    
                    const err_msg = document.querySelector('.almefy-me-register-info.error');
                    const info_msg = document.querySelector('.almefy-me-register-info.success');

                    button.addEventListener('click', async (e)=> {
                        e.preventDefault();

                        const tmp_button_text = button.innerText;
                        err_msg.style.display = 'none';
                        info_msg.style.display = 'none';

                        button.disabled = true;
                        button.innerText = "...";


                        let name = null;
                        if (name_input) {
                            name = name_input.value;
                        }

                        const response = await fetch(rest + 'register', {
                            method: "post",
                            
                            headers: { "X-WP-NONCE": nonce, "Content-Type": "application/json", },
                            body: JSON.stringify({
                                username: name,
                                email: email_input.value,
                            })
                        });

                        if (!response.ok) {
                            const jsonResponse = await response.json();
                            err_msg.innerText = jsonResponse.data;
                            err_msg.style.display = "inline-block";
                        } else {
                            info_msg.innerText = "Check your emails to connect your smartphone!";
                            info_msg.style.display = "inline-block";

                            email_input.value = '';
                            if (name_input) name_input.value = '';
                            // window.location.href = JSON.parse(`{"raw": ${text}}`).raw;
                            // console.log(text)
                        }
                        
                        button.disabled = false;
                        button.innerText = tmp_button_text;
                        
                    });
                    
                }

            </script>
<?php
            return ob_get_clean();
        });
    }
}
