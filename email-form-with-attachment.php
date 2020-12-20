<?php
/*
 * Plugin Name: WP VIP Email Form with Attachment
 * Description: Allows a form to send an email with an attachment in a wp vip setup.
 * Version: 0.1
 * Author: Patrick
 */

require_once __DIR__ . '/src/send-attachment-emailer.php';

// Init class
new Send_Attachment_Emailer();
