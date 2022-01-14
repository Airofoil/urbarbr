<?php 
	global $woocommerce, $product;
	$average = $product->get_average_rating();
	$review_count = $product->get_review_count();
	$product_title = $product->get_name();
	$product_id = $product->get_id();

	$product_details = $product->get_data();
	$product_full_description = $product_details['description'];
	$product_short_description = $product_details['short_description'];

	$custom_field = get_post_meta( $product_id, '_product_addons', false );

	do_action( 'woocommerce_before_shop_loop_item' ); 
?>

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
		
		<div class="jac-barber-details">
			<div class="jac-products-header-top">
				<div class="jac-products-header-top-left">
					<h5 class="jac-barber-name"><?php echo $product_title ?></h5>
					<?php echo '<div class="star-rating"><span style="width:'.( ( $average / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>'; ?>
					<?php if ( comments_open() ): ?><a href="<?php echo get_permalink() ?>#reviews" class="woocommerce-review-link" rel="nofollow"><?php printf( _n( '%s',$review_count,'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?> Reviews</a><?php endif ?>
				</div>
				<div class="jac-products-header-top-right">
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="jac-visit-barber">Visit Barber</a>
				</div>
			</div>
			<div class="jac-barber-description">
				<p><?php echo $product_short_description ?></p>
			</div>
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

	<div class="product-element-bottom">
		<?php
			/**
			 * woocommerce_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_product_title - 10
			 */
			// do_action( 'woocommerce_shop_loop_item_title' );
		?>

		<?php
			foreach( $custom_field as $field => $value) { 
				foreach( $value as $val => $v) {  
					?>
					<p><?php echo $v["name"]; ?></p>
					<?php

						foreach( $v as $val => $v_items) {
							echo $v_items["label"];
						}
					// echo $v["name"]; // or other field that you want!
				}            
			}
		?>

		<div><p>"Testing 1"</p></div>
		<?php
			woodmart_product_categories();
			woodmart_product_brands_links();
		?>
		<div><p>"Testing 2"</p></div>
		<?php 
			echo woodmart_swatches_list();
		?>
		<div><p>"Testing 3"</p></div>
		<?php
			/**
			 * woocommerce_after_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			do_action( 'woocommerce_after_shop_loop_item_title' );
		?>
		<div><p>"Testing 4"</p></div>
		<?php if ( woodmart_loop_prop( 'progress_bar' ) ): ?>
			<?php woodmart_stock_progress_bar(); ?>
		<?php endif ?>
		<div><p>"Testing 5"</p></div>
		<?php if ( woodmart_loop_prop( 'timer' ) ): ?>
			<?php woodmart_product_sale_countdown(); ?>
		<?php endif ?>
	</div>
</div>
