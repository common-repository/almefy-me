<?php

if (!defined('ABSPATH')) {
    exit;
}

function settings_header()
{
    ob_start();
    ?>
    <div class="almefy-me-header">
        <div class="flex justify-between">
            <img class="almefy-me-header-logo" src="<?php echo esc_attr(AlmefyConstants::$LOGO_URL) ?> " alt="Almefy Logo">
            <div>
                <a href="<?php _e("https://almefy.com/contact/", 'almefy-me') ?>" rel="noopener" target="_blank" class="almefy-me-button-yellow"><?php _e('Help us be better', 'almefy-me') ?></a>
            </div>
        </div>
        <p>
            <?php _e('Almefy is a Two-Factor Authentication solution in one step - highly secure and without passwords.<br>Connect your device below to get started and login by scanning the QR code on the Login page going forward. No password needed<br><a target="_blank" rel="noopener" href="https://almefy.com/">Learn more</a>', 'almefy-me') ?>
        </p>
    </div>

    <?php
    return ob_get_clean();
}
