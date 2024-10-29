<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyUsers {

    public function __construct() {

        // allows admins send enrollment mails in user overview
        add_filter('user_row_actions', function($actions, $user) {
            // TODO: Hide if resetting password is disabled
            // unset($actions['resetpassword']);

            // show "send 2 factor email" button
            $actions['almefy_enroll'] = "<a class='almefy-users-send-enrollment' data-user-id='$user->ID'  href='#'>" . __( 'Connect Almefy', 'almefy-me' ) . "</a>";

            // show whether almefy is enabled for this user
            try {
                // $identity = AlmefyManager::$client->getIdentity($user->user_email);
                // $tokens = $identity->getTokens();

                // if (count($tokens) > 0) {
                    // $actions['almefy_enabled'] = "<span>" . __( '2-factor devices connected: ', 'almefy-me' ) . count($tokens) . "</span>";
                // }

            } catch (\Throwable $th) {
                //throw $th;
            }

            return $actions;
        }, 10, 2);

        // makes sure the "almefy_enroll" action actually sends an email
        add_action('admin_footer', function() {
            ob_start();

            ?>

            <script>
                {
                    const rest = "<?php echo rest_url('almefy/v1/device/add') ?>";
                    const nonce = "<?php echo wp_create_nonce('wp_rest') ?>";
                    const enroll_buttons = document.querySelectorAll('.almefy-users-send-enrollment');
                    for (let button of enroll_buttons) {
                        button.addEventListener('click', async (e) => {
                            e.preventDefault();
                            
                            const old_text = button.innerText;
                            button.innerText = "<?php _e('Sending...', 'almefy-me') ?>";
                            button.disabled = true;

                            const response = await fetch(rest, {
                                method: "post",
                                body: JSON.stringify({ user_id: button.getAttribute("data-user-id") }),
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-WP-NONCE": nonce,
                                },
                            });

                            if(response.ok) {
                                button.innerText = "<?php _e('Mail sent!', 'almefy-me') ?>";
                                setTimeout(() => {
                                    button.innerText = old_text;
                                    button.disabled = false;
                                }, 6000);

                            } else {
                                button.innerText = "<?php _e('Mail could not be sent.', 'almefy-me') ?>";
                                button.classList.add('delete')
                                setTimeout(() => {
                                    button.innerText = old_text;
                                    button.classList.remove('delete')
                                    button.disabled = false;
                                }, 6000);
                            }

                            
                        });
                    }
                }
            </script>

            <?php

            echo ob_get_clean();
        });
    }

}