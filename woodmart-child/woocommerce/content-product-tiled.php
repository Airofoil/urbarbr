<?php 
	global $woocommerce, $product;
	$average = $product->get_average_rating();
	$review_count = $product->get_review_count();
	$product_title = $product->get_name();
	$product_id = $product->get_id();

	$product_details = $product->get_data();
	$product_full_description = $product_details['description'];
	$product_short_description = $product_details['short_description'];

	do_action( 'woocommerce_before_shop_loop_item' ); 

	$show_brife_product_tile = is_front_page();
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
		
		<div class="jac-barber-details <?php echo $show_brife_product_tile?"brief-product-tile":""; ?>">
			<div class="jac-products-header-top">
				<div class="jac-products-header-top-left">
					<h5 class="jac-barber-name"><?php echo $product_title ?></h5>
					<?php if($show_brife_product_tile && get_field( "short_description",$product_id) && get_field( "short_description",$product_id)!=""){
						echo '<p class="short_description">' . get_field( "short_description",$product_id) . '</p>';
					} ?>
					<?php if(!$show_brife_product_tile){
						echo '<div class="star-rating"><span style="width:'.( ( $average / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>'; 
					}?>
					<div><span> <?php printf( _n( '%s',$review_count,'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>
					<?php if ( comments_open() && !$show_brife_product_tile ){ echo "Reviews"; } ?> </span>
					<?php if($show_brife_product_tile){
						echo '<div class="star-rating"><span style="width:'.( ( $average / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>'; 
					}?>
					
					<?php if($show_brife_product_tile){
						echo '<span class="barber_location">' . get_field( "barber_location",$product_id) . '</span>';
					} ?>
					</div>
				</div>
				<?php if(!$show_brife_product_tile){ ?>
					<div class="jac-products-header-top-right">
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="jac-visit-barber">Visit Barber</a>
					</div>
				<?php } ?>
			</div>
			<?php if(!$show_brife_product_tile){ ?>
				<div class="jac-barber-description">
					<p><?php echo $product_short_description ?></p>
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
				foreach($data->value[0]['options'] as $index=>$value){
					echo '<div class="product-element-bottom">';
					// var_dump($value);
					echo '<div>' . $value['label'] . '</div>';
					echo '<div>$' . number_format((float)$value['price'], 2, '.', '') . '</div>';
					
					echo '</div>';
				}
			}
		}
	?>
	<style>
		.product-element-bottom{
			display: flex;
    		justify-content: space-between;
			margin-bottom: var(--wd-tags-mb);
			color: #284158;
			font-size:16px;
			text-transform: var(--wd-title-transform);
			font-weight: var(--wd-title-font-weight);
			font-style: var(--wd-title-font-style);
			font-family: var(--wd-title-font);
			line-height: 1.4;
		}
		.jac-barber-description{
			font-weight: bolder;
			margin-top: 25px;
		}
		.jac-barber-name{
			margin-bottom: 0px;
			color:#292727;
			font-size:20px;
		}
		.jac-products-header-top-right a{
			color: #284158;
			font-size: 16px;
			font-weight: bold;
			/* border-bottom: 2px solid #8896A2; */
    		padding: 0 0 3px;
			position: absolute;
			top: 0;
			left: -5px;
			width: 110px;
		}
		.jac-products-header-top-left{
			color: #102C45;
			font-size: 14px;
			font-weight: 400;
		}
		.jac-products-header-top-right {
			height: 30px;
			position: relative;
			border-bottom: 2px solid #8896A2;
			width: 95px;
		}
		.jac-barber-details .woocommerce-review-link{
			color: #284158;
			font-weight: bold;
			font-size: 90%;
		}
		.jac-products-header-top-left .star-rating:before{
			content: "\f149" !important;
			color:#FFC702 !important;
		}
		.jac-products-header-top-left .product-grid-item .star-rating{
			margin-bottom: 2px;
		}
		.archive .product-grid-item.product-type-booking .product-wrapper{
			padding: 30px;
    		background-color: #F8F9F9;
		}
		.wd-hover-tiled .product-element-bottom{
			padding: 20px;
		}
		.product-element-bottom:last-of-type{
			margin-bottom:0px;
		}
		.brief-product-tile{
			margin-left:0px;
			margin-top:10px;
		}
		.brief-product-tile .jac-barber-name{
			font-size: 16px;
		}
		.product-grid-item .brief-product-tile .star-rating{
			margin-bottom: 2px;
		}
		.brief-product-tile .jac-products-header-top-left{
			color: #70808F;
    		font-size: 14px;
		}
		.jac-products-header-top-left > div > span.barber_location{
			float: right;
			color:#000000;
		}
		.short_description{
			margin-bottom: 6px;
		}
		<?php if(!$show_brife_product_tile): ?>
			.jac-products-header-top-left > div{
				display:inline-block;
			}
		<?php endif ?>
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
