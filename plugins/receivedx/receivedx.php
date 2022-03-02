<?php
/**
 * @package Receivedx
 * @version 1.0.0
 */
/*
Plugin Name: Receivedx
Description: This plugin helps to make 2-way-messaging in WordPress work.  
Author: Onwuka Gideon
Version: 1.0.0
Author URI: https:twitter.com/@onwuka_gideon
*/

// Our code will go here
/**
 * Register the receive message route.
 *
 * @since    1.0.0
 */
add_action( 'rest_api_init', 'register_receive_message_route');
function register_receive_message_route() {
    register_rest_route( 'receivedx/v1', '/receive_sms', array(
      'methods' => 'POST',
      'callback' => 'trigger_receive_sms',
    ) );
}

function trigger_receive_sms() {
    $mobile = '+61420603110';
    echo header('content-type: text/xml');
  
    echo <<<RESPOND
    <?xml version="1.0" encoding="UTF-8"?>
    <Response>
      <Message>{{From}}: {{Body}}</Message>
    </Response>
    RESPOND;
    die();
  }
?>