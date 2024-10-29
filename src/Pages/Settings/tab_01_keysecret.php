
<p style="max-width: 110ch; padding: 20px 0;">
    <?php _e("With your key & secret you enable the secure communication with the server during the login process.", 'almefy-me') ?>
    <!-- <br>If you don't have a key & secret yet, please follow the instructions here: -->
    <!-- <span style="display: block; margin-top: .5rem;">
        <a href="https://almefy.com/prices" rel="noopener" target="_blank" class="button">
            <?php _e("Request key & secret", 'almefy-me') ?>
        </a>
    </span> -->
</p>
<div>

    <style>
        .almefy-button-green, .almefy-button-green:hover {
            color: var(--almefy-green) !important;
            border-color: var(--almefy-green) !important;
        }

        .almefy-button-error, .almefy-button-error:hover {
            background: transparent;
            border-color: var(--almefy-red) !important;
            color: var(--almefy-red) !important;
        }
    </style>

    <table class="form-table">
        <tbody>
            <?php echo do_settings_fields('almefy', AlmefyConstants::$SECTION_KEY_SECRET); ?>
            <tr>
                <th></th>
                <td><button id="btn-verify" class="button"><?php _e("Activate", "almefy-me") ?></button></td>
            </tr>
        </tbody>
    </table>

    <script>
        {
            const button = document.querySelector('#btn-verify');
            const key = document.querySelector('#almefy-api-key');
            const secret = document.querySelector('#almefy-api-secret');

            const almefy_api =  "<?php echo rest_url('almefy/v1/') ?>";

            async function start() {

                if(key.value != '' && secret.value != '') {
                    const response = await validate(key.value, secret.value);
    
                    if (response.ok) {
                        button.textContent = "<?php _e("Active", "almefy-me") ?>";
                        // button.disabled = true;
                        button.classList.add('almefy-button-green');
                    } else {
                        button.textContent = "<?php _e("Invalid. Try again", "almefy-me") ?>";
                        button.disabled = false;
                        button.classList.add('almefy-button-error');
                    }
                }
            }

            key.addEventListener('blur', () => {
                button.textContent = "<?php _e("Activate", "almefy-me") ?>";
                button.disabled = false;
                button.classList.remove('almefy-button-green');
                button.classList.remove('almefy-button-error');
            });

            secret.addEventListener('blur', () => {
                button.textContent = "<?php _e("Activate", "almefy-me") ?>";
                button.disabled = false;
                button.classList.remove('almefy-button-green');
                button.classList.remove('almefy-button-error');
            });

            async function validate(key, secret) {

                return await fetch(
                    almefy_api + "verify_credentials", {
                        method: "post",
                        body: JSON.stringify({
                            key,
                            secret
                        }),
                        headers: {
                            "Content-Type": "application/json",
                            "X-WP-NONCE": "<?php echo esc_html(wp_create_nonce('wp_rest')) ?>",
                        },
                    }
                );
            }

            // check key when loading page
            start();

            // check on button press
            if (!button) {
                console.warn('Key validation: Could not find button.');
            } else {
                const old_text = button.innerText;

                button.addEventListener('click', async (event) => {
                    event.preventDefault();

                    
                    if (!key) {
                        console.error('Key validation: Could not find key input.');
                        return;
                    }
                    if (!secret) {
                        console.error('Key validation: Could not find secret input.');
                        return;
                    }
                    button.classList.remove('almefy-button-green');
                    button.classList.remove('almefy-button-error');
                    
                    button.innerText = "Activating..."
                    // button.disabled = true;

                    const response = await validate(key.value, secret.value);

                    if (response.ok) {
                        button.textContent = "<?php _e("Active!", "almefy-me") ?>";
                        button.classList.add('almefy-button-green');
                        // button.disabled = true;

                        // Show connect device modal
                        tb_show("Almefy has been activated!", "#TB_inline?&width=1100&height=550&inlineId=almefy-connect-after-setup"); 
                        
                    } else {
                        button.innerHTML = "<?php _e("Invalid key or secret.", "almefy-me") ?>";
                        setTimeout(() => {
                            button.innerText = old_text;
                            button.classList.remove('almefy-button-error');
                        }, 5000);
                        button.classList.add('almefy-button-error');
                        // button.disabled = false;
                    }

                });
            }

        }
    </script>
</div>