<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$options = [
    'almefy-api-key',
    'almefy-api-secret',
    'almefy-api-enabled',
    'almefy-mail-connect-on-register',
    'almefy-mail-disable-welcome',
    'almefy-connect-in-login',
    'almefy-api-redirect',
    'almefy-session-timeout',
];

foreach ($options as $option) {
    delete_option($option);
}
