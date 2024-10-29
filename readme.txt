===  ALMEFY: Two-Factor Authentication in one step. Without password​ ===
Contributors: allbutsocial
Tags: login, signin, authentication, two-factor-authentication
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 0.16.5
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The Almefy Plugin enables secure 2FA login for your users – without passwords, just by scanning a QR code.​

== Description ==

## Welcome to the ALMEFY experience

Almefy enables a secure Two-Factor Authentication user experience in one step. No passwords needed, the users just scan to login.   

Your users just need to scan a QR code on your website with the Almefy App and login through our technology built on Identity Based Encryption (IBE). 
Almefy is the next generation of logins, taking the burden of secure passwords away from the users by eliminating them completely. 
We enable a secure, easy and fast Two-Factor Authentication in one step for the convenience of your users.

Learn more about the Identity Based Encryption and 2FA and Almefy on our [website](https://almefy.com).

## The ALMEFY HUB - Where you manage all accesses

We offer an easy to manage setup and management of accesses through our ALMEFY HUB. 
The HUB gives you the possibility to setup new websites, manage admins and users and offers statistics for your reference.
It is also the fastest way to get in touch with us in case you have any questions or need support!
And here is how you get started with your ALMEFY Plugin:

## Setup

To activate the plugin, please navigate to the Almefy settings in the backend.
You will be required to provide a key and a secret to connect to the Almefy servers.
To generate your individual key & secret, please contact us at https://almefy.com/contact/. 
We will send you an email with all instructions how to get access to the ALMEFY HUB where you generate your key & secret in a guided flow through our setup wizard.

In the settings of the plugin you can also define a `Redirect to after login` url.
Use a relative url like `/profile-page` to redirect users to a custom page.
Alternatively leave the page blank to redirect users to the Wordpress backend.

### Login

To add the login code to your website add the `[almefy-login]` shortcode to your page.
Alternatively you may add it via `do_shortcode('[almefy-login]');` to your theme.

If you would like to redirect users to the backend when logging in via the Wordpress login at `/wp-login.php`
but to a profile page when logging in via the the frontend you have to:
- Leave the `Redirect to after login` empty in the plugin settings
- Set the 'redirect' variable on the shortcode

Example: `[almefy-login redirect="/profile"]`

### Device Management

The built in device manager is found in the backend `Users -> Profile`.
If you do not want your users to have access to the Wordpress backend but let them manage their devices,
add the `[almefy-devices]` shortcode to a custom profile page.

### Connecting new devices

When creating an account, users will receive an email asking them to connect their device.
Further devices may be connected in the backend `Users -> Profile`.

To add management options like connecting and removing devices to the frontend of your website, 
you may include the device connection shortcode to a profile page using `[almefy-connect]`.

### Password-free Registration (Optional)

You may add password free registration by adding the `[almefy-register]` shortcode.
Disabling `require_username` will result in users just being prompted for an email address.
The button text is customizable by setting the `button_text` variable.

Example: `[almefy-register require_username="false" button_text="Sign Up Now!"]`;


## License

GNU General Public License v2.0 - GNU Project - Free Software Foundation
https://www.gnu.org

Contact - Almefy : 2FA Authentication in one step
https://almefy.com