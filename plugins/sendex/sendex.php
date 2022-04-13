<?php
/*
Plugin Name: Sendex
Description: A wordpress plugin for sending bulk SMS using Twilio
Version:  1.0.0
Author: Jae Choi
*/

//Basic Security
defined('ABSPATH') or die('Unauthorized Access');

// Required if your environment does not handle autoloading
require __DIR__ . '/vendor/autoload.php';
use Twilio\TwiML\MessagingResponse;

// Use the REST API Client to make requests to the Twilio REST API
use Twilio\Rest\Client;

function sendex_publish_post($mobile, $name, $time) { 
    
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'AC48d70c672e1844c4bb6dd44a89bbf83c';
    $token = '194e0e9736a16a88eeb62cb72291ef20';
    $client = new Client($sid, $token);
                        
    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
        // the number you'd like to send the message to
        $mobile,
        [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => '+18606891709',
            // the body of the text message you'd like to send
            'body' => 'Hi, '. $name .', you have an appointment tomorrow at '.$time.'.
If you need to reach your Barber you can reply directly to this SMS and we will make sure they get it!
Your Barber can also reach out to you in the same way. 
            - Urbarbr Team'
        ]
    );
}

function reminder_barber($mobile, $barberName, $time, $customerName, $orderId) { 
    
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'AC48d70c672e1844c4bb6dd44a89bbf83c';
    $token = '194e0e9736a16a88eeb62cb72291ef20';
    $client = new Client($sid, $token);
                        
    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
        // the number you'd like to send the message to
        $mobile,
        [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => '+18606891709',
            // the body of the text message you'd like to send
            'body' => 'Hi, '. $barberName .', you have an appointment tomorrow at '.$time.' with '.$customerName. ' (#'.$orderId.').
If you need to reach your Customer you can reply directly to this SMS and we will make sure they get it!
Your Barber can also reach out to you in the same way. You need to specify a customer\'s order ID and a ":" before the message. (e.g #1234: Welcome to Urbarbr!)
            - Urbarbr Team'
        ]
    );
}

function complete_appointment_customer($mobile, $name, $barberName) {
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'AC48d70c672e1844c4bb6dd44a89bbf83c';
    $token = '194e0e9736a16a88eeb62cb72291ef20';
    $client = new Client($sid, $token);
                        
    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
        // the number you'd like to send the message to
        $mobile,
        [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => '+18606891709',
            // the body of the text message you'd like to send
            'body' => 'Hi '.$name.', 
Your appointment with '.$barberName.' should now be complete. If you have any further questions or issues please reach out directly to UrBarbr and we will help get them resolved. Replies to this message will no longer be forwarded to your Barber but may be logged for quality and assurance purposes 
                - Urbarbr Team'
        ]
    );
}

function complete_appointment_barber($mobile, $name, $customerName, $orderId) {
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'AC48d70c672e1844c4bb6dd44a89bbf83c';
    $token = '194e0e9736a16a88eeb62cb72291ef20';
    $client = new Client($sid, $token);
                        
    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
        // the number you'd like to send the message to
        $mobile,
        [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => '+18606891709',
            // the body of the text message you'd like to send
            'body' => 'Hi '.$name.', 
Your appointment with '.$customerName.' (#'.$orderId.') should now be complete. If you have any further questions or issues please reach out directly to UrBarbr and we will help get them resolved. Replies to this message will no longer be forwarded to your Customer but may be logged for quality and assurance purposes 
                - Urbarbr Team'
        ]
    );
}

function just_made_booking($mobile, $name, $time) { 
    
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'AC48d70c672e1844c4bb6dd44a89bbf83c';
    $token = '194e0e9736a16a88eeb62cb72291ef20';
    $client = new Client($sid, $token);
                        
    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
        // the number you'd like to send the message to
        $mobile,
        [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => '+18606891709',
            // the body of the text message you'd like to send
            'body' => 'Hi, '. $name .', you have an appointment at '.$time.' within 24 hours.
If you need to reach your Barber you can reply directly to this SMS and we will make sure they get it!
Your Barber can also reach out to you in the same way. 
            - Urbarbr Team'
        ]
    );
}

function just_made_booking_barber($mobile, $barberName, $time, $customerName, $orderId) { 
    
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'AC48d70c672e1844c4bb6dd44a89bbf83c';
    $token = '194e0e9736a16a88eeb62cb72291ef20';
    $client = new Client($sid, $token);
                        
    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
        // the number you'd like to send the message to
        $mobile,
        [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => '+18606891709',
            // the body of the text message you'd like to send
            'body' => 'Hi, '. $barberName .', you have an appointment at '.$time.' within 24 hours with '.$customerName. ' (#'.$orderId.').
If you need to reach your Customer you can reply directly to this SMS and we will make sure they get it!
Your Barber can also reach out to you in the same way. You need to specify a customer\'s order ID and a ":" before the message. (e.g #1234: Welcome to Urbarbr!)
            - Urbarbr Team'
        ]
    );
}

function test($now, $start) { 
    
    // Your Account SID and Auth Token from twilio.com/console
    $sid = 'AC48d70c672e1844c4bb6dd44a89bbf83c';
    $token = '194e0e9736a16a88eeb62cb72291ef20';
    $client = new Client($sid, $token);
                        
    // Use the client to do fun stuff like send text messages!
    $client->messages->create(
        // the number you'd like to send the message to
        '+61420603110',
        [
            // A Twilio phone number you purchased at twilio.com/console
            'from' => '+18606891709',
            // the body of the text message you'd like to send
            'body' => $now. ' / ' .$start
        ]
    );
}