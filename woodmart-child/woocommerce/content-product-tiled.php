<?php 
	global $woocommerce, $product;
	$product_id = $product->get_id();

	$street = get_field( "barber_address" );
	$city = get_field( "barber_city" );
	$state = get_field( "barber_state" );
	$country = get_field( "barber_country" );
	$prefer_distance = get_field( "prefer_serve_distance_km" );

	if($street != "" && $city != "" && $state != "" && $country != "" && $prefer_distance != "") {
		$geo_result = brrad_geocode($street, $city, $state, $country);
		$bar_lat = floatval ($geo_result['latitude']);
		$bar_long = floatval ($geo_result['longitude']);

		// echo '<pre>barber latitude:'; print_r($bar_lat);  echo '</pre>';
		// echo '<pre>barber longitude:'; print_r($bar_long);  echo '</pre>';
	}

	if ($_GET) {
		$searching_location = $_GET['your-location'];
		$searching_service = $_GET['booking-services'];
		$searching_date = $_GET['booking-date'];
		$filtering = false;
		$location_qualified = true;

		$searching_lat_long = $_GET['your-lat-long'];

		// echo '<pre>'; print_r($searching_location);  echo '</pre>';

		if($searching_location || $searching_service || $searching_date || $searching_lat_long){
			$filtering = true;
			$qualified = false;
		}else{
			$qualified = true;
		}

		if($searching_location) {

			$location_qualified = false;
			// echo '<pre>'; print_r($searching_location);  echo '</pre>';

			$goo_tem_address = str_replace(" ", "+", $searching_location);
			$goo_address = str_replace(",", "", $goo_tem_address);
			// echo '<pre>'; print_r($goo_address);  echo '</pre>';

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

			// echo '<pre>'; print_r($return);  echo '</pre>';

			$search_location_lat = floatval ($return['latitude']);
			$search_location_long = floatval ($return['longitude']);

			$search_distance_between = distance($search_location_lat, $search_location_long, $bar_lat, $bar_long, "K");

			// echo '<pre>'; print_r($search_distance_between);  echo '</pre>';

			if($search_distance_between < $prefer_distance) {
				$location_qualified = true;
				$qualified = true;
			}
		} elseif($searching_lat_long) { //use search input address instead of current location if there is an address in the search field

			$location_qualified = false;

			$lat_long_array = explode(',', $searching_lat_long);
			$location_lat = floatval ($lat_long_array[0]);
			$location_long = floatval ($lat_long_array[1]);
			// echo '<pre>'; print_r($location_lat);  echo '</pre>';
			// echo '<pre>'; print_r($location_long);  echo '</pre>';

			$distance_between = distance($location_lat, $location_long, $bar_lat, $bar_long, "K");
			// echo '<pre>'; print_r("Distance:" . $distance_between);  echo '</pre>';

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

		if($searching_date){

			global $wpdb;

			$qualified = false;

			$min_date = date('Y-m-d', strtotime(substr($searching_date,0,-3)));

			$max_date = date('Y-m-d', strtotime($min_date . ' +1 day'));
			
			// $url = get_site_url() . '/wp-json/wc-bookings/v1/products/slots?min_date=' . $min_date . '&max_date=' . $max_date . '&product_ids=' . $product_id;

			/* curl function not working on local, that needs to crawl staging/live site product data. Comment the above line and uncomment the below line to fetch from staging site */
			$url = 'https://staging-urbarbr.kinsta.cloud/wp-json/wc-bookings/v1/products/slots?min_date=' . $min_date . '&max_date=' . $max_date . '&product_ids=' . $product_id;

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			// //for debug only!
			// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$resp = curl_exec($curl);
			$resources = json_decode($resp, true);
			curl_close($curl);

			/* Don't convert search date time because Woo Bookings REST API does not recognize time, but only date*/
			$search_date_formatted = date("Y-m-d H:i", strtotime($searching_date));

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

		// echo '<pre>'; print_r($qualified);  echo '</pre>';
	}
	
	
// 	echo $filtering?"true":"false";
	$average = $product->get_average_rating();
	$review_count = $product->get_review_count();
	$product_title = $product->get_name();

	$product_details = $product->get_data();
	$product_full_description = $product_details['description'];
	$product_short_description = $product_details['short_description'];
	$display_addon = woodmart_loop_prop( 'display_addon' );
	do_action( 'woocommerce_before_shop_loop_item' ); 
	$show_brife_product_tile = is_front_page();
	
	$service_label = '';
	$service_fee = '';

	$search = $wpdb->prepare(
				"SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE 'addon_sales_%' AND post_id = %d ORDER BY meta_value DESC",
				$product_id 
			);
	$search_results = $wpdb->get_results($search);
	if($search_results && count($search_results)>0){
		$service_label = $search_results[0]->meta_key;
		$service_label = str_replace('_', ' ', substr($service_label,(strlen('addon_sales_')-1)));
		
		foreach ($product->get_meta_data() as $index => $data) { 
			if($data->key == '_product_addons'){
				foreach($data->value[0]['options'] as $index=>$value){
					if(trim(strtolower($value['label'])) == trim(strtolower($service_label))){
						$service_fee = '$' . number_format((float)($value['price']), 2, '.', '');
					}
				}
			}
		}
	}
?>
<?php if($qualified){  ?>
<div class="product-wrapper">
	<div class="product-element-top">
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="product-image-link">
			<?php
				/**
				 * woocommerce_before_shop_loop_item_title hook
				 *
				 * @hooked woocommerce_show_product_loop_sale_flash - 10
				 * @hooked woodmart_template_loop_product_thumbnail - 10
				 */
				do_action( 'woocommerce_before_shop_loop_item_title' );
			?>
		</a>
		<?php woodmart_hover_image(); ?>
		
		<div class="jac-barber-details <?php echo $show_brife_product_tile?"brief-product-tile":""; ?>">
			<!-- <div>
				<?php 
					// echo '<pre>'; print_r($geo_result);  echo '</pre>'; 
				?>
			</div> -->
			<div class="jac-products-header-top">
				<div class="jac-products-header-top-left">
					<h5 class="jac-barber-name">
						<?php  if(!$display_addon || !$show_brife_product_tile){
							echo $product_title;
						}else{
							echo $service_label;
						} ?>
					</h5>
					<?php if(!$display_addon || !$show_brife_product_tile){}else{
						echo '<p class="short_description">' . $product_title . '</p>';
					} ?>
					<?php if($show_brife_product_tile && !$display_addon  && get_field( "short_description",$product_id) && get_field( "short_description",$product_id)!=""){
						echo '<p class="short_description">' . get_field( "short_description",$product_id) . '</p>';
					} ?>
					<?php if(!$display_addon){ ?>
						<?php if(!$show_brife_product_tile){
							echo '<div class="star-rating"><span style="width:'.( ( $average / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>'; 
						}?>
						<div>
							<span> <?php printf( _n( '%s',$review_count,'woocommerce' ), ' <span class="count">' . esc_html( $review_count ) . '</span>' ); ?>
							<?php if (!$show_brife_product_tile){ echo ' Review' . (esc_html( $review_count ) == 1 ? '' : 's'); } ?> </span>
							<?php if($show_brife_product_tile){
								echo '<div class="star-rating"><span style="width:'.( ( $average / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>'; 
							}?>
							
							<?php if($show_brife_product_tile){
								echo '<span class="barber_location">' . get_field( "barber_location",$product_id) . '</span>';
							} ?>
						</div>
					<?php }else{
						echo '<div class="barber_service_price"> From ' . $service_fee . '</div>';
					}?>
				</div>
			</div>
			<?php if(!$show_brife_product_tile){ ?>
				<div class="jac-barber-description">
					<p><?php echo $product_short_description ?></p>
				</div>
			<?php } ?>
			
			<?php if(!$show_brife_product_tile){ ?>
					<div class="jac-products-header-top-right">
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="jac-visit-barber btn btn-color-alt">Visit Barber</a>
					</div>
			<?php } ?>
		</div>
		
		<div class="wd-buttons wd-pos-r-t<?php echo woodmart_get_old_classes( ' woodmart-buttons' ); ?>">
			<?php woodmart_enqueue_js_script( 'btns-tooltip' ); ?>
			<div class="wd-add-btn wd-action-btn wd-style-icon wd-add-cart-icon<?php echo woodmart_get_old_classes( ' wd-add-cart-btn woodmart-add-btn' ); ?>"><?php do_action( 'woocommerce_after_shop_loop_item' ); ?></div>
			<?php woodmart_quick_view_btn( get_the_ID() ); ?>
			<?php woodmart_add_to_compare_loop_btn(); ?>
			<?php do_action( 'woodmart_product_action_buttons' ); ?>
		</div> 
		<?php woodmart_quick_shop_wrapper(); ?>
	</div>
	
	<?php
		foreach ($product->get_meta_data() as $index => $data) {
			if($data->key == '_product_addons' && !$show_brife_product_tile){
				if ($data->value[0]) {
					foreach($data->value[0]['options'] as $index=>$value){
						echo '<div class="product-element-bottom">';
						// var_dump($value);
						echo '<div>' . $value['label'] . '</div>';
						echo '<div>$' . number_format((float)$value['price'], 2, '.', '') . '</div>';
						
						echo '</div>';
					}
				}
			}
		}
	?>
	<style>
		<?php
		// Moved styling to style.css ✓

		if (!$show_brife_product_tile) { ?>
			.jac-products-header-top-left > div{
				display: inline-block;
			}
		<?php } ?>
	</style>
	<!-- NOT SURE IF BELOW CODE IS REQUIRED -->
	<?php
			/**
			 * woocommerce_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_product_title - 10
			 */
			// do_action( 'woocommerce_shop_loop_item_title' );
		?>
	
		<?php
			woodmart_product_categories();
			woodmart_product_brands_links();
		?>
	
		<?php 
			echo woodmart_swatches_list();
		?>
	
		<?php
			/**
			 * woocommerce_after_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			//do_action( 'woocommerce_after_shop_loop_item_title' );
		?>
		
		<?php if ( woodmart_loop_prop( 'progress_bar' ) ): ?>
			<?php woodmart_stock_progress_bar(); ?>
		<?php endif ?>

		<?php if ( woodmart_loop_prop( 'timer' ) ): ?>
			<?php woodmart_product_sale_countdown(); ?>
		<?php endif ?>
</div>
<?php } ?>
