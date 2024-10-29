<?php

if (!defined('ABSPATH')) {
    exit;
}

class AlmefyDeviceManagerPage {

    public $page_suffix = false;

    public function __construct() {
        $this->register_page();
    }

    public static function URL() {
        $redirect = get_site_url() . $_SERVER['REQUEST_URI'];

        // don't redirect back to the registration page
        if(strpos($redirect, 'wp-login.php') !== false) {
            $redirect = get_site_url();
        }

        return admin_url('/users.php?page=almefy-device-manager&redirect=' . $redirect);
    }

    private function register_page() {
        add_action('admin_menu', function () {
            $this->page_suffix = add_menu_page(
                __('My Almefy Devices', 'almefy-me'),
                __('My Almefy Devices', 'almefy-me'),
                'read',
                'almefy-device-manager',
                [$this, 'html'],
                'dashicons-smartphone'
            );
        });
    }

    public function html() {

        require_once(__DIR__ . '/../../Components/almefy_header.php');

        $redirect = home_url();
        // TODO: different default? Use the login redirect setting?
        if(isset($_GET['redirect'])) {
            $redirect = sanitize_url($_GET['redirect']);
        }

        ?>
        <div class="wrap">

            <div class="almefy-me-box" style="padding-top: 2rem;">
                <?php echo settings_header() ?>

                <?php _e("<b>Important: </b>By activating Almefy on your account you enable secure 2-Factor-Authentication in one step. <br>To do so, the login through username and password will be disabled. <br>Just use the <a href='https://almefy.com/products/almefyapp' target='_blank'>Almefy App</a> to login. Secure, Simple, Fast.", "almefy-me") ?>

                <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 2rem;">
                    
                    <div class="almefy-me-box almefy-me-max-w-500 m-0 flex flex-col almefy-me-device-page-col" >
                        <?php echo do_shortcode('[almefy-devices]') ?>
                    </div>

                    <div class="almefy-me-device-pageseparator almefy-me-device-page-col">
                        <?php echo do_shortcode('[almefy-connect]') ?>
                    </div>
        
                </div>
            </div>
        </div>
        <?php
    }
}