<?php

class AlmefyMailer {

    // file, uid, name 
    public static $attachments = [];

    public function __construct() {

        $this->email_attacher();
    }

    public static function send_enrollment($email, $nickname = null) {

        if($nickname != null) {
            $nickname = sanitize_user($nickname);

            if($nickname == "") {
                $nickname = null;
            }
        }

        AlmefyManager::$client->enrollIdentity($email, [
            'nickname' => $nickname,
            'sendEmail' => true,
            'sendEmailTo' => $email,
        ]);

        return true;
        
        // https://stackoverflow.com/questions/15646187/display-inline-image-attachments-with-wp-mail
        $img = $enrollment_token->getBase64ImageData();

        $uid = AlmefyMailer::attach_img(base64_decode($img));
        $src = "cid:$uid";

        // TODO: Static image
        // $entity_img = "STATIC URL";
        // $entity_uid = AlmefyMailer::attach_img(base64_decode($entity_img));
        // $entity_src = "cid:$entity_uid";
        $entity_src = "STATIC URL";

        ob_start();
        require(dirname(__FILE__) . '/connect_device_template.php');
        $message = ob_get_clean();

        // TODO: custom email message filter hook
        // TODO: escape message as soon as site owners can set their own templates or options


        $message = self::parse_template($message, [
            // TODO: make custom setting. escape
            'entityName' => get_bloginfo('name'),
            // This is the enrollment QR code
            'provisionImageData' => $src,
            'identifier' => $email,
            // TODO: Make this an option in settings? escape
            'websiteUrl' => home_url(),
            // TODO: Maybe get data from API? escape
            'entityIconData' => get_site_icon_url(512, $entity_src),
            'mainMessage' => "Connect your customer account with your smartphone to log in password-free in the future. ",
            'code' => $enrollment_token->getId(),
        ]);

        $headers = array('Content-Type: text/html; charset=UTF-8;');

        $success = wp_mail($email, sprintf(__("Connect your device to %s.", "almefy-me"), get_bloginfo('name')), $message, $headers);
        return $success;
        // return true;
    }

    // Request an image to be attached to an email.
    public static function attach_img($content) {

        // Create a temporary email
        $uuid = wp_generate_uuid4();
        $file = get_temp_dir() . $uuid . '.png';
        file_put_contents($file, $content);

        AlmefyMailer::$attachments[] = [
            'file' => $file, 
            'uid' => $uuid,
            'name' => $uuid . '.png',
        ];

        // called when script finishes or exit is called
        register_shutdown_function(function() use($file) { 
            // delete file. @ represses error messages
            @unlink($file);
        });

        return $uuid;
    }

    // Check if images have to be attached to an email.
    public function email_attacher() {
        add_action( 'phpmailer_init', function( &$phpmailer ) {
            $phpmailer->SMTPKeepAlive=true;
            foreach ( AlmefyMailer::$attachments as $a ) {
                $phpmailer->AddEmbeddedImage( $a['file'], $a['uid'], $a['name'] );
            }
        });
    }

    public static function parse_template($template, $placeholders = []) {
        foreach ($placeholders as $placeholder => $replacement) {
            $template = preg_replace('/{{ *' . $placeholder . ' *}}/i', $replacement, $template);
        }

        return $template;
    }


}