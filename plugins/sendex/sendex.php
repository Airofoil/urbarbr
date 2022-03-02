<?php
/*
Plugin Name: Sendex
Plugin URI: https://urbarbr-1.local/
Description: A wordpress plugin for sending bulk SMS using Twilio
Version:  1.0.0
Author: Jae Choi
*/

//Basic Security
defined('ABSPATH') or die('Unauthorized Access');

// Required if your environment does not handle autoloading
require __DIR__ . '/vendor/autoload.php';

// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;

add_action( 'publish_post', 'sendex_publish_post' );

function sendex_publish_post($mobile, $name, $time) { 
    
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'ACe9f1967c03f7a7d20feb6aff4622cbfb';
    $token = '59793b894568093041a5e6346a10aa74';
    $client = new Client($sid, $token);
                        
    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
        // the number you'd like to send the message to
        $mobile,
        [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => '+19105699851',
            // the body of the text message you'd like to send
            'body' => 'Hi, '. $name .', ypu have an appointment tomorrow at '.$time.'.
             If you need to reach your Barber you can reply directly to this SMS and we will make sure they get it!
             Your Barber can also reach out to you in the same way. - Urbarbr Team'
        ]
    );
}

function complete_appointment($mobile) {
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'ACe9f1967c03f7a7d20feb6aff4622cbfb';
    $token = '59793b894568093041a5e6346a10aa74';
    $client = new Client($sid, $token);
                        
    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
        // the number you'd like to send the message to
        $mobile,
        [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => '+19105699851',
            // the body of the text message you'd like to send
            'body' => 'Your appointmrnt should now be complete. If you have any further questions or issues please reach out directly to UrBarbr and we will help get them resolved. Replies to this message will no longer be forwarded to your Barber but may be logged for quality and assurance purposes - Urbarbr Team'
        ]
    );
}