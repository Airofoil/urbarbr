<?php
/**
 * Enqueue script and styles for child theme
 */
/* ++Must be at the top of this file: * /

function initCors( $value ) {
	$hostname = $_SERVER['SERVER_NAME'];

	// Verify which environment the site is running on
	switch ($hostname) {
		case 'staging-urbarbr.kinsta.cloud':
			define('WP_ENV', 'staging');
			$env = 'staging';
			break;
		case 'www.urbarbr.com.au':
			define('WP_ENV', 'production');
			$env = 'production';
			break;
		default:
			define('WP_ENV', 'staging');
			$env = 'staging';
	}

	$origin_url = '*';

	// Check if production environment or not
	if ($env === 'production') {
		$origin_url = '--PRODUCTION URL'; //--someone gotta add the url - JDH
	}

	header( 'Access-Control-Allow-Origin: ' . $origin_url );
	return $value;
}
add_action( 'rest_api_init', function() {
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter( 'rest_pre_serve_request', initCors);
}, 15 );*/


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
	// Make sure the request is for a user-facing product page
	if (!is_product()) {
		return false;
	}
	
	?>
	<script>
		document.addEventListener("DOMContentLoaded", function(event) {
			const url = window.location.href;
			let hash = url.split('#');
			let items = hash.slice(1);
			if (items.length){
				let items_together = items[0].replace(/["'{}%2134567890:]/g, "");
				let services = items_together.split(',');

				for (const i in services) {
					if (services[i]) document.querySelectorAll(`input[type="checkbox"][value="${services[i]}"]`)[0].checked = true;
				}
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
	/* If the user is logged in, redirect them to their account page instead of showing a blank page */
	if (is_admin() || is_user_logged_in()) {
		wp_redirect('/my-account');
		exit();
	}
  
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
		'wishlist'   	  => __( 'My Favourites', 'woocommerce' ),
		'my-review'   	  => __( 'My Reviews', 'woocommerce' ),
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
		<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>-->
		<script src="' . get_stylesheet_directory_uri() . '/xdsoft_datetimepicker/jquery.datetimepicker.full.min.js"></script>
		<script src="' . get_stylesheet_directory_uri() . '/periodpicker/jquery.mousewheel.min.js"></script>
		<script src="' . get_stylesheet_directory_uri() . '/periodpicker/build/jquery.timepicker.min.js"></script>
		<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/xdsoft_datetimepicker/jquery.datetimepicker.css">
		<link rel="stylesheet" type="text/css" href="' . get_stylesheet_directory_uri() . '/periodpicker/build/jquery.timepicker.min.css">
		<script>
			var listener = function(e) {
				if (!document.getElementsByClassName("xdsoft_datetimepicker")[0].contains(e.target)) { console.log(11,"clicked on",e,"- hiding datetimepicker");
					$(".xdsoft_datetimepicker").hide();
					if ($("#booking-date-search").val()) $("#booking-date-search").addClass("entered");
					else {
						$("#booking-date-search").datetimepicker("reset");
						$("#booking-time").TimePickerAlone("setValue", "00:00");
						$(".xdsoft_datetimepicker").hide();//datetimepicker("hide");
						$("#booking-date-search").datetimepicker("hide");
					}
					window.removeEventListener("click", listener, false);
				}
			};
			$(document).ready(function() {
				$(".searchform input").unbind();
				$("#booking-date-search").datetimepicker({
					// timepicker: false, // Need timepicker enabled to insert the other timepicker
					format: "Y-m-d",
					yearStart: 2022,
					yearEnd: new Date().getFullYear() + 1,
					minDate: Date.now(),
					maxDate: Date.now() + 3600000*24*200, // Allow up to 200 days in the future
					// closeOnWithoutClick: true,
					// closeOnDateSelect: false,
					onClose: function(ct,$i){ console.log("cancelling close"); return false; /* Cancel the default close - this is since touching in the timepicker will close the datepicker */ },
					onShow: function(){ console.log("opening");
						setTimeout(() => window.addEventListener("click", listener, false), 100);
					}
				});
				$("#booking-time").TimePickerAlone({
					inputFormat: "HH:mm:ss",
					defaultTime: "00:00",
					hours: true,
					minutes: true,
					seconds: false,
					ampm: true,
					steps: [1,5,30,1],
					onHide: function ($input) { console.log($input.val())
						return $input.val() === "12:34:00";
					}
				});
				$("#booking-time").trigger("click");
				$(".xdsoft_datetimepicker .xdsoft_timepicker").html($(".periodpicker_timepicker_dialog")).append(`<button class="datepicker-confirm btn-link btn">Ok</button><button class="datepicker-cancel btn-link btn">Cancel</button>`);
				$(".datepicker-confirm").on("click touchstart", function() {
					$(".xdsoft_datetimepicker").hide();//datetimepicker("hide");
					$("#booking-date-search").datetimepicker("hide");
					if ($("#booking-date-search").val()) $("#booking-date-search").addClass("entered");
				});
				$(".datepicker-cancel").on("click touchstart", function() {
					$("#booking-date-search").datetimepicker("reset");
					$("#booking-time").TimePickerAlone("setValue", "00:00");
					$(".xdsoft_datetimepicker").hide();//datetimepicker("hide");
					$("#booking-date-search").datetimepicker("hide");
				});
				//-$(".periodpicker_timepicker_dialog").appendTo(".xdsoft_datetimepicker");
				//-$(".xdsoft_datetimepicker").append();
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

remove_action('wp_head', 'moment-js');
remove_action('wp_head', 'moment-js-after'); 

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

	$barberList = array();

	foreach ($jsonProduct as $productItem) {
		foreach ($productItem['meta_data'] as $item) {
			if ($item['key'] === 'barber_phone') {
				$item['value'] = str_replace(' ', '', $item['value']);
				if ($item['value'][0] === '0') {
					$item['value'] = '+61' . substr($item['value'], 1);
				}
				if ($item['value'][0] !== '+') {
					$item['value'] = '+' . strval($item['value']);
				}
				$barberList[$productItem['name']] = $item['value'];
			}
		}
	}

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
			$jsonBooking[$i]['date_created'] = $jsonBooking[$i]['date_created'] - 37800;
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
						sendex_publish_post($customers[$i]['billing']['phone'], $customers[$i]['billing']['first_name'], date('g:i A', $jsonBooking[$i]['start']));
						reminder_barber($barberList[$jsonProduct[$j]['name']], $jsonProduct[$j]['name'], date('g:i A', $jsonBooking[$i]['start']), $customers[$i]['billing']['first_name'], $jsonBooking[$i]['order_id']);
					
					} else if (($long - $jsonBooking[$i]['date_created']) > 120 && ($long - $jsonBooking[$i]['date_created']) < 180) {
						just_made_booking($customers[$i]['billing']['phone'], $customers[$i]['billing']['first_name'], date('g:i A', $jsonBooking[$i]['start']));
						just_made_booking_barber($barberList[$jsonProduct[$j]['name']], $jsonProduct[$j]['name'], date('g:i A', $jsonBooking[$i]['start']), $customers[$i]['billing']['first_name'], $jsonBooking[$i]['order_id']);
					
					} else if ($jsonBooking[$i]['product_id'] === $jsonProduct[$j]['id']) {
						if ((($jsonBooking[$i]['start'] + 3600) - $long) > -30 && (($jsonBooking[$i]['start'] + 3600) - $long) < 30) {
							//wp_mail( 'ghjgjh0107@gmail.com', 'complete appointment', $customers[$i]['billing']['phone'] );
							complete_appointment_customer($customers[$i]['billing']['phone'], $customers[$i]['billing']['first_name'], $jsonProduct[$j]['name']);
							complete_appointment_barber($barberList[$jsonProduct[$j]['name']], $jsonProduct[$j]['name'], $customers[$i]['billing']['first_name'], $jsonBooking[$i]['order_id']);
						}
					}
				}
			}
		}
/*
		for ($i=0; $i < count($jsonBooking); $i++) {  //Complete the appointment
			for ($j=0; $j < count($jsonProduct); $j++) { 
				if ($jsonBooking[$i]['product_id'] === $jsonProduct[$j]['id']) {
					if ((($jsonBooking[$i]['start'] + 3600) - $long) > -30 && (($jsonBooking[$i]['start'] + 3600) - $long) < 30) {
						//wp_mail( 'ghjgjh0107@gmail.com', 'complete appointment', $customers[$i]['billing']['phone'] );
						complete_appointment_customer($customers[$i]['billing']['phone'], $customers[$i]['billing']['first_name']);
						complete_appointment_barber($barberList[$jsonProduct[$j]['name']], $jsonProduct[$j]['name']);
					}
				}
			}
		}*/
	}
	//wp_mail( 'ghjgjh0107@gmail.com', $jsonBooking[0]['status'], $customers[0]['billing']['phone'] );
}

/*---TEMP-REMOVED-function calculate_distance($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'kilometers') {
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


// gets inputs and checks which ones have been selected, adds to the amount of time in javascript 
// It needs to convert the javascript increase_time varibale into a php varibale variable
// You then need to divide by 15 to get the amount of blocks it will take. 
// then times by 900. 900 is 15 minutes, so 2700 adds 45 minutes to the booking. you then add this to the...
// $field_array['_end_date'] varibale, setting the end time to the right amount of time
*/
function edit_length_of_booking($field_array) {
	if (!is_singular('product')) return $field_array; // To stop the extra field data showing on product info e.g. for Favourites/Wishlist page //-echo 'post_type:' . get_post_type();

	$total_time = 0;
	echo <<<'EOD'
	<script>
	const times = {
		"burst-fade": 30,
		"crew-cut": 30,
		"buzzcut": 15,
		"beard-trim": 15,
		"event-styling": 45,
		"headshave": 15,
		"kids-haircut": 30,
		"taper": 30,
		"porpadour": 45,
		"head-+-beard": 60,
		"hair-colour": 105,
		"perm": 90,
		"toupe": 90
	}
	document.addEventListener('DOMContentLoaded', function(event) {
		let increase_time = 0;
		const input_fields = document.querySelectorAll('input.wc-pao-addon-field');
		for (key in input_fields) {
			if (times[input_fields[key].value]) {
				increase_time += times[input_fields[key].value];
			}
		}
	});
	</script>
	EOD;
	
	/*echo <<<'EOD'
	<script>
	document.addEventListener('DOMContentLoaded', function(event) {
		// get the value of the start time
		let increase_time = 0;
		const input_fields = document.getElementsByTagName('input');
		for (key in input_fields) {
			if (input_fields[key].value == 'burst-fade') {
				if (input_fields[key].checked != true) {
					increase_time += 30;
				}
			}
			else if (input_fields[key].value == 'crew-cut') {
				if (input_fields[key].checked) {
					increase_time += 30;
				}
			}
			else if (input_fields[key].value == 'buzzcut') {
				if (input_fields[key].checked) {
					increase_time += 15;
				}
			}
			else if (input_fields[key].value == 'beard-trim') {
				if (input_fields[key].checked) {
					increase_time += 15;
				}
			}
			else if (input_fields[key].value == 'event-styling') {
				if (input_fields[key].checked) {
					increase_time += 45;
				}
			}
			else if (input_fields[key].value == 'headshave') {
				if (input_fields[key].checked) {
					increase_time += 15;
				}
			}
			else if (input_fields[key].value == 'kids-haircut') {
				if (input_fields[key].checked) {
					increase_time += 30;
				}
			}
			else if (input_fields[key].value == 'taper') {
				if (input_fields[key].checked) {
					increase_time += 30;
				}
			}
			else if (input_fields[key].value == 'porpadour') {
				if (input_fields[key].checked) {
					increase_time += 45;
				}
			}
			else if (input_fields[key].value == 'head-+-beard') {
				if (input_fields[key].checked) {
					increase_time += 60;
				}
			}
			else if (input_fields[key].value == 'hair-colour') {
				if (input_fields[key].checked) {
					increase_time += 105;
				}
			}
			else if (input_fields[key].value == 'perm') {
				if (input_fields[key].checked) {
					increase_time += 90;
				}
			}
			else if (input_fields[key].value == 'toup�e') {
				if (input_fields[key].checked) {
					increase_time += 90;
				}
			}
		}
		// this doesnt work yet, thought i could send the variable to the server and set it in php but it retrns null
		$.ajax({
			url: window.location, //window.location points to the current url. change is needed.
			type: 'POST',
			data: {
				length: increase_time
			},
			success: function(response){
				//console.log('Successful! My post data is: ',response);
				//console.log(${json_encode($_POST['length'])});
			},
			error: function(error){
				console.log('error',error);
			}
		});
	});
	//console.log(${json_encode($_POST['wc_bookings_field_start_month'])});
	console.log("Field here maybe");
	</script>
	EOD;*/

	// 15 mins is 900
	// set booking length to not fixed
	$field_array['wc_bookings_field_start_date']['duration_type'] = "variable";	

	// calculate what services are selected and their length

	$field_array['_end_date'] = $field_array['_end_date'] + 2700;
	return $field_array;
}
add_filter('booking_form_fields', 'edit_length_of_booking', 10, 1);


function edit_length_of_booking2($field_array) {
	// $field_array['wc_bookings_field_duration']['max'] = 6;
	?>
		<script>
			console.log("edit duration posted data");
			console.log(<?php echo json_encode($field_array['_end_date']) ?>);
		</script> 
	<?php 
	// $field_array['_end_date']	=
	return $field_array;
}
add_filter('woocommerce_booking_form_get_posted_data', 'edit_length_of_booking', 10, 1); 

function brrad_geocode($street_address,$city,$state,$country){
        
	$street_address = str_replace(" ", "+", $street_address); //google doesn't like spaces in urls, but who does?
	$city = str_replace(" ", "+", $city);
	$state = str_replace(" ", "+", $state);
	$country = str_replace(" ", "+", $country);

	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=$street_address,+$city,+$state,+$country&key=AIzaSyBrFVuDdduHECkgQNAsFuv0XgBW-3jLw60&sensor=false"; 
	$google_api_response = wp_remote_get( $url );    

	$results = json_decode( $google_api_response['body'] ); //grab our results from Google
	$results = (array) $results; //cast them to an array
	$status = $results["status"]; //easily use our status
	$location_all_fields = (array) $results["results"][0];
	$location_geometry = (array) $location_all_fields["geometry"];
	$location_lat_long = (array) $location_geometry["location"];

	echo "<!-- GEOCODE RESPONSE " ;
	var_dump( $location_lat_long );
	echo " -->";

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
	return $return;
}

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344);
	} else if ($unit == "N") {
		return ($miles * 0.8684);
	} else {
		return $miles;
	}
}

// replace default WC header action with a custom one
add_action( 'init', 'ml_replace_email_footer_hook' );    
function ml_replace_email_footer_hook(){
    remove_action( 'woocommerce_email_footer', array( WC()->mailer(), 'email_footer' ) );
    add_action( 'woocommerce_email_footer', 'ml_woocommerce_email_footer', 10, 2 );
}

// new function that will switch template based on email type
function ml_woocommerce_email_footer( $email ) {
    // var_dump($email); die; // see what variables you have, $email->id contains type
//     switch($email->id) {
//         case 'new_order':
//             $template = 'emails/email-header-new-order.php';
//             break;
//         default:
//             $template = 'emails/email-header.php';
//     }	

	$template = 'emails/email-footer.php';
	
	// echo '<pre>'; print_r($email); echo '</pre>';

    // wc_get_template( $template, array( 'email_heading' => $email_heading ) );
	wc_get_template( $template, array( 'email_id' => $email->id ) );
}
add_action( 'save_post_wc_booking', 'save_post_wc_booking', 10, 3 );

function save_post_wc_booking( $post_id, \WP_Post $post, $update ) {

	//Get customer's latitude and lontitude from COOKIE
	$location = isset($_COOKIE['location_lat_long']) ? $_COOKIE['location_lat_long'] : false;
	$location_latitutde = false;
	$location_longtitude = false;
	if($location){
		$coord = explode(",",$location);
		if(count($coord) == 2){
			$location_latitutde = $coord[0];
			$location_longtitude = $coord[1];
		}
	}

	if($location_latitutde){
		if( ! get_post_meta( $post_id,'latitude', true ) ){
			add_post_meta($post_id,'latitude',$location_latitutde);
		}else{
			update_post_meta($post_id,'latitude',$location_latitutde);
		}
	}
	if($location_longtitude){
		if( ! get_post_meta( $post_id,'longitude', true ) ){
			add_post_meta($post_id,'longitude',$location_longtitude);
		}else{
			update_post_meta($post_id,'longitude',$location_longtitude);
		}
	}
}

add_filter( 'calculate_buffer_time','calculate_buffer_time_filter',10,6);

function calculate_buffer_time_filter($buffertime,$latitude,$longitude,$booking_latitude,$booking_longitude,$duration){
	if($latitude && $longitude && $booking_latitude && $booking_longitude){
		$distance = distance($latitude,$longitude,$booking_latitude,$booking_longitude,"K");
		$speed = 50;
		//calculate time based on distance/speed. convert to how many duration
		$buffertime = round($distance*(60/$duration) / $speed,0); 
	}

	$buffertime = intval($buffertime);
	$buffertime = $buffertime <= 0? 1: $buffertime;
	$buffertime = $buffertime >=4? 4: $buffertime;

	return $buffertime;
}