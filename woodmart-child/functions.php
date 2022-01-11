<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), filemtime(get_stylesheet_directory().'/style.css') );
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
		$html_addon = '';
		if(count($strings)>0){
			$html_addon .= '<strong>Service: </strong>';
			$html_addon .= implode( ',', $strings );
			$html_addon .= '<br><br>';
		}	
		$booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
		foreach ( $booking_ids as $booking_id ) {
			$booking = new WC_Booking( $booking_id );
			$booking_time = esc_html( sprintf( __( 'The booking will take place on %1$s.', 'woocommerce-bookings' ), $booking->get_start_date( null, null, wc_should_convert_timezone( $booking ) ) ) );
			$html_addon .= $booking_time;
			$html_addon .= '<br><br>';
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
	foreach($data_array as $to=>$content){
		wp_mail($to, $subject, $content[0], $headers, $content[1]);
	}

}
add_action( 'woocommerce_thankyou', 'urbarber_woocommerce_order_status_completed', 10, 1 );
//add_action( 'woocommerce_checkout_order_processed', 'urbarber_woocommerce_order_status_completed', 10, 1 );


function wpse27856_set_content_type(){
    return "text/html";
}
add_filter( 'wp_mail_content_type','wpse27856_set_content_type' );

/**
 * Moving the payments
 */
add_action( 'woocommerce_checkout_shipping', 'my_custom_display_payments', 20 );

/**
 * Displaying the Payment Gateways 
 */
function my_custom_display_payments() {
  if ( WC()->cart->needs_payment() ) {
    $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
    WC()->payment_gateways()->set_current_gateway( $available_gateways );
  } else {
    $available_gateways = array();
  }
  ?>
  <div id="checkout_payments">
    <h3><?php esc_html_e( 'Payment Method', 'woocommerce' ); ?></h3>
    <?php if ( WC()->cart->needs_payment() ) : ?>
    <ul class="wc_payment_methods payment_methods methods">
    <?php
    if ( ! empty( $available_gateways ) ) {
      foreach ( $available_gateways as $gateway ) {
        wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
      }
    } else {
      echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ) . '</li>'; // @codingStandardsIgnoreLine
    }
    ?>
    </ul>
  <?php endif; ?>
  </div>
<?php
}

/**
 * Adding the payment fragment to the WC order review AJAX response
 */
add_filter( 'woocommerce_update_order_review_fragments', 'my_custom_payment_fragment' );

/**
 * Adding our payment gateways to the fragment #checkout_payments so that this HTML is replaced with the updated one.
 */
function my_custom_payment_fragment( $fragments ) {
	ob_start();

	my_custom_display_payments();

	$html = ob_get_clean();

	$fragments['#checkout_payments'] = $html;

	return $fragments;
}

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