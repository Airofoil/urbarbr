<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */
defined( 'ABSPATH' ) || exit;

global $product;

$is_slider 		   = woodmart_loop_prop( 'is_slider' );
$is_shortcode 	   = woodmart_loop_prop( 'is_shortcode' );
$different_sizes   = woodmart_loop_prop( 'products_different_sizes' );
$hover 			   = woodmart_loop_prop( 'product_hover' );
$current_view      = woodmart_loop_prop( 'products_view' );
$shop_view 		   = woodmart_get_opt( 'shop_view' );
$xs_columns 	   = (int) woodmart_get_opt( 'products_columns_mobile' );

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

// Increase loop count
wc_set_loop_prop( 'loop', woodmart_loop_prop( 'woocommerce_loop' ) + 1 );
woodmart_set_loop_prop( 'woocommerce_loop', woodmart_loop_prop( 'woocommerce_loop' ) + 1 );
$woocommerce_loop = woodmart_loop_prop( 'woocommerce_loop' );

// Swatches
woodmart_set_loop_prop( 'swatches', woodmart_swatches_list() );

// Extra post classes
$classes = array( 'product-grid-item' );

if ( 'info' === $hover && ( woodmart_get_opt( 'new_label' ) && woodmart_is_new_label_needed( $product->get_id() ) ) || woodmart_get_product_attributes_label() || $product->is_on_sale() || $product->is_featured() || ! $product->is_in_stock() ) {
	$classes[] = 'wd-with-labels';
}

$classes[] = 'product';

if ( get_option( 'woocommerce_enable_review_rating' ) == 'yes' && $product->get_rating_count() > 0 && 'base' === $hover ) {
	$classes[] = 'has-stars';
}

if ( 'base' === $hover && ! woodmart_loop_prop( 'swatches' ) ) {
	$classes[] = 'product-no-swatches';
}

//Grid or list style
if ( $shop_view == 'grid' || $shop_view == 'list' )	$current_view = $shop_view;

if ( $is_slider ) $current_view = 'grid';

if ( $is_shortcode ) $current_view = woodmart_loop_prop( 'products_view' );

if ( $current_view == 'list' ){
	$hover = 'list';
	$classes[] = 'product-list-item'; 
	woodmart_set_loop_prop( 'products_columns', 1 );
} else {
	$classes[] = 'wd-hover-' . $hover;
	$classes[] = woodmart_get_old_classes( 'woodmart-hover-' . $hover );
}

if ( 'base' === $hover ) {
	wp_enqueue_script( 'imagesloaded' );
	woodmart_enqueue_js_script( 'product-hover' );
	woodmart_enqueue_js_script( 'product-more-description' );
}

if ( woodmart_get_opt( 'quick_shop_variable' ) ) {
	woodmart_enqueue_js_script( 'quick-shop' );
	wp_enqueue_script( 'wc-add-to-cart-variation' );
}

$xs_size = 12 / $xs_columns;

$products_columns = woodmart_loop_prop( 'products_columns' );

if ( $products_columns == 1 ) $xs_size = 12;

if( $different_sizes && in_array( $woocommerce_loop, woodmart_get_wide_items_array( $different_sizes ) ) ) woodmart_set_loop_prop( 'double_size', true );

if( ! $is_slider ){
	$classes[] = woodmart_get_grid_el_class( $woocommerce_loop , $products_columns, $different_sizes, $xs_size );
} elseif ( 'base' === $hover  ) {
	$classes[] = 'product-in-carousel';
}

woodmart_enqueue_product_loop_styles( $hover );





$product_id = $product->get_id();

$street = get_field( "barber_address" );
$city = get_field( "barber_city" );
$state = get_field( "barber_state" );
$country = get_field( "barber_country" );
$prefer_distance = get_field( "barber_distance" );

if($street != "" && $city != "" && $state != "" && $country != "" && $prefer_distance != "") {
	$geo_result = brrad_geocode($street, $city, $state, $country);
	$bar_lat = floatval ($geo_result['latitude']);
	$bar_long = floatval ($geo_result['longitude']);
}

$qualified = true;
if ($_GET) {
	$searching_location = $_GET['your-location'];
	$searching_service = $_GET['booking-services'];
	$searching_date = $_GET['booking-date'];
	$searching_time = $_GET['booking-time'];
	$filtering = false;
	$location_qualified = true;
	$searching_lat_long = $_GET['your-lat-long'];

	if($searching_location || $searching_service || $searching_date || $searching_lat_long){
		$filtering = true;
		$qualified = false;
	}else{
		$qualified = true;
	}

	if($searching_location) {

		$location_qualified = false;

		$goo_tem_address = str_replace(" ", "+", $searching_location);
		$goo_address = str_replace(",", "", $goo_tem_address);

		$url = "https://maps.googleapis.com/maps/api/geocode/json?address=$goo_address&key=AIzaSyBrFVuDdduHECkgQNAsFuv0XgBW-3jLw60&sensor=false"; 
		$google_api_response = wp_remote_get( $url );    

		$results = json_decode( $google_api_response['body'] ); //grab our results from Google
		$results = (array) $results; //cast them to an array
		$status = $results["status"]; //easily use our status
		$location_all_fields = (array) $results["results"][0];
		$location_geometry = (array) $location_all_fields["geometry"];
		$location_lat_long = (array) $location_geometry["location"];

		if( $status == 'OK'){
			$latitude = $location_lat_long["lat"];
			$longitude = $location_lat_long["lng"];
		}else{
			$latitude = '';
			$longitude = '';
		}

		$return = array(
			'latitude'  => $latitude,
			'longitude' => $longitude
		);

		$search_location_lat = floatval ($return['latitude']);
		$search_location_long = floatval ($return['longitude']);

		$search_distance_between = distance($search_location_lat, $search_location_long, $bar_lat, $bar_long, "K");

		if($search_distance_between < $prefer_distance) {
			$location_qualified = true;
			$qualified = true;
		}
	} elseif($searching_lat_long) { //use search input address instead of current location if there is an address in the search field

		$location_qualified = false;

		$lat_long_array = explode(',', $searching_lat_long);
		$location_lat = floatval ($lat_long_array[0]);
		$location_long = floatval ($lat_long_array[1]);

		// echo '<pre>'; print_r($bar_lat);  echo '</pre>';
		// echo '<pre>'; print_r($bar_long);  echo '</pre>';
		// echo '<pre>'; print_r($location_lat);  echo '</pre>';
		// echo '<pre>'; print_r($location_long);  echo '</pre>';

		$distance_between = distance($location_lat, $location_long, $bar_lat, $bar_long, "K");

		if($distance_between < $prefer_distance) {
			$location_qualified = true;
			$qualified = true;
		}
	}
	
	if($searching_service && $searching_service != "default"){

		$qualified = false;

		foreach ($product->get_meta_data() as $index => $data) { 
			if($data->key == '_product_addons'){
				foreach($data->value[0]['options'] as $index=>$value){
					if(trim(strtolower($value['label'])) == trim(strtolower(str_replace('_',' ',$searching_service))) && $location_qualified){
						$qualified = true;
					}
				}
			}
		}
	}elseif($searching_service== "default" && $location_qualified){
		$qualified = true;
	}

	if($searching_date && $searching_time){

		$qualified = false;

		$min_date = date('Y-m-d', strtotime($searching_date));
		$max_date = date('Y-m-d', strtotime($min_date . ' +1 day'));
		/* Don't convert search date time because Woo Bookings REST API does not recognize time, but only date*/
		$search_date_formatted = date("Y-m-d H:i", strtotime($searching_date . $searching_time));

		// echo '<pre>'; print_r($search_date_formatted);  echo '</pre>';
		// echo '<pre>'; print_r($min_date);  echo '</pre>';
		// echo '<pre>'; print_r($max_date);  echo '</pre>';
		
		// $url = get_site_url() . '/wp-json/wc-bookings/v1/products/slots?min_date=' . $min_date . '&max_date=' . $max_date . '&product_ids=' . $product_id;

		/* curl function not working on local, that needs to crawl staging/live site product data. Comment the above line and uncomment the below line to fetch from staging site */
		$url = 'https://staging-urbarbr.kinsta.cloud/wp-json/wc-bookings/v1/products/slots?min_date=' . $min_date . '&max_date=' . $max_date . '&product_ids=' . $product_id;

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$resp = curl_exec($curl);
		$resources = json_decode($resp, true);
		curl_close($curl);

		/* Don't convert search date time because Woo Bookings REST API does not recognize time, but only date*/
		// $search_date_formatted = date("Y-m-d H:i", strtotime($searching_date));

		$slots_info = $resources['records'];

		foreach($slots_info as $slot_info) {
			if ($slot_info['available'] == 1) {

				$start_time = str_replace("T"," ",$slot_info['date']);
				$end_time = date('Y-m-d H:i', strtotime($start_time) + 3600);

				if($search_date_formatted < $end_time && $start_time <= $search_date_formatted && $location_qualified){
					$qualified = true;
					break;
				}

			}
		}

	}
}

?>
<?php if($qualified){ ?>
<div <?php wc_product_class( $classes, $product ); ?> data-loop="<?php echo esc_attr( $woocommerce_loop ); ?>" data-id="<?php echo esc_attr( $product->get_id() ); ?>"><?php wc_get_template_part( 'content', 'product-' . $hover ); ?></div>
<?php } ?>