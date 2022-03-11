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
	$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
	);
	$chBooking = curl_init();
	$urlBooking= 'https://staging-urbarbr.kinsta.cloud/wp-json/wc-bookings/v1/bookings?per_page=100&consumer_key=ck_5a1cb710eb2853f8f109830d2d3346b4fef4fd78&consumer_secret=cs_ec2aa8ae576eec5362416a93c3d57e504baca46d';
	curl_setopt($chBooking, CURLOPT_URL, $urlBooking);
	curl_setopt($chBooking, CURLOPT_RETURNTRANSFER, 1);
	$outputBooking = curl_exec($chBooking);
	try {
		$jsonBooking = json_decode($outputBooking, true, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		throw new EncryptException('Could not encrypt the data.', 0, $e);
	}
	curl_close($chBooking);

	$chOrder = curl_init();
		$urlOrder= 'https://staging-urbarbr.kinsta.cloud/wp-json/wc/v3/orders?per_page=100&consumer_key=ck_5a1cb710eb2853f8f109830d2d3346b4fef4fd78&consumer_secret=cs_ec2aa8ae576eec5362416a93c3d57e504baca46d';
	curl_setopt($chOrder, CURLOPT_URL, $urlOrder);
		curl_setopt($chOrder, CURLOPT_RETURNTRANSFER, 1);
	$outputOrder = curl_exec($chOrder);
	try {
		$jsonOrder = json_decode($outputOrder, true, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		throw new EncryptException('Could not encrypt the data.', 0, $e);
	}
	curl_close($chOrder);

	$chProduct = curl_init();
	$urlProduct= 'https://staging-urbarbr.kinsta.cloud/wp-json/wc/v3/products/?per_page=100&consumer_key=ck_5a1cb710eb2853f8f109830d2d3346b4fef4fd78&consumer_secret=cs_ec2aa8ae576eec5362416a93c3d57e504baca46d';
	curl_setopt($chProduct, CURLOPT_URL, $urlProduct);
	curl_setopt($chProduct, CURLOPT_RETURNTRANSFER, 1);
	$outputProduct = curl_exec($chProduct);
	try {
		$jsonProduct = json_decode($outputProduct, true, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		throw new EncryptException('Could not encrypt the data.', 0, $e);
	}
	curl_close($chProduct);

  	$barberList = array(
		"+61420603110" => "Barber-test", //barber names need to be replaced with store name later
		"+61416711229" => "Barber Abbey",
		"+61424713392" => "Barber Fritz"
	);

  	date_default_timezone_set('Australia/Adelaide');
	$date = date('Y-m-d H:i:s');
	$now = strtotime($date);
	$long = strtotime($date);

	if (is_countable($jsonBooking)) {
		$bookingData = array_values(array_filter($jsonBooking, function($item) {
			$item['start'] = $item['start'] - 37800;
			$now = strtotime(date('Y-m-d H:i:s'));
			if (($item['start'] - $now > -3600 && $item['start'] - $now < 86400) && ($item['status'] === 'complete' || $item['status'] === 'paid')) {
				return $item;
			}
		}));
	}
	
	if (is_countable($jsonOrder)) {
		$orderData = array_values(array_filter($jsonOrder, function($item) {
			$item['billing']['phone'] = str_replace(' ', '', $item['billing']['phone']);
			if ($item['billing']['phone'][0] === '0') {
				$item['billing']['phone'] = '+61' . substr($item['billing']['phone'], 1);
			}
			if ($item['billing']['phone'][0] !== '+' && strlen($item['billing']['phone']) > 0) {
				$item['billing']['phone'] = '+' . strval($item['billing']['phone']);
			}
			if ($item['billing']['phone'] === $_REQUEST['From']) {
				return $item;
			}
		}));
	}

	$customerName;
	$customerFullName;
	$fromCustomer = false;
	$filteredBookingData = array();
	if (is_countable($bookingData) && is_countable($orderData)) {
		foreach ($bookingData as $bookingItem) {
			foreach ($orderData as $orderItem) {
				if ($bookingItem['order_id'] === $orderItem['id']) {
					$orderItem['billing']['phone'] = str_replace(' ', '', $orderItem['billing']['phone']);
					if ($orderItem['billing']['phone'][0] === '0') {
						$orderItem['billing']['phone'] = '+61' . substr($orderItem['billing']['phone'], 1);
					}
					if ($orderItem['billing']['phone'][0] !== '+' && strlen($orderItem['billing']['phone']) > 0) {
						$orderItem['billing']['phone'] = '+' . strval($orderItem['billing']['phone']);
					}
					$fromCustomer = true;
					$customerName = $orderItem['billing']['first_name'];
					$customerFullName = $orderItem['billing']['first_name']." ".$orderItem['billing']['last_name'];
					array_push($filteredBookingData, $bookingItem);
				}
			}
		}
	}

	if (is_countable($filteredBookingData)) {
		if (count($filteredBookingData) > 1) {
			$filteredBookingData = array_reduce($filteredBookingData, function ($previous, $current) {
				if ($previous['start'] < $current['start']) {
					return $previous;
				} else {
					return $current;
				}
			});

			if (is_countable($orderData)) {
				foreach ($orderData as $orderItem) {
					if ($filteredBookingData[0]['order_id'] === $orderItem['id']) {
						$customerName = $orderItem['billing']['first_name'];
					}
				}
			}
		}
	}

	$barberName;
	if (is_countable($jsonProduct) && is_countable($filteredBookingData)) {
		foreach ($jsonProduct as $productItem) {
			if ($filteredBookingData[0]['product_id'] === $productItem['id']) {
				$barberName = $productItem['name'];
			}
		}
	}
  
	echo header('content-type: text/xml');
	$to = '+61420603110';
	$from = $_REQUEST['From'];
	$body = $_REQUEST['Body'];
  
  	if (!$fromCustomer) {
    	echo 
    	"<Response>
      		<Message to='".$to."'>
      		Hi " .$barberName.", " .$customerName. " has sent you the following message: 
      
".$body. " - " .$customerFullName."
       
To reploy to " .$customerName. ", reply directly to this message
        - UrBabr Team
      		</Message>
    	</Response>";
  	
	} else {
		$str = $_REQUEST['Body'];
		$separatorPosition = strpos($str, ':');
		$recipientNumber;
		$recipientName;
		$messageBody;
		
		if ($separatorPosition < 1) {
			echo 
			"<Response>
				<Message to='".$to."'>
					You need to specify a recipient's full name and a \":\" before the message.
				</Message>
			</Response>";
		} else {
			/*$recipientName = substr($str, 0, $separatorPosition);
			$messageBody = substr($str, $separatorPosition + 1);
			
			if (is_countable($jsonOrder)) {
				$tmpOrderData = array_values(array_filter($jsonOrder, function($item) use ($recipientName) {
					$fullName = $item['billing']['first_name']." ".$item['billing']['last_name'];
					if ($recipientName === $fullName) {
						return $item;
					}
				}));
			}
	
			if (is_countable($tmpOrderData) && is_countable($bookingData)) {
				foreach ($tmpOrderData as $productItem) {
					foreach ($bookingData as $bookingItem) {
						if ($productItem['id'] === $bookingItem['order_id']) {
							$productItem['billing']['phone'] = str_replace(' ', '', $productItem['billing']['phone']);
							if ($productItem['billing']['phone'][0] === '0') {
								$productItem['billing']['phone'] = '+61' . substr($productItem['billing']['phone'], 1);
							}
							if ($productItem['billing']['phone'][0] !== '+' && strlen($productItem['billing']['phone']) > 0) {
								$productItem['billing']['phone'] = '+' . strval($productItem['billing']['phone']);
							}
							$recipientNumber = $productItem['billing']['phone'];
							break;
						}
					}
				}
			}*/

			echo 
			"<Response>
				<Message to='".$to."'>
					Hi
				</Message>
			</Response>";
		}
	}
	die();
}
?>