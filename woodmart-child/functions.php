<?php
/**
 * Enqueue script and styles for child theme
 */

/* ++Must be at the top of this file: */
function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *"); //IMPORTANT: This must be changed to the urbarbr site when moving onto production, as this will be a security issue
}
add_action('init','add_cors_http_header');

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
		<!-- ----<p style="text-align: center;">Become a member - don't miss out on deals, offers, discounts and bonus vouchers</p> -->
		<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
	
			<?php do_action( 'woocommerce_register_form_start' ); ?>
	
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
	
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username">Name&nbsp;<span class="required"></span></label>
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
	
			<p class="woocommerce-form-row form-row last">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" style="max-width: 110px;" class="form-item-right woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Sign up', 'woocommerce' ); ?></button>
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
   echo '<h3 class="my-reviews-title">My Reviews</h3>';
//    echo do_shortcode( ' /* your shortcode here */ ' );
	// echo '<p>Coming soon</p>';
	$user_id = get_current_user_id();
    // $recent_comments = get_comments( array(
	// 	// 'number'    => -1,
	// 	'status'    => 'approve',
	// 	'user_id' => $user_id
    // ) );

	$args = array(
		'orderby' => 'date',
		'post_type' => 'product',
		// 'number' => '2',
		'post_author' => $user_id
	);

	$comments = get_comments($args);

	foreach($comments as $comment) :

		$jac_comment_date = date("Y/m/d", strtotime($comment->comment_date));

		// echo '<div class="my-reviews-all">';
		echo '<div class="my-reviews-all"><div class="reviews-barber-pic reviews-first-col"><img src="'.wp_get_attachment_url( get_post_thumbnail_id($comment->comment_post_ID) ).'"></div>';

		echo '<div class="reviews-sec-col"><div class="reviews-first-row"><div class="reviews-in-first-col">';
		echo '<div class="barber-title"><a href="'.post_permalink($comment->comment_post_ID).'" target="_blank">'.get_the_title($comment->comment_post_ID).'</a></div>';
		echo('<div class="star-rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating"><span style="width:' . ( get_comment_meta( $comment->comment_ID, 'rating', true ) / 5 ) * 100 . '%"><strong itemprop="ratingValue">' . get_comment_meta( $comment->comment_ID, 'rating', true ) . '</strong></span></div><br />');
		echo '</div><div class="reviews-in-sec-col">' . get_day_name($jac_comment_date) . '</div></div>';

		echo '<div class="reviews-sec-row">' . $comment->comment_content . '</div></div></div>';
		// echo '</div>';
	endforeach;

	// echo '<pre>'; print_r($comments);  echo '</pre>';

	// echo '<pre>'; print_r($recent_comments);  echo '</pre>';

    // echo '<ul>';
    // foreach($recent_comments as $recent_comment) {
	// 	echo '<li>';
	// 	echo wp_get_attachment_url( get_post_thumbnail_id($recent_comment->comment_post_ID) );
	// 	echo '<a href="'.get_comment_link($recent_comment).'" target="_blank">'.get_the_title($recent_comment->comment_post_ID).'</a>';
	// 	echo $recent_comment->comment_content;
	// 	echo $recent_comment->comment_date;
	// 	echo '</li>';

	// }
	// echo '</ul>';
}

function get_day_name($date) {

    // $date = date('Y/m/d', $timestamp);

    if($date == date('Y/m/d')) {
      $date = 'Today';
    } 
    else if($date == date('Y/m/d',strtotime("-1 days"))) {
      $date = 'Yesterday';
    }
	else if($date == date('Y/m/d',strtotime("-2 days"))) {
		$date = '2 days ago';
	}
	else if($date == date('Y/m/d',strtotime("-3 days"))) {
		$date = '3 days ago';
	}
	else if($date == date('Y/m/d',strtotime("-4 days"))) {
		$date = '4 days ago';
	}
	else if($date == date('Y/m/d',strtotime("-5 days"))) {
		$date = '5 days ago';
	}
	else if($date == date('Y/m/d',strtotime("-6 days"))) {
		$date = '6 days ago';
	}
	else if($date == date('Y/m/d',strtotime("-7 days"))) {
		$date = '7 days ago';
	}
    return $date;
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
    echo '<script src="' . get_stylesheet_directory_uri() . '/xdsoft_datetimepicker/jquery.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script src="' . get_stylesheet_directory_uri() . '/xdsoft_datetimepicker/jquery.datetimepicker.full.min.js"></script>
	<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/xdsoft_datetimepicker/jquery.datetimepicker.css">
	<script>
		$(document).ready(function() {
			$("#booking-date-search").datetimepicker();
		});
	</script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/css/bootstrap-select.min.css">
	
	<!-- --<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css">-->
 	<!-- --<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>-->
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>
';
}
/*
echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>
	<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/xdsoft_datetimepicker/jquery.datetimepicker.css">
	<script src="' . get_stylesheet_directory_uri() . '/xdsoft_datetimepicker/jquery.js"></script>
	<script src="' . get_stylesheet_directory_uri() . '/xdsoft_datetimepicker/jquery.datetimepicker.full.min.js"></script>';
*/
add_action( 'wp_head', 'my_custom_js_css' );

/**
 * @snippet       Redirect to Checkout Upon Add to Cart - WooCommerce
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    Woo 3.8
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
  
add_filter( 'woocommerce_add_to_cart_redirect', 'bbloomer_redirect_checkout_add_cart' );
 
function bbloomer_redirect_checkout_add_cart() {
   return wc_get_checkout_url();
}

add_filter( 'wc_add_to_cart_message', 'my_add_to_cart_function', 10, 2 ); 
function my_add_to_cart_function( $message, $product_id ) { 
    $message = sprintf(esc_html__('%s selected successfully.','woocommerce'), get_the_title( $product_id ) ); 
    return $message; 
}

add_action( 'woocommerce_checkout_before_order_review_heading', 'bbloomer_checkout_step3' );
function bbloomer_checkout_step3( $cart ) {

   	global $woocommerce;
    $items = $woocommerce->cart->get_cart();

	foreach($items as $item => $values) { 

		$p_booking_date = date('d F, Y', strtotime($values['booking']['_date']));
		$p_booking_time = $values['booking']['time'];

		echo '<div class="jac-booking-date-time"><div class="booking-date">' . $p_booking_date . '</div><div class="booking-time">' . $p_booking_time . '</div></div>';
	} 
}

add_action( 'check_booking_time', 'cw_function' );
function cw_function() {
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
	if (!$jsonBooking) return;//++
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
	if (!$jsonBooking) return;//++
	curl_close($chProduct);

	$barberList = array(
		"test07" => "+61420603110",
		"test06" => "+61420603110",
		"Hoochie" => "+61420603110",
		"test05" => "+61420603110",
		"test04" => "+61420603110",
		"Test02" => "+61420603110",
		"test01" => "+61420603110",
		"test00" => "+61420603110",
		"Test 100" => "+61420603110",
		"test66" => "+61420603110",
		"Ben's Server Barber" => "+61420603110",
		"Ben's locals" => "+61420603110",
		"Ben's Local barbershop" => "+61420603110",
		"Barber Jo" => "+61420603110",
		"New-test" => "+61420603110",
		"Barber-test" => "+61420603110",
		"JAC Product" => "+61420603110",
	);

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

	if (is_countable($jsonBooking)) {
		for ($i=0; $i < count($jsonBooking); $i++) {  //SMS reminder 24 hours before a booking time
			for ($j=0; $j < count($jsonProduct); $j++) { 
				if ($jsonBooking[$i]['product_id'] === $jsonProduct[$j]['id']) {
					if (($jsonBooking[$i]['start'] - $long) > 86370 && ($jsonBooking[$i]['start'] - $long) < 86430) {
						//$tmpCustomerFullName = $customers[$i]['billing']['first_name']." ".$customers[$i]['billing']['last_name'];
						//wp_mail( 'ghjgjh0107@gmail.com', $customers[$i]['billing']['first_name'], $customers[$i]['billing']['phone'] );
						sendex_publish_post($customers[$i]['billing']['phone'], $customers[$i]['billing']['first_name'], date('H:i', $jsonBooking[$i]['start']));
						reminder_barber($barberList[$jsonProduct[$j]['name']], $jsonProduct[$j]['name'], date('H:i', $jsonBooking[$i]['start']), $customers[$i]['billing']['first_name'], $jsonBooking[$i]['order_id']);
					}
				}
			}
		}

		for ($i=0; $i < count($jsonBooking); $i++) {  //Complete the appointment
			for ($j=0; $j < count($jsonProduct); $j++) { 
				if ($jsonBooking[$i]['product_id'] === $jsonProduct[$j]['id']) {
					if (($jsonBooking[$i]['end'] - $long) > -30 && ($jsonBooking[$i]['end'] - $long) < 30) {
						//wp_mail( 'ghjgjh0107@gmail.com', 'complete appointment', $customers[$i]['billing']['phone'] );
						complete_appointment_customer($customers[$i]['billing']['phone'], $customers[$i]['billing']['first_name']);
						complete_appointment_barber($barberList[$jsonProduct[$j]['name']], $jsonProduct[$j]['name']);
					}
				}
			}
		}
	}
	//wp_mail( 'ghjgjh0107@gmail.com', $jsonBooking[0]['status'], $customers[0]['billing']['phone'] );
}


function calculate_distance($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'kilometers') {
	$theta = $longitude1 - $longitude2; 
	$distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
	$distance = acos($distance); 
	$distance = rad2deg($distance); 
	$distance = $distance * 60 * 1.1515; 
	switch($unit) { 
	  case 'miles': 
		break; 
	  case 'kilometers' : 
		$distance = $distance * 1.609344; 
	} 
	return (round($distance,2)); 
  }

function edit_availability_slots_by_location( $available_blocks, $blocks ) {
// Split html into array blocks
$available_arr= explode("</li>",$available_blocks);

// get current user lat and lng


// get current user coordiantes
$user_ip = getenv('REMOTE_ADDR');
$geo = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=$user_ip"));
$latitude_current_user = $geo['geoplugin_latitude'];
$longitude_current_user = $geo['geoplugin_longitude'];


if ($latitude_current_user == null || $longitude_current_user == null) {
	$latitude_current_user = -34.925621825287166;
	$longitude_current_user = 138.60092004661487;
}

// might be able to use the rest api to get the bookings for today -> location can defineitley get the address -> just need access to the product id - maybe can user url to get id of product

$latitude_previous_booking = -34.925621825287166;
$longitude_previous_booking =  138.60092004661487;
$slug = basename(get_permalink());


// set previous block time to a time string so no errors occur
$previous_block_time = '12:01:02';
$time_blocks_to_hide = 0;
$previous_block_manually_hidden = false;

foreach ($available_arr as $key=>$block) {
	// TODO get location of the barber during previous 

	// Check if there need to be more time blocks hidden decided by distance previously, otherwise continue 
	if ($time_blocks_to_hide > 0) {
		//used data block val to cehck correct time
		$data_block_val = explode("data-block=",$block)[1];
		unset($available_arr[$key]);
		$time_blocks_to_hide--;
		$previous_block_manually_hidden = true;
	}
	else {
		// get time of html values
		$data_block_val = explode("data-block=",$block)[1];
		$data_block_val_new = explode(">",$data_block_val)[0];

		$data_value =   explode("data-value=",$block)[1];
		$data_value = explode("T",$data_value)[1];
		$old_time = explode("+",$data_value)[0];

		// check if previous block is booked
		$previous_check_time = date('H:i:s', strtotime("-15 minutes", strtotime($old_time)));
		
		// prevent the first block from starting late 
		if ($key == 0) {
			$previous_block_time = $previous_check_time;
		}

		//calculate distance to travel
		$distance = calculate_distance($latitude_current_user, $longitude_current_user, $latitude_previous_booking, $longitude_previous_booking);

		// Calculate whether it is peak hour or not
		$morning_start = "7:30:00";
		$morning_end = "9:30:00";
		$night_start = "16:00:00";
		$night_end = "18:30:00";

		$time_formatted = DateTime::createFromFormat('H:i:s', $old_time);
		$morning_peak_start = DateTime::createFromFormat('H:i:s', $morning_start);
		$morning_peak_end = DateTime::createFromFormat('H:i:s', $morning_end);
		$nightpeak_start = DateTime::createFromFormat('H:i:s', $night_start);
		$nightpeak_end = DateTime::createFromFormat('H:i:s', $night_end);
		if (($morning_peak_start < $time_formatted && $time_formatted < $morning_peak_end) || ($nightpeak_start < $time_formatted && $time_formatted < $nightpeak_end)) {
			$time_drive_int = round(((5 * $distance)/5), 0) * 5;
		} else {
			$time_drive_int = round(((3 * $distance)/5), 0) * 5;
		}

		$time_drive = strval($time_drive_int);
	
		// if previous block has a booking add a buffer time based on above calculations
		if ($previous_block_time != $previous_check_time && $previous_block_manually_hidden == false) {
			if ($time_drive > 19 && $time_drive < 34) {
				$time_blocks_to_hide++;
			}
			else if ($time_drive > 34 && $time_drive < 49)
			{
				$time_blocks_to_hide+=2;
			}
			else {
				$previous_block_manually_hidden = false;
			}
			// // This code increased the next available time slot by x minutes // // 
		// $display_time =  date('h:i a', strtotime("+".$time_drive." minutes", strtotime($old_time)));
		// $new_time = date('h:i:s', strtotime("+".$time_drive." minutes", strtotime($old_time)));

		// $available_arr[$key] = '<li class="block" data-block='. $data_block_val_new. '>
		// <a href="#" data-value="2022-03-23T'.$new_time.'+1030">'.$display_time.'</a>
		// </li>';

			// instead, auto hide this next one, if drive time is less than 15, hide this one only, if more than 15 less than 30 hide next 2, if more than 30 hide next 3
			unset($available_arr[$key]);
		}
		else {
			$previous_block_manually_hidden = false;
		}
		$previous_block_time = $old_time;
	}
}
// join the string back back_together to be returned 
$back_together = implode("</li>", $available_arr);

return $back_together;
}
add_filter( 'wc_bookings_get_time_slots_html', 'edit_availability_slots_by_location', 10, 2);



add_action( 'template_redirect', 'edit_end_time_of_booking' );
function edit_end_time_of_booking() {
  // Make sure the request is for a user-facing page
  if ( 
    ! is_product()
  ) {
    return false;
  }

  // Otherwise do your thing
  ?><script>
	document.addEventListener("DOMContentLoaded", function(event) {
		// get the value of the start time

		function setEndTime() {
			console.log("end time")
		}

		let start_time = document.querySelectorAll('[name="wc_bookings_field_start_date_time"]')
		console.log(start_time);
		//  any time a service changes
		// I get the start time, and change the end time accordingly (use a function)
		
		// on start time change

		// on services change
		document.querySelectorAll(`input[type='checkbox'][value=crew-cut`)[0].addEventListener('change', setEndTime());


	}); 
	 </script> 
  <?php  
}

