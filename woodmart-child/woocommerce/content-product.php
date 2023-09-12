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


if (!is_wc_booking_product($product)) return; // Skip if the product is not a bookable product


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

$searching_service = null;
$searching_date = null;
$searching_time = null;
$searching_lat_long = null;

if ($_GET) {

	$searching_service = 	!empty($_GET['booking-services']) ? $_GET['booking-services'] : null;
	$searching_date =    	!empty($_GET['booking-date']) ? $_GET['booking-date'] : null;
	$searching_time =    	!empty($_GET['booking-time']) ? $_GET['booking-time'] : null;
	$searching_lat_long = 	!empty($_GET['your-lat-long']) ? $_GET['your-lat-long'] : null;
	


	/*-For printing the service types on the barber card:
	if ($searching_service && !in_array($searching_service, array("all", "default"))) {

		$service_string = '';

		foreach ($product->get_meta_data() as $index => $data) {
			if ($data->key == '_product_addons') {
				if ($data->value && $data->value[0] && $data->value[0]['options']) {
					foreach ($data->value[0]['options'] as $index => $value) {
						$service_string .= str_replace(' ','_',trim(strtolower($value['label'])));

						if ($index !== array_key_last($data->value[0]['options'])) $service_string .= ','; // Add a comma
					}
				}
			}
		}
	} */
	if ($searching_service && !in_array($searching_service, array("all", "default"))) { // Check if the looped barber has the Service that has been searched for

		$qualified = false;

		$services = array_map('trim', explode(',', strtolower($searching_service))); // In case multiple services are selected, create a trimmed, lowercase array with them
		
		//testing--echo '.1.____' . strtolower($searching_service);
		//testing--var_dump($services);
		//testing--echo '____.2.____';

		foreach ($product->get_meta_data() as $index => $data) {
			if ($data->key == '_product_addons') {
				if ($data->value && $data->value[0] && $data->value[0]['options'])
					foreach ($data->value[0]['options'] as $index=>$value) {
						//testing--echo str_replace(' ','_',trim(strtolower($value['label']))) . ' ? ' . in_array(str_replace(' ','_',trim(strtolower($value['label']))), $services);
						if (in_array(str_replace(' ','_',trim(strtolower($value['label']))), $services)) {
							$qualified = true; // The barber has this service listed
						}
					}
			}
		}
	} elseif (in_array($searching_service, array("all", "default")) && $location_qualified) { // If searched service is "all" or "default"
		$qualified = true;
	}



	if ($searching_date) { // If searching on a particular date, set these variables to be displayed in the output
		
		$min_date = date('Y-m-d', strtotime($searching_date));
		$max_date = date('Y-m-d', strtotime($min_date . ' +1 day'));
		if ($searching_time) $search_date_formatted = date("Y-m-d H:i", strtotime($searching_date . $searching_time)); // Don't convert search date time because Woo Bookings REST API does not recognize time, but only date
		else $search_date_formatted = date("Y-m-d H:i", strtotime($searching_date));

		//-$availability = get_post_meta( $product_id, '_wc_booking_availability', true );

	}



	if ($searching_lat_long) { // If searching with lat & long, calculate and set the distance between the barber and the searched location

		$distance_between = '';

		$lat_long_array = explode(',', $searching_lat_long);
		$location_lat = floatval ($lat_long_array[0]);
		$location_long = floatval ($lat_long_array[1]);

		// echo '<pre>'; print_r($bar_lat);  echo '</pre>';
		// echo '<pre>'; print_r($bar_long);  echo '</pre>';
		// echo '<pre>'; print_r($location_lat);  echo '</pre>';
		// echo '<pre>'; print_r($location_long);  echo '</pre>';

		if (!empty($bar_lat) && !empty($bar_long)) { // If the barber's location is set, and the lat & long are found - JDH
			$distance_between = distance($location_lat, $location_long, $bar_lat, $bar_long, "K");
		}
	}

}



?>
<?php if ($_GET && $qualified) { //echo $location_lat . "2:" . $location_long . "3:" . $bar_lat . "4:" . $bar_long . "5:" . distance($location_lat, $location_long, $bar_lat, $bar_long, "K");	?>
	<div style="display:none; opacity:0; transition:opacity 3s;"
		<?php wc_product_class( $classes, $product ); ?> 
		data-loop="<?php echo esc_attr( $woocommerce_loop ); ?>"
		<?php
		if (!empty($min_date) && !empty($max_date) ) { // Add the min and max date (calculations in child-theme.js)
			echo ' data-mindate="' . $min_date . '"';
			echo ' data-maxdate="' . $max_date . '"';
			echo ' data-formatteddate="' . $search_date_formatted . '"';
		}
		if (isset($distance_between)) 
			echo ' data-distance="' . round($distance_between, 2) . '"'; 	// Add the barber's distance
		/*-To print the barber's services on their card:
		if (!empty($service_string)) 
			echo ' data-services="' . $service_string . '"'; 	// Add the services the barber provides */
		?>
		data-id="<?php echo esc_attr( $product->get_id() ); ?>">
		
		<?php wc_get_template_part( 'content', 'product-' . $hover ); ?>
	</div>
<?php } elseif ($qualified) { //-var_dump(get_post_meta( $product_id, '_wc_booking_availability', true )); ?>
	<div 
		<?php wc_product_class( $classes, $product ); ?> 
		data-loop="<?php echo esc_attr( $woocommerce_loop ); ?>" 
		data-id="<?php echo esc_attr( $product->get_id() ); ?>">
		<?php wc_get_template_part( 'content', 'product-' . $hover ); ?>
	</div>
<?php } ?>