<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );

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

//add_action( 'woocommerce_after_single_product_summary', 'comments_template', 50 );
add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
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