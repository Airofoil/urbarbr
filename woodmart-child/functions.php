<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), filemtime(get_stylesheet_directory().'/style.css') );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );

function my_theme_scripts() {
	wp_enqueue_script( 'child-theme', esc_url( get_stylesheet_directory_uri() ) . '/js/child-theme.js');
}
add_action( 'wp_enqueue_scripts', 'my_theme_scripts' );

function urbarber_woocommerce_order_status_completed( $order_id ) {
	
    $to = 'jhan@jamesanthonyconsulting.com.au'; //test account
	$fromEmail = 'webmaster-external@jamesanthonyconsulting.com.au';
  	$subject = "Test booking request for order #" .$order_id. ". ";
  	$headers = 'From: '. $fromEmail . "\r\n" .'Reply-To: ' . $fromEmail . "\r\n";
	
	// Get an instance of the WC_Order object
	$order = wc_get_order( $order_id );
	
	$html = "<strong>Total cost:</strong> $" . $order->get_total();
	$html .= '<br><br>';
	
	$html .= "8% service fee will be taken by UrBarbr: $" . ($order->get_total() * 0.08);
	$html .= '<br><br>';
	
	$html .= '<strong>Address: </strong>';
	$html .= $order->get_billing_address_1() . ' ';
	$html .= $order->get_billing_address_2(). ' ';
	$html .= $order->get_billing_city(). ' ';
	$html .= $order->get_billing_state(). ' ';
	$html .= $order->get_billing_postcode(). ' ';
	$html .= $order->get_billing_country(). ' ';
	$html .= '<br><br>';
	
	$data_array = array();
	
	foreach ( $order->get_items() as $item_id => $item ) { 
		$strings = array();
		$attachments = array();
		
		$product_id = $item['product_id'];
		
// 		$product = wc_get_product( $product_id );
		$product = $item->get_product();
		$barber_email = get_field( "barber_email",$item['product_id'] );
		
		foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
			$strings[] = $meta->key . ' ' . $meta->value;
		}
		wp_mail($to, $subject, '0', $headers);
		$html_addon = '';
		
		$html_addon .= '<strong>Customer name:</strong> '. $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		$html_addon .= '<br><br>';		
																		
		if(count($strings)>0){
			$html_addon .= '<strong>Service: </strong>';
			$html_addon .= implode( ',', $strings );
			$html_addon .= '<br><br>';
		}	
		
		$booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
		foreach ( $booking_ids as $booking_id ) {
			$booking = new WC_Booking( $booking_id );
			
			$html_addon .= '<strong>Booking status:</strong> '. wc_bookings_get_status_label( $booking->get_status());
			$html_addon .= '<br><br>';
			
			$product_id = $booking->get_product_id();
			$booking_product = get_wc_product_booking( $product_id );
			
			$resource = $booking_product->get_resource( $booking->get_resource_id() );	
			$html_addon .= '<strong>Barber name:</strong> ' . (is_object( $resource ) ? $resource->get_title() : '');
			$html_addon .= '<br><br>';
			
			$html_addon .= '<strong>Booking person:</strong> '. ($booking->has_persons() ? array_sum( $booking->get_persons() ) : 0);
			$html_addon .= '<br><br>';
			
			$booking_time = esc_html( sprintf( __( 'The booking will take place on %1$s.', 'woocommerce-bookings' ), $booking->get_start_date( null, null, wc_should_convert_timezone( $booking ) ) ) );
			$html_addon .= $booking_time;
			$html_addon .= '<br><br>';
// 			$html_addon .= '<strong>Time zone: </strong>'. wc_booking_get_timezone_string();
// 			$html_addon .= '<br><br>';
			
			$generate = new WC_Bookings_ICS_Exporter;
			$attachments[] = $generate->get_booking_ics( $booking );	
		}
		if (array_key_exists($barber_email, $data_array)){
		 	$new_html = $data_array[$barber_email][0] . '<br><br>' . $html_addon;
			array_push($data_array[$barber_email][1], $attachments);
		}else{
			$data_array[$barber_email] = [$html.''.$html_addon, $attachments];
		}
	}
	
	foreach($data_array as $barber_email=>$content){
		wp_mail($barber_email, $subject, $content[0], $headers, $content[1]);
	}

}
//add_action( 'woocommerce_thankyou', 'urbarber_woocommerce_order_status_completed', 10, 1 );
add_action( 'woocommerce_checkout_order_processed', 'urbarber_woocommerce_order_status_completed', 10, 1 );


function wpse27856_set_content_type(){
    return "text/html";
}
add_filter( 'wp_mail_content_type','wpse27856_set_content_type' );

function filter_gettext( $translated, $text, $domain  ) {
  if( $text == 'Your order' && is_checkout() && ! is_wc_endpoint_url() ) {
      // Loop through cart items
      foreach( WC()->cart->get_cart() as $cart_item ) {
          // Is virtual
          if ( $cart_item['data']->is_virtual() ) {
              $translated = __( 'Booking Details', $domain );
          }
      }
  }
  return $translated;
}
add_filter( 'gettext',  'filter_gettext', 10, 3 );

add_filter( 'woocommerce_add_to_cart_validation', 'bbloomer_only_one_in_cart', 9999, 2 );
function bbloomer_only_one_in_cart( $passed, $added_product_id ) {
   wc_empty_cart();
   return $passed;
}

//add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
function woo_remove_product_tabs( $tabs ) {
    unset( $tabs['reviews'] );  // Removes the reviews tab
    return $tabs;
}

function new_orders_columns( $columns = array() ) {
    // Hide the columns
    if( isset($columns['order-total']) ) {
        // Unsets the columns which you want to hide
        unset( $columns['order-number'] );
        unset( $columns['order-date'] );
        unset( $columns['order-status'] );
        unset( $columns['order-total'] );
        unset( $columns['order-actions'] );
    }
    // Add new columns
    $columns['order-number'] = __( 'Order', 'woocommerce' );
    $columns['rating'] = __( 'Rating', 'woocommerce' );
	$columns['order-total'] = __( 'Total', 'woocommerce' );
    $columns['order-actions'] = __( 'Actions', 'woocommerce' );

    return $columns;
}
add_filter( 'woocommerce_account_orders_columns', 'new_orders_columns' );

add_filter( 'woocommerce_order_button_text', 'woo_custom_order_button_text' ); 
function woo_custom_order_button_text() {
    return __( 'Pay Now', 'woocommerce' ); 
}

add_action( 'template_redirect', 'select_services' );
function select_services() {
  // Make sure the request is for a user-facing page
  if ( 
    ! is_singular() && 
    ! is_page() && 
    ! is_single() && 
    ! is_archive() && 
    ! is_home() &&
    ! is_front_page() 
  ) {
    return false;
  }

  // Otherwise do your thing
  ?><script>
	document.addEventListener("DOMContentLoaded", function(event) {
		const url = window.location.href;
		let hash = url.split('#')
		let items = hash.slice(1);
		let items_together = items[0].replace(/["'{}%2134567890:]/g, "");
		let services = items_together.split(',');

		for (const i in services) {
			document.querySelectorAll(`input[type='checkbox'][value=${services[i]}]`)[0].checked = true;
		}
	}); 
	 </script> 
  <?php  
}

/* Change the base Author url from '/author/' to '/profile/' - JDH * /
add_action('init', 'cng_author_base');
function cng_author_base() {
    global $wp_rewrite;
    $author_slug = 'profile';
    $wp_rewrite->author_base = $author_slug;
}*/