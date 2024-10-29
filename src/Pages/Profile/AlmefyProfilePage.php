<?php

class AlmefyProfilePage
{
    public function __construct()
    {
        add_action('personal_options', function () {
            // current user_id being edited
            global $user_id;
            $user = get_user_by('id', $user_id);

            // Only show the info for currently logged in user
            if ($user_id != get_current_user_id()) {
                return;
            }

            ?>
                <div>
                    <div style="display: flex; gap: 2rem;">
                        <img height="32px" src="<?php echo esc_attr(AlmefyConstants::$LOGO_URL) ?>" alt="Almefy">
                    </div>
                    <p>
                        <?php _e('Almefy is a Two-Factor Authentication solution in one step - highly secure and without passwords.<br>Connect your device below to get started and login by scanning the QR code on the Login page going forward. No password needed<br><a target="_blank" rel="noopener" href="https://almefy.com/">Learn more</a>', 'almefy-me') ?>
                    </p>
                    <a class="button" href="<?php echo admin_url("users.php?page=almefy-device-manager") ?>"><?php _e("Manage Almefy Devices", "almefy-me") ?></a>                   
                </div>
            <?php
        });
    }
}
