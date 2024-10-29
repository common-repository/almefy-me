<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyDeviceManagerShortcode {
    
    public function __construct() {
        $this->shortcode();
    }

    private function shortcode() {
        add_shortcode('almefy-devices', function($attributes = [], $content = null) {
            $attributes = shortcode_atts([], $attributes, 'almefy-devices');

            if(!is_user_logged_in()) {
                return "<div class='almefy-devices-not-logged-in'>" . __('You must be logged in to manage devices.', 'almefy-me') . "</div>";
            }
            
            ob_start(); ?>

                <style>


                    /* devices */

                    .almefy-devices-container {
                        min-height: 15rem;
                    }

                    .almefy-devices-container h3 {
                        margin-top: 0;
                        font-size: 24px;
                        font-weight: bold;
                        text-transform: uppercase;
                        margin-bottom: 1.8rem;
                        margin-top: 1rem;
                    }

                    .almefy-me-devices {
                        display: grid;
                        grid-template-columns: 1fr;
                        grid-template-rows: min-content auto min-content;
                        justify-items: start;

                        min-height: 15rem;
                    }

                    .almefy-devices-table {
                        display: grid;
                        grid-template-columns: 13rem 6rem 6rem 2rem; 
                    }
                    
                    .almefy-devices-row {
                        display: grid;
                        order: 1;
                    }

                    .almefy-devices-row .label {
                        font-weight: bold;
                        display: none;
                    }

                    .almefy-devices-table-head {
                        margin-bottom: .5rem;
                        padding-bottom: .5rem; 
                        border-bottom: solid 1px rgb(240, 240, 240);
                        font-weight: bold;
                    }

                    .almefy-center {
                        text-align: center;
                    }

                    .almefy-v-divider {
                        border-left: 1px solid rgb(240, 240, 240);
                        margin: 0 2rem;
                    }

                    .almefy-manager-container .app-icons {
                        display: flex; gap: .5rem; justify-content: center; flex-wrap: wrap; margin: auto; margin-top: 1.5rem;
                    }

                    .almefy-delete {
                        border: none;
                        padding: 0 !important;
                        border-radius: 0 !important;
                        font-weight: bold;
                        background: none;
                        cursor: pointer;
                        min-width: 32px;
                        order: 1;
                    }
                    
                    @media (max-width:620px) {

                        .almefy-devices-table-head {
                            display: none;
                        }

                        .almefy-devices-table {
                            width: 100%;
                            grid-template-columns: 1fr; 
                            margin-bottom: 1.5rem;
                        }

                        .almefy-devices-row {
                            grid-template-columns: minmax(3rem, 5rem) auto; 
                        }

                        .almefy-devices-row .label {
                            display: block;
                        }

                        .almefy-delete {
                            /* mobile */
                            /* justify-self: end; */
                            order: -1;
                        }

                        .almefy-mobile-center {
                            margin: auto;
                        }

                        .almefy-devices-container {
                            width: 100%;
                        }

                        .almefy-devices-container h3, .almefy-refresh-devices.button {
                            margin-left: auto;
                            margin-right: auto;
                        }
                    }


                    .almefy-me-download {
                        /* display: none; */
                    }

                    .almefy-me-hidden {
                        display: none !important;
                    }

                    .almefy-me-download-notice {
                        font-size: 16px;
                    }

                </style>



                <div class="almefy-devices-container">

                    
                    <div class="almefy-me-download almefy-me-hidden">
                        <h3><?php _e('Download Almefy', 'almefy-me') ?></h3>

                        <div class="flex " style="gap: 1rem; margin-top: 1rem;">
                            <a target="_blank" href="https://apps.apple.com/de/app/almefy/id1622001172">
                                <img src="<?php echo AlmefyConstants::$APP_LOGO_URL ?>" alt="">
                            </a>
                            <a target="_blank" href="https://play.google.com/store/apps/details?id=com.almefy.app&gl=US">
                                <img src="<?php echo AlmefyConstants::$PLAY_LOGO_URL ?>" alt="">
                            </a>
    
                        </div>
                        <p>
                            <?php _e("Get the Almefy app and scan the QR code to connect the device.", "almefy-me") ?>
                        </p>
                    </div>

                    <div class="almefy-me-devices">
                        <h3><?php _e('Manage Devices', 'almefy-me') ?></h3>
    
                        <div class="almefy-mobile-center">
                            <div class="almefy-devices-table almefy-devices-table-head">
                                <div><?php _e('Device', 'almefy-me') ?></div>
                                <div><?php _e('Label', 'almefy-me') ?></div>
                                <div class=""><?php _e('Added', 'almefy-me') ?></div>
                                <div class="almefy-center"></div>
                            </div>
    
                            <div class="almefy-devices-table almefy-devices-body">
                            </div>
                        </div>
    
                        <button class="button almefy-refresh-devices"><?php _e('Refresh', 'almefy-me') ?></button>
                    </div>

                </div>



                <script>
                    {
                        // devices list
                        const rest = "<?php echo rest_url('almefy/v1/') ?>";
                        const nonce = "<?php echo wp_create_nonce('wp_rest') ?>";
                        const user_id = "<?php echo get_current_user_id(); ?>";

                        window.addEventListener('load', () => {
                            const qr = document.querySelector('#enrollment-code');
                            if (qr) {
                                qr.addEventListener('almefyQrHidden', async () => {
                                    // console.log("almefy - detected freshly hidden enrollment code.");
                                    await redraw_list(rest, nonce);
                                });
                            }
                        })


                        async function fetch_devices(rest, nonce) {
                            const response = await fetch(rest + "devices", {
                                method: "get",
                                headers: { "X-WP-NONCE": nonce },
                            });
    
                            if (response.ok) {
                                const json = await response.json();
                                return json;
                            } else {
                                // TODO: show error in frontend
                                console.error('almefy - could not fetch connected devices.')
                                return [];
                            }
                        }

                        function switch_display(devices_cnt) {
                            const devices_container = document.querySelector('.almefy-me-devices');
                            const downloads_container = document.querySelector('.almefy-me-download');

                            // console.log(devices_cnt)

                            if(devices_cnt == 0) {
                                devices_container.classList.add("almefy-me-hidden"); 
                                downloads_container.classList.remove("almefy-me-hidden"); 
                            } else {
                                devices_container.classList.remove("almefy-me-hidden"); 
                                downloads_container.classList.add("almefy-me-hidden"); 
                            }

                        }

                        async function delete_device(id, rest, nonce) {

                            const response = await fetch(rest + "device/remove", {
                            method: "post",
                            body: JSON.stringify({ device_id: id }),
                            headers: {
                                "Content-Type": "application/json",
                                "X-WP-NONCE": nonce,
                            },
                            });

                            if(!response.ok) console.error("Failed to delete device: ", (await response.json()));

                            return response.ok;
                        }

                        async function redraw_list(rest, nonce) {
                            console.log("almefy - redrawing device list")
                            const devices = await fetch_devices(rest, nonce);
                            const list = document.querySelector('.almefy-devices-body');
                            list.innerHTML = '';

                            // show/hide download guide
                            switch_display(devices.length);

                            for(let i = 0; i < devices.length; i++) {
                                const device = devices[i];

                                if(device.label == null) {
                                    device.label = '';
                                }
                                
                                list.innerHTML += `
                                    <div class="almefy-devices-row"> <div class="label"><?php _e('Device', 'almefy-me') ?></div> <div>${device.name}</div></div>
                                    <div class="almefy-devices-row"> <div class="label"><?php _e('Label', 'almefy-me') ?></div> <div>${device.label}</div></div>
                                    <div class="almefy-devices-row"> <div class="label"><?php _e('Added', 'almefy-me') ?></div> <div>${new Date(device.created_at).toISOString().split('T')[0]}</div></div>
                                `;

                                const button = document.createElement('button');
                                button.innerText = 'x'
                                button.classList.add('almefy-delete');
                                button.setAttribute('almefy-device-index', i)

                                list.appendChild(button)
                            }
                            
                            const delete_buttons = document.querySelectorAll('.almefy-delete');
                            // console.log(delete_buttons);

                            for (button of delete_buttons) {

                                const device = devices[button.getAttribute('almefy-device-index')];
                                // console.log("device ", device);

                                button.addEventListener('click', async (e) => {
                                    e.preventDefault();
                                    if(confirm(`<?php _e('Are you sure you want to delete this device?', 'almefy-me') ?> ${device.name}?`)) {
                                        const ok = await delete_device(device.id, rest, nonce);
                                        if(!ok) {
                                            alert("<?php _e('There was an error. Please try again later.', 'almefy-me') ?>")
                                        }
                                        await redraw_list(rest, nonce);
                                    }
                                })
                            }

                            // for (device of devices) {
                            // } 
                        }
    
                        
                        async function init(rest, nonce){
                            await redraw_list(rest, nonce);

                            const refresh = document.querySelector('.almefy-refresh-devices');
                            if(refresh) {
                                refresh.addEventListener('click', async () => {
                                    const tmp_text = refresh.innerText;
                                    refresh.innerText = "...";
                                    await redraw_list(rest, nonce);
                                    refresh.innerText= tmp_text;
                                });
                            }
                        }
    
                        init(rest, nonce);
                    }
                </script>
            <?php
            return ob_get_clean();
        });
    }
}