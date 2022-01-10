<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );
function urbarber_woocommerce_order_status_completed( $order_id ) {
	
    $to = 'jhan@jamesanthonyconsulting.com.au';
	$fromEmail = 'webmaster-external@jamesanthonyconsulting.com.au';
  	$subject = "Test booking request for order #" .$order_id. ". ";
  	$headers = 'From: '. $fromEmail . "\r\n" .'Reply-To: ' . $fromEmail . "\r\n";
	//$headers = array('Content-Type: text/html; charset=UTF-8');
	
	// Get an instance of the WC_Order object
	$order = wc_get_order( $order_id );
	
	$strings = array();
	//wp_mail($to, $subject, '1', $headers);
	$html = "<strong>Total cost:</strong> $" . $order->get_total();
	$html .= '<br><br>';
	
	$html .= '<strong>Address: </strong>';
	$html .= $order->get_billing_address_1() . ' ';
	$html .= $order->get_billing_address_2(). ' ';
	$html .= $order->get_billing_city(). ' ';
	$html .= $order->get_billing_state(). ' ';
	$html .= $order->get_billing_postcode(). ' ';
	$html .= $order->get_billing_country(). ' ';
	$html .= '<br><br>';
	
	foreach ( $order->get_items() as $item_id => $item ) { 
		
		foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
			$strings[] = $meta->key . ' ' . $meta->value;
		}
		if(count($strings)>0){
			$html .= '<strong>Service: </strong>';
			$html .= implode( ',', $strings );
			$html .= '<br><br>';
			
			$booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
			foreach ( $booking_ids as $booking_id ) {
				$booking = new WC_Booking( $booking_id );
				$booking_time = esc_html( sprintf( __( 'The booking will take place on %1$s.', 'woocommerce-bookings' ), $booking->get_start_date( null, null, wc_should_convert_timezone( $booking ) ) ) );
				$html .= $booking_time;
			}
		}
	}
		
	wp_mail($to, $subject, $html, $headers);

}
add_action( 'woocommerce_thankyou', 'urbarber_woocommerce_order_status_completed', 10, 1 );


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