<h3 class="almefy-h3"><?php _e("Login", 'almefy-me') ?></h3>
<p>
    <?php _e("To add the login code to your website add the <b>[almefy-login]</b> shortcode to your page.", 'almefy-me') ?><br>
    <?php _e("Alternatively you may add it via <b>do_shortcode('[almefy-login]');</b> to your theme.", 'almefy-me') ?><br>
    <br>
    <?php _e("If you would like to redirect users to the backend when logging in via the Wordpress login at `/wp-login.php`", 'almefy-me') ?><br>
    <?php _e("but to a profile page when logging in via the the frontend you have to:", 'almefy-me') ?><br>
    <?php _e("- Leave the `Redirect to after login` empty in the plugin settings", 'almefy-me') ?><br>
    <?php _e("- Set the 'redirect' variable on the shortcode", 'almefy-me') ?><br>
    <br>
    <?php _e('Example: <b>[almefy-login]</b>', 'almefy-me') ?><br>
    <?php _e('Example: <b>[almefy-login redirect="/replace-with-custom-page"]</b>', 'almefy-me') ?><br>
</p>

<h3 class="almefy-h3"><?php _e("Device Management", 'almefy-me') ?></h3>
<p>
    <?php _e("The built in device manager is found in the backend `Users -> Profile`.", 'almefy-me') ?><br>
    <?php _e("If you do not want your users to have access to the Wordpress backend but let them manage their devices,", 'almefy-me') ?><br>
    <?php _e("add the <b>[almefy-devices]</b> shortcode to a custom profile page.", 'almefy-me') ?><br>
</p>

<h3 class="almefy-h3"><?php _e("Connecting new devices", 'almefy-me') ?></h3>
<p>
    <?php _e("When creating an account, users will receive an email asking them to connect their device.", 'almefy-me') ?><br>
    <?php _e("Further devices may be connected in the backend `Users -> Profile`.", 'almefy-me') ?><br>
    <br>
    <?php _e("To add management options like adding and removing devices to the frontend of your website, ", 'almefy-me') ?><br>
    <?php _e("you may add a the device connection shortcode to a profile page using <b>[almefy-connect]</b>.", 'almefy-me') ?><br>
</p>

<h3 class="almefy-h3"><?php _e("Password-free Registration (Optional)", 'almefy-me') ?></h3>
<p>
    <?php _e("You may add password free registration by adding the <b>[almefy-register]</b> shortcode.", 'almefy-me') ?><br>
    <?php _e("Disabling `require_username` will result in users just being prompted for an email address.", 'almefy-me') ?><br>
    <?php _e("The button text is customizable by setting the `button_text` variable.", 'almefy-me') ?><br>
    <br>
    <?php _e('Example: <b>[almefy-register require_username="false" button_text="Sign Up Now!"]</b>', 'almefy-me') ?><br>
</p>
