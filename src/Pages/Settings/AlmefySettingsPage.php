<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefySettingsPage
{

    public $page_suffix = '';

    public function __construct()
    {
        $this->main_page();

        // To prevent other wordpress admins seeing the login key in the frontend, it is replaced with a dummy $SECRET_PLACEHOLDER .
        // This filter prevent that dummy actually being saved to the database, it must be filtered out.
        add_filter("pre_update_option_" . "almefy-api-secret", function($value, $old_value, $option) {
            if ($value == AlmefyConstants::$SECRET_PLACEHOLDER) {
                return $old_value;
            }
            return $value;
        }, 10, 3);
    }

    // Add settings page to admin panel.
    private function main_page()
    {

        add_action('admin_enqueue_scripts', function ($hook) {
            if ($hook == $this->page_suffix) {
                wp_enqueue_style('almefy-settings-style', AlmefyConstants::$BASE_URL . "assets/style/admin.css", ['almefy-helpers'], '1');
            }
        });

        add_action('admin_menu', function () {

            ob_start();
            require_once(__DIR__ . "/icon.php");
            $icon_data = ob_get_clean();

            $this->page_suffix = add_menu_page('Almefy Settings', 'Almefy', 'manage_options', 'almefy', function () {
                
                // load modal css and js
                add_thickbox();

                if (!current_user_can('manage_options')) {
                    return;
                }

                require_once(__DIR__ . '/../../Components/almefy_header.php');

                // show settings saved info
                if (isset($_GET['settings-updated'])) {
                    add_settings_error('almefy_messages', 'almefy_message', __('Settings Saved', 'almefy-me'), 'updated');
                }
                // show messages/errors
                
                // Render Settings
                ?>

                <!-- Connection Modal -->
                <div id="almefy-connect-after-setup" style="display: none;">
                    <p>
                        <div class="almefy-me-box" style="padding-top: 0rem;">
                            <?php _e('Almefy has been successfully activated.<br>Please scan the Code with the Almefy app to connect the device.', 'almefy-me') ?>
                            <br>
                            <br>
                            <?php _e("By enabling Almefy for secure 2FA login in one step, we will disable the login through username and password. <br>Going forward you can only login by using the <a href='https://almefy.com/products/almefyapp' target='_blank'>Almefy App</a>.", "almefy-me") ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 2rem;">
                                
                                <div class="almefy-me-box almefy-me-max-w-500 m-0 flex flex-col almefy-me-device-page-col" >
                                    <?php echo do_shortcode('[almefy-devices]') ?>
                                </div>

                                <div class="almefy-me-device-pageseparator almefy-me-device-page-col">
                                    <?php echo do_shortcode('[almefy-connect]') ?>
                                </div>
                    
                            </div>
                        </div>
                    </p>
                </div>

                <!-- Settings -->
                <div class="wrap">
                    <?php echo settings_errors('almefy_messages'); ?>

                    <form action="options.php" method="post">
                        <?php echo settings_header() ?>
                        <?php echo settings_fields("almefy") ?>

                        <h2 class="nav-tab-wrapper">
                            <a class="nav-tab" href="#keysecret"><?php _e('Key & Secret', 'almefy-me') ?></a>
                            <a class="nav-tab" href="#settings"><?php _e('Settings', 'almefy-me') ?></a>
                            <a class="nav-tab" href="#howtouse"><?php _e('How to', 'almefy-me') ?></a>
                        </h2>

                        <div>
                            <div id="settings" class="tab">
                                <!-- BASIC -->
                                <?php require_once(__DIR__ . "/tab_02_settings.php") ?>
                                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'almefy-me') ?>">
                            </div>
                            
                            <div id="howtouse" class="tab">
                                <?php require_once(__DIR__ . "/tab_03_howtouse.php") ?>
                            </div>
                            
                            <!-- Default tab needs to be last in list for css to work -->
                            <div id="keysecret" class="tab">
                                <?php require_once(__DIR__ . "/tab_01_keysecret.php") ?>
                                <input style="margin-top: 1rem;" type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'almefy-me') ?>">
                            </div>
                        </div>
                    </form>
                </div>

                <script>
                    const tabs = document.querySelectorAll(".nav-tab");

                    let start_tab = document.querySelector(`a[href="${window.location.hash}"]`);
                    if(!start_tab) {
                        start_tab = document.querySelector(`.nav-tab`);
                    }
                    if(start_tab) {
                        start_tab.classList.add('nav-tab-active');
                    }

                    for(const tab of tabs) {
                        tab.addEventListener('click', () => {
                            for(const tab of tabs) {
                                tab.classList.remove('nav-tab-active');
                            }
                            tab.classList.add('nav-tab-active');
                        });
                    }
                </script>
            <?php
            }, $icon_data);
        });

        // Update the authenticationURL /////////////////////////////////////////////////

        // Update on key change ( used for plugin setup primarily  )
        add_action('update_option_almefy-api-key', function() {
            try {
                $configuration = AlmefyManager::$client->setConfiguration([
                    'authenticationUrl' => AlmefyConstants::AUTH_CONTROLLER()
                ]);
            } catch (\Throwable $th) {
                // This should only fail when the key or secret are incorrect. 
                // In other rare cases there is nothing we can do here.
            }
        });
        
        // Update on permalink settings change
        add_action('permalink_structure_changed', function() {
            try {
                $configuration = AlmefyManager::$client->setConfiguration([
                    'authenticationUrl' => AlmefyConstants::AUTH_CONTROLLER()
                ]);
            } catch (\Throwable $th) {
                // This should only fail when the key or secret are incorrect. 
                // In other rare cases there is nothing we can do here.
            }
        });
        
        add_action('admin_init', function () {
            $page = 'almefy';
            
            // add section
            add_settings_section(AlmefyConstants::$SECTION_KEY_SECRET, __('Key&Secret', 'almefy-me'), function () {
                // Settings Section Title
            }, $page);
            add_settings_section(AlmefyConstants::$SECTION_SETTINGS, __('Settings', 'almefy-me'), function () {
                // Settings Section Title
            }, $page);
            add_settings_section(AlmefyConstants::$SECTION_HOW_TO, __('How To', 'almefy-me'), function () {
                // Settings Section Title
            }, $page);

            // add setting to section
            $this->add_setting("almefy-api-key", AlmefyConstants::$SECTION_KEY_SECRET, "KEY", "", __("Key", 'almefy-me'), "");
            $this->add_setting("almefy-api-secret", AlmefyConstants::$SECTION_KEY_SECRET, "SECRET", "", __("Secret", 'almefy-me'), "");


            $this->add_setting("almefy-api-enabled", AlmefyConstants::$SECTION_SETTINGS, "BOOL", "1", __("Enable Almefy", 'almefy-me'), __("Enable/Disable Almefy for login to your website.", 'almefy-me'));
            // TODO:change from registration to "Send mail on registration"
            $this->add_setting("almefy-mail-connect-on-register", AlmefyConstants::$SECTION_SETTINGS, "BOOL", "0", __("Activation on Register", 'almefy-me'), __("Send the Almefy Connect email to newly registered users.", 'almefy-me'));
            $this->add_setting("almefy-mail-disable-welcome", AlmefyConstants::$SECTION_SETTINGS, "BOOL", "0", __("Disable 'Welcome' mail", 'almefy-me'), __("Disable the default Wordpress 'Welcome' mail sent by wordpress.", 'almefy-me'));
            $this->add_setting("almefy-connect-in-login", AlmefyConstants::$SECTION_SETTINGS, "BOOL", "0", __("Enable 'Connect Almefy' during login", 'almefy-me'), __("Allow users to activate 'Login with Almefy' in the [almefy-login] widget.", 'almefy-me'));
            // $this->add_setting("almefy-api-sandbox", AlmefyConstants::$SECTION_SETTINGS, "BOOL", "0", __("Sandbox Mode", 'almefy-me'), __("NEEDS TO BE DISCUSSED", 'almefy-me'));
            $this->add_setting("almefy-api-redirect", AlmefyConstants::$SECTION_SETTINGS, "TEXT", "", __("Redirect after login", 'almefy-me'), __("Decide to which page users should be redirected to after successfully logging in. <br>'/wp-admin' is the default.", 'almefy-me'), "/wp-admin");

            $this->add_setting("almefy-session-timeout", AlmefyConstants::$SECTION_SETTINGS, "SESSION", "12", __("Session timeout", 'almefy-me'), __("How many hours until users have to re-login?", 'almefy-me'), "/wp-admin");

        });
    }

    private function add_setting($setting_name, $section_name, $type, $default, $title, $description, $placeholder = "") {
        // TODO: register the settings in a static array, so they can be used by the uninstall script
        $page = "almefy";

        register_setting($page, $setting_name);

        add_settings_field($setting_name . "_field", $title, function() use($setting_name, $type, $default, $description, $placeholder) {
            $setting = get_option($setting_name, $default); 


            // Reasoning see 'pre_update_option_' below 
            if ($setting_name == "almefy-api-secret" && $setting != '') {
                $setting = AlmefyConstants::$SECRET_PLACEHOLDER;
            }

            ?>

            <label>
                <?php if($type == "BOOL"): ?>
                    <input type="checkbox" id="<?php echo esc_attr($setting_name) ?>" name="<?php echo esc_attr($setting_name) ?>" value="1" <?php echo esc_attr($setting) == 1 ? 'checked' : '' ?> />
                <?php elseif($type=="TEXT"): ?>
                    <input type="text" placeholder="<?php echo esc_attr($placeholder) ?>" id="<?php echo esc_attr($setting_name) ?>" name="<?php echo esc_attr($setting_name) ?>" value="<?php echo esc_attr($setting); ?>" />
                    <br>
                <?php elseif($type=="SESSION"): ?>
                    <input type="NUMBER" style="max-width: 8ch;" min="2" max="48" placeholder="<?php echo esc_attr($default) ?>" id="<?php echo esc_attr($setting_name) ?>" name="<?php echo esc_attr($setting_name) ?>" value="<?php echo esc_attr($setting); ?>" />
                    <br>
                <?php elseif($type=="KEY"): ?>
                    <input type="text" style="min-width: 65ch;" placeholder="<?php echo esc_attr($placeholder) ?>" id="<?php echo esc_attr($setting_name) ?>" name="<?php echo esc_attr($setting_name) ?>" value="<?php echo esc_attr($setting); ?>" />
                    <br>
                <?php elseif($type=="SECRET"): ?>
                    <input type="password" style="min-width: 65ch;" placeholder="<?php echo esc_attr($placeholder) ?>" id="<?php echo esc_attr($setting_name) ?>" name="<?php echo esc_attr($setting_name) ?>" value="<?php echo esc_attr($setting); ?>" />
                    <br>
                <?php else: ?>
                    INVALID SETTINGS TYPE
                <?php endif; ?>
                
                <span><?php echo $description ?></span>
            </label>

        <?php
        }, $page, $section_name, ["label_for" => $setting_name]);
    }
}
