<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyConnectDeviceShortcode {

    public function __construct() {
        $this->shortcode();
    }

    private function shortcode() {

        add_shortcode('almefy-connect', function($attributes = [], $content = null) {
            $attributes = shortcode_atts([], $attributes, 'almefy-connect');

            if(!is_user_logged_in()) {
                return "<div class='almefy-devices-not-logged-in'>" . __('You must be logged in to connect devices.', 'almefy-me') . "</div>";
            }
            
            $user = wp_get_current_user();
            $email = $user->user_email;

            $enrollment_token = null;
            $img = "";
            $token_id = "";
            try {
                $enrollment_token = AlmefyManager::$client->enrollIdentity($email, ['sendEmail' => false]);
            } catch (\Throwable $th) {
                // return "<div style='margin: auto;'>" . __("Could not create qr code. Please try again later.", 'almefy-me') . "</div>";
            }
            
            // $img = $enrollment_token->getBase64ImageData();
            // $token_id = $enrollment_token->getId();
            $src = "data:image/png;base64,$img";
            $connect_url = "https://app.almefy.com/?c=$token_id";

            ob_start(); ?>
            
                <div class="almefy-me-connect">

                    <div class="almefy-me-connect-error" style="display: none;">Error fetching qr code.<br>Is Almefy setup correctly?</div>

                    <div class="almefy-me-connect-content flex flex-col">
                        
                        <div class="connect-desktop">
                            <h3 style=""><?php _e("Connect Device", "almefy-me") ?></h3>

                            <img width="120px" height="120px" id='enrollment-code' class='qr blurred' width="120px" src="<?php echo $src ?>">
                            
                            <p class="almefy-me-scan-me"><?php _e("Scan with the <a href='https://almefy.com/products/almefyapp' target='blank'>Almefy app</a>.", "almefy-me") ?></p>
                            <!-- <div style="max-width: 80px;"> -->
                                <!-- By activating ALMEFY on your account you enable secure 2-Factor-Authentication in one step. To do so, the login through username and password will be disabled. Just use the ALMEFY App to login. Secure, Simple, Fast. -->
                            <!-- </div> -->
                            <button id='show-enroll' class="button"><?php _e("Show Code", "almefy-me") ?></button>
                        </div>
                        
                        <div class="connect-mobile">
                            <h3 style=""><?php _e("Connect Device", "almefy-me") ?></h3>
                            <a id="connect-button" href="<?php echo esc_url($connect_url) ?>">
                                <button class="button almefy-connect-button"><?php _e('Connect Device', 'almefy-me') ?></button>
                            </a>
                        </div>                       
    
                        <a href="#" style="text-align: center; margin-top: 1rem; color: #2D4D74;" id='send-mail'><?php echo sprintf( __( 'Send a mail to %s instead.', 'almefy-me' ) , $email ) ?></a>
                    </div>
                </div>

                <script>
                    {
                        
                        // enrollment button
                        {
                            const button = document.querySelector('#show-enroll');
                            let hidden = true;
                            const qr = document.querySelector('#enrollment-code');
                            const old_text = button.innerText;
                            const connect_url = document.querySelector('#connect-button');


                            // Reload the QR code as soon as window is visible
                            onVisible(qr, async () => {
                                qr.src = await fetch_qr();
                            })

                            // cancel timer when user hides code manually
                            let active_timer = null;

                            button.addEventListener('click', async (e) => {
                                e.preventDefault();
                                hidden = !hidden;
                                
                                if(hidden) {
                                    qr.classList.add('blurred');
                                    button.innerText = old_text;
                                    clearTimeout(active_timer);
                                    active_timer = null;
                                    qr.src = await fetch_qr();
                                }
                                else  {
                                    qr.classList.remove('blurred');
                                    button.innerText = '<?php _e('Hide Code', 'almefy-me') ?>';

                                    active_timer = setTimeout(async () => {
                                        // TODO: visual timer
                                        qr.classList.add('blurred');
                                        hidden = true;
                                        button.innerText = old_text;
                                        // if redraw_list {
                                        //     await redraw_list();
                                        // }

                                        qr.dispatchEvent(new Event("almefyQrHidden"));
                                        
                                        qr.src = await fetch_qr();

                                    }, 5 * 1000);
                                }

                            
                            });
                        }

                        function onVisible(element, callback) {
                            new IntersectionObserver((entries, observer) => {
                                entries.forEach(entry => {
                                if(entry.intersectionRatio > 0) {
                                    callback(element);
                                    observer.disconnect();
                                }
                                });
                            }).observe(element);
                        }

                        async function fetch_qr() {

                            const rest = "<?php echo rest_url('almefy/v1/') ?>";
                            const nonce = "<?php echo wp_create_nonce('wp_rest') ?>";
                            const user_id = "<?php echo get_current_user_id(); ?>";

                            const content = document.querySelector('.almefy-me-connect-content');
                            const error_banner = document.querySelector('.almefy-me-connect-error');

                            const response = await fetch(rest + "device/connect_qr", {
                                method: "get",
                                headers: { "X-WP-NONCE": nonce },
                            });
    
                            if (response.ok) {
                                // TODO: return img and tokenId so I can update qr code
                                const text = await response.text();
                                // Undo escaping done by wordpress

                                content.style.display = "block";
                                error_banner.style.display = "none";
                                
                                return JSON.parse(text);
                            } else {
                                // TODO: show error in frontend
                                console.error('almefy - could not fetch qr image.')

                                content.style.display = "none";
                                error_banner.style.display = "block";
                                return '';
                            }
                        }

                        // send enrollment mail button
                        {

                            const rest = "<?php echo rest_url('almefy/v1/') ?>";
                            const nonce = "<?php echo wp_create_nonce('wp_rest') ?>";
                            const user_id = "<?php echo get_current_user_id(); ?>";
                            const email = "<?php echo $email ?>";

                            const button = document.querySelector('#send-mail');
                            button.addEventListener('click', async (e) => {
                                e.preventDefault();

                                const old_text = button.innerText;
                                button.innerText = '...';
                                button.disabled = true;

                                const response = await fetch(rest + "device/add", {
                                    method: "post",
                                    body: JSON.stringify({ user_id }),
                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-WP-NONCE": nonce,
                                    },
                                });

                                if (response.ok) {
                                    // show mail sent
                                    button.innerText = "<?php _e('Mail sent!', 'almefy-me') ?>";
                                    console.log(button)
                                    setTimeout(() => {
                                        button.innerText = old_text;
                                        button.disabled = false;
                                    }, 6000);

                                } else {
                                    console.error('almefy - enrollment mail could not be sent.')
                                    button.innerText = "<?php _e('Mail could not be sent.', 'almefy-me') ?>";
                                    button.classList.add('almefy-error-text')
                                    setTimeout(() => {
                                        button.innerText = old_text;
                                        button.classList.remove('almefy-error-text')
                                        button.disabled = false;
                                    }, 6000);
                                }
                            })
                        }
                    }
                </script>
            <?php
            return ob_get_clean();
        });
    }

}