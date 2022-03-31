<?php 
	global $woocommerce, $product;
	$product_id = $product->get_id();
	
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
<?php /*--if($qualified){ */ ?>
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
					}

					$rating_count = $product->get_rating_count();
					$review_count = $product->get_review_count(); ?>

					<?php /*-woocommerce_template_single_rating(); * ///--Disabled in WooCommerce > Settings > Products ?>
					<a href="<?php echo get_permalink() ?>#reviews" class="star" rel="nofollow"><span></span><?php echo $product->get_average_rating(); ?> (<?php printf( _n( '%s',$review_count,'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?> Reviews)</a> */?>

					<div class="product-reviews">
						<p class="star"><?php echo substr($product->get_average_rating(), 0, 3); ?> (<?php printf( _n( '%s',$review_count,'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?><span class="count"> Reviews</span>)</p>
					</div>
					
					<?php /*--if(!$display_addon){ ?>
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
					} */ ?>
				</div>
			</div>
			<?php if(!$show_brife_product_tile){ ?>
				<div class="jac-barber-description">
					<p><?php echo $product_short_description ?></p>
				</div>
			<?php } ?>
			
			<?php if(!$show_brife_product_tile){ ?>
					<div class="jac-products-header-top-right">
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="jac-visit-barber btn btn-color-primary">Visit Barber</a>
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
				if ($data->value && $data->value[0]) {
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

		/* if ($muffin && !$show_brife_product_tile) { ?>
		<style>
			.jac-products-header-top-left > div{
				display: inline-block;
			}
		</style>
		<?php } */ ?>

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
<?php /*--} */ ?>
