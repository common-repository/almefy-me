<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyLoginPage
{
    // Display login code on wp login page.
    public function __construct()
    {
        $api_enabled = get_option('almefy-api-enabled', 1);
        if ($api_enabled != 1) {
            return false;
        }

        add_action('lostpassword_form', function () {
            ob_start();

            ?>
            <p>
                <button id="almefy-reconnect" class="button button-large">Reconnect Almefy</button>
            </p>
            <p style="text-align: center; margin: .3rem 0;">
                - or -
            </p>

            <script>
                {
                    const login_url = "<?php echo wp_login_url() ?>";

                    const button = document.querySelector('#almefy-reconnect');
                    const user_login = document.querySelector('#user_login');

                    const message = document.querySelector('.message');

                    if(button && user_login) {

                        button.addEventListener('click', async(e) => {
                            e.preventDefault();
                            user_login.required = true;
                            user_login.reportValidity();

                            if(user_login.value == '') {
                                return;
                            }

                            const res = await request_reconnect(user_login.value);
                            // console.log(res)
                            if(res.ok) {
                                window.location.href = login_url + '?checkemail=confirm';
                            } else {
                                console.error(res)
                                
                                const old_error = document.querySelector("#login_error");
                                if (old_error) old_error.remove();

                                const new_error = document.createElement('div');
                                new_error.id = 'login_error';
                                new_error.classList.add("notice-error");

                                const err_msg = await res.json();
                                new_error.innerHTML = `<strong>Error: </strong>${err_msg}<br>`;
                                message.after(new_error)
                            }
                        });
                    }

                    async function request_reconnect(user_login) {
                        const rest = "<?php echo rest_url('almefy/v1/') ?>";
                        const nonce = "<?php echo wp_create_nonce('wp_rest') ?>";

                        const response = await fetch(rest + "device/reconnect", {
                            method: "post",
                            body: JSON.stringify({ user_login }),
                            headers: {
                                "Content-Type": "application/json",
                                "X-WP-NONCE": nonce,
                            },
                        });

                        return await response;
                    }
                }
                

            </script>

            <?php
            echo ob_get_clean();

            // echo do_shortcode('[almefy-register]');

            // echo "<p style='margin-bottom: 1rem;'>";
            // _e("If the specified email is almefy login enabled you will receive a qr code to register a new device.", 'almefy-me');
            // echo "</p>";
        });

        add_action('login_footer', function () {
            // don't show login code when registering or requesting email etc.
            if (!isset($_GET['action']) && !isset($_GET['checkemail'])) {
                // login page
                echo do_shortcode("[almefy-login login_page='true']");
            }
        });
    }

}
