<?php
/*
Plugin Name: Custom API
Description: A wordpress plugin for customer data
Version:  1.0.0
Author: Jae Choi
*/

function customerData() {
    $chBooking = curl_init();
    $headers = array(
		'Accept: application/json',
		'Content-Type: application/json',
    );
	
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
	
	date_default_timezone_set('Australia/Adelaide');
	$date = date('Y-m-d H:i:s');
	$long = strtotime($date);

	$start = $jsonBooking[0]['start'];
	if (is_countable($jsonBooking)) {
		for ($i=0; $i < count($jsonBooking); $i++) { //1hr = 3600
			if (($jsonBooking[$i]['start'] < $long && $jsonBooking[$i]['end'] < $long) || $jsonBooking[$i]['status'] === 'cancelled' || $jsonBooking[$i]['status'] === 'unpaid') {
				array_splice($jsonBooking, $i, 1);
				$i = 0;
			}
		}
	}

	$customers = array();
	if (is_countable($jsonBooking) && is_countable($jsonOrder)) {
		for ($i=0; $i < count($jsonBooking); $i++) {
			$jsonBooking[$i]['start'] = $jsonBooking[$i]['start'] - 37800;
			$jsonBooking[$i]['end'] = $jsonBooking[$i]['end'] - 37800;
			for ($j=0; $j < count($jsonOrder); $j++) { 
				if ($jsonBooking[$i]['order_id'] === $jsonOrder[$j]['id']) {
					array_push($customers, $jsonOrder[$j]);
				}
			}
		}
	}

	if (is_countable($customers)) {
		for ($i=0; $i < count($customers); $i++) { 
			$customers[$i]['billing']['phone'] = str_replace(' ', '', $customers[$i]['billing']['phone']);
			if ($customers[$i]['billing']['phone'][0] === '0') {
				$customers[$i]['billing']['phone'] = '+61' . substr($customers[$i]['billing']['phone'], 1);
			}
			if ($customers[$i]['billing']['phone'][0] !== '+') {
				$customers[$i]['billing']['phone'] = '+' . strval($customers[$i]['billing']['phone']);
			}
		}
	}

	$customerData = array();
	if (is_countable($jsonBooking) && is_countable($jsonProduct)) {
		for ($i=0; $i < count($jsonBooking); $i++) { 
			for ($j=0; $j < count($jsonProduct); $j++) { 
				if ($jsonBooking[$i]['product_id'] === $jsonProduct[$j]['id']) {
					array_push($customerData, array(
						'first_name' => $customers[$i]['billing']['first_name'],
						'last_name' => $customers[$i]['billing']['last_name'],
						'phone' => $customers[$i]['billing']['phone'],
						'booking_id' => $jsonBooking[$i]['id'],
						'order_id' => $jsonBooking[$i]['order_id'],
						'product_id' => $jsonBooking[$i]['product_id'],
						'product_name' => $jsonProduct[$j]['name']
					));
				}
			}
		}
	}

	return $customerData;
}

add_action( 'rest_api_init', 'json_data_route');
function json_data_route() {
	register_rest_route( 'json_data/v1', '/customer_info', array(
		'methods' => 'GET',
		'callback' => 'customerData',
	) );
}