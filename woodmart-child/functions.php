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
    ! is_product()
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

// REGISTRATION SHORTCODE
function wc_registration_form_function() {
	if ( is_admin() ) return;
	if ( is_user_logged_in() ) return;
  
	ob_start();
  
	do_action( 'woocommerce_before_customer_login_form' );
  
	?>
	<div class="registration-wrapper">
		<h2 style="text-align: center;">Become a Member</h2>
		<p style="text-align: center;">Become a member - don't miss out on deals, offers, discounts and bonus vouchers</p>
		<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
	
			<?php do_action( 'woocommerce_register_form_start' ); ?>
	
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
	
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required"></span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
				</p>
	
			<?php endif; ?>
	
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required"></span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
			</p>
	
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
	
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required"></span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
				</p>
	
			<?php else : ?>
	
				<p><?php esc_html_e( 'A password will be sent to your email address.', 'woocommerce' ); ?></p>
	
			<?php endif; ?>
	
	
			<p class="woocommerce-form-row form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" style="width: 30%; border-radius: 3px" class="form-item-right woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Sign up', 'woocommerce' ); ?></button>
				<a class="form-item-left" style="text-decoration: underline;" href="/my-account">Login here</a>
			</p>
	
			<?php do_action( 'woocommerce_register_form_end' ); ?>
		</form>
	</div>
	<?php
	  
	return ob_get_clean();
	 
 }
 add_shortcode( 'wc_registration_form', 'wc_registration_form_function' );

/**
* @snippet       Hide Edit Address Tab @ My Account
* @how-to        Get CustomizeWoo.com FREE
* @author        Rodolfo Melogli
* @testedwith    WooCommerce 5.0
* @donate $9     https://businessbloomer.com/bloomer-armada/
*/
 
add_filter( 'woocommerce_account_menu_items', 'bbloomer_remove_address_my_account', 9999 );
 
function bbloomer_remove_address_my_account( $items ) {
	// unset( $items['dashboard'] );
	// unset( $items['downloads'] );
	// unset( $items['payment-methods'] );
	// unset( $items['downloads'] );
	// unset( $items['bookings'] );

	$items = array(
		'edit-account'    => __( 'My Profile', 'woocommerce' ),
		// 'edit-address'    => _n( 'My Addresses', 'Address', (int) wc_shipping_enabled(), 'woocommerce' ),
		'orders'          => __( 'My Orders', 'woocommerce' ),
		'wishlist'   	  => __( 'My Wishlist', 'woocommerce' ),
		'my-review'   	  => __( 'My Review', 'woocommerce' ),
		'customer-logout' => __( 'Logout', 'woocommerce' ),
	);

   return $items;
}

/**
* @snippet       Rename Edit Address Tab @ My Account
* @how-to        Get CustomizeWoo.com FREE
* @author        Rodolfo Melogli
* @testedwith    WooCommerce 5.0
* @donate $9     https://businessbloomer.com/bloomer-armada/
*/
 
// add_filter( 'woocommerce_account_menu_items', 'bbloomer_rename_address_my_account', 9999 );
 
// function bbloomer_rename_address_my_account( $items ) {
// //    $items['edit-account'] = 'My Profile';
// //    $items['orders'] = 'My Orders';
//    $items['wishlist'] = 'My Wishlist';
//    return $items;
// }

/**
 * @snippet       WooCommerce Add New Tab @ My Account
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 5.0
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
  
// ------------------
// 1. Register new endpoint (URL) for My Account page
// Note: Re-save Permalinks or it will give 404 error
  
function bbloomer_add_my_review_endpoint() {
    add_rewrite_endpoint( 'my-review', EP_ROOT | EP_PAGES );
}
  
add_action( 'init', 'bbloomer_add_my_review_endpoint' );
  
// ------------------
// 2. Add new query var
  
function bbloomer_my_review_query_vars( $vars ) {
    $vars[] = 'my-review';
    return $vars;
}
  
add_filter( 'query_vars', 'bbloomer_my_review_query_vars', 0 );
  
// ------------------
// 3. Insert the new endpoint into the My Account menu
  
function bbloomer_add_my_review_link_my_account( $items ) {
    $items['my-review'] = 'My Review';
    return $items;
}
  
add_filter( 'woocommerce_account_menu_items', 'bbloomer_add_my_review_link_my_account' );
  
// ------------------
// 4. Add content to the new tab
  
function bbloomer_my_review_content() {
   echo '<h3>My Review</h3>';
//    echo do_shortcode( ' /* your shortcode here */ ' );
	echo '<p>Coming soon</p>';
}
  
add_action( 'woocommerce_account_my-review_endpoint', 'bbloomer_my_review_content' );
// Note: add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format

/**
* @snippet       Merge Two "My Account" Tabs @ WooCommerce Account
* @how-to        Get CustomizeWoo.com FREE
* @author        Rodolfo Melogli
* @compatible    WooCommerce 5.0
* @donate $9     https://businessbloomer.com/bloomer-armada/
*/
 
// -------------------------------
// 1. First, hide the tab that needs to be merged/moved (edit-address in this case)
 
// add_filter( 'woocommerce_account_menu_items', 'bbloomer_remove_address_my_account', 999 );
 
// function bbloomer_remove_address_my_account( $items ) {
//    unset( $items['edit-address'] );
//    return $items;
// }
 
// -------------------------------
// 2. Second, print the ex tab content (woocommerce_account_edit_address) into an existing tab (woocommerce_account_edit-account_endpoint). See notes below!
 
add_action( 'woocommerce_account_edit-account_endpoint', 'woocommerce_account_edit_address' );
 
// NOTES
// 1. to select a given tab, use 'woocommerce_account_ENDPOINTSLUG_endpoint' hook
// 2. to print a given tab content, use any of these:
// 'woocommerce_account_orders'
// 'woocommerce_account_view_order'
// 'woocommerce_account_downloads'
// 'woocommerce_account_edit_address'
// 'woocommerce_account_payment_methods'
// 'woocommerce_account_add_payment_method'
// 'woocommerce_account_edit_account'

add_action( 'woocommerce_before_calculate_totals', 'custom_cart_items_prices', 10, 1 );
function custom_cart_items_prices( $cart ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    // Loop through cart items
    foreach ( $cart->get_cart() as $cart_item ) {

		// echo '<pre>'; print_r($cart_item);  echo '</pre>';

        // Get an instance of the WC_Product object
        $product = $cart_item['data'];

		// echo '<pre>'; print_r($product);  echo '</pre>';

        // Get the product name (Added Woocommerce 3+ compatibility)
        $original_name = method_exists( $product, 'get_name' ) ? $product->get_name() : $product->post->post_title;

        // SET THE NEW NAME
        $new_name = 'mydesiredproductname';

		if (array_key_exists("type", $cart_item['booking']) && !empty($cart_item['booking']['type']))
		{
		// echo '<pre>'; print_r("Key & Value exists!");  echo '</pre>';
			$new_name = $cart_item['booking']['type'];
		}
		else
		{
			$new_name = $original_name;
			// echo '<pre>'; print_r("Key or Value does not exist!");  echo '</pre>';
		}

        // Set the new name (WooCommerce versions 2.5.x to 3+)
        if( method_exists( $product, 'set_name' ) )
            $product->set_name( $new_name );
        else
            $product->post->post_title = $new_name;
    }
}

function my_custom_js_css() {
    echo '<script src="wp-content/themes/woodmart-child/js/jquery.datetimepicker.js"></script><link rel="stylesheet" type="text/css" href="wp-content/themes/woodmart-child/jquery.datetimepicker.css"/><script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.25.1/moment.min.js"></script>
';
}
add_action( 'wp_head', 'my_custom_js_css' );