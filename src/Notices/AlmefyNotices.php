<?php
// <!-- key missing, errors, run the wizard, updates, news, key invalid -->

if (!defined('ABSPATH')) {
    exit;
}

// Show notice if sandbox mode is activated
add_action('admin_notices', function() {
    $sandbox_enabled = AlmefyManager::$api_url != AlmefyConstants::$API;
    if ($sandbox_enabled) {
    ?>
        <div class="notice notice-error">
                <p>
                <h1><?php _e( 'Almefy is in sandbox mode! Do not use in production.', 'almefy-me' ); ?></h1>
                <br>
            </p>

        </div>
    <?php
    }
});

// Show notice if key and secret are still not set
add_action('admin_notices', function () {
    $api_key = get_option('almefy-api-key');
    $api_secret = get_option('almefy-api-secret');

    if ($api_key == false && $api_secret == false) {
        ?>
        <div class="notice notice-info">
            <h3><?php _e("Notice: Please finish your Almefy setup", "almefy-me") ?></h3>
            <p>
                <?php printf(__("You are almost ready to use Almefy on your website.<br>Please follow the instructions <a href='%s'>here</a> to enable the Almefy Plugin.", "almefy-me"), esc_attr(admin_url('admin.php?page=almefy'))) ?>            
            </p>
        </div>
        <?php
    }
});