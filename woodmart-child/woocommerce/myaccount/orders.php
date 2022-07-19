<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<?php if ( $has_orders ) : ?>
	<?php foreach ($customer_orders->orders as $customer_order) {
		$order      	= wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$item_count 	= $order->get_item_count() - $order->get_item_count_refunded();
		$product_name 	= array();
		$product_id 	= array();

		foreach($order->get_items() as $item) {
			$product_name[] = $item['name'];
			$product_id[] = $item->get_product_id();
		}



		/* Calculate the number of days from booking: */
		foreach($order->get_items() as $item_id => $item) { //only allow for one booking in single order
			$booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
			foreach ( $booking_ids as $booking_id ) {
				$booking = new WC_Booking( $booking_id );
			}
		}

		$booking_time = $booking->get_start_date( null, null, true ); // echo $booking_time;
		
		$timezone = $booking->get_local_timezone();
		date_default_timezone_set($timezone);
		$current_timpstamp = time();
		
		$booking_timestamp = strtotime($booking_time); // 00:00:00
		$current_timestamp_start = strtotime(date("Y-m-d  H:i:s", mktime(0,0,0,date("n", $current_timpstamp),date("j",$current_timpstamp) ,date("Y", $current_timpstamp))));  
		
		$diffDays = $booking_timestamp - $current_timestamp_start;
		$diffDays = $diffDays / (60 * 60 * 24);

		if (floor($diffDays) == 0) {
			$daysText = 'Today ' . date('g:i A', $booking_timestamp);
		} else if (floor($diffDays) == 1) {
			$daysText = 'Tomorrow';
		} else if (floor($diffDays) == -1) {
			$daysText = 'Yesterday';
		} else if ($diffDays > 0) {
			$daysText = 'in ' . floor($diffDays) . ' days';
		} else {
			$daysText = abs(floor($diffDays)) . ' days ago';
		}
		/* END Calculate the number of days from booking */
		?>

		<div class="woocommerce-orders-table-wrapper<?php echo $diffDays < 0 ? ' past' : ''; ?>">
			<div class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
				<div class="woocommerce-orders-table__row barber">
					
					<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
						<?php if ('order-number' === $column_id) { ?>
						<div class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>">
							<div class="barber-profile-column">
								<div class="profile-img" style="background:#C4C4C4;background-image:url(<?php echo wp_get_attachment_url( wc_get_product($product_id[0])->get_image_id() ); ?>)"></div>
								<span class=""><?php echo esc_html( $product_name[0] ); ?></span>
							</div>
						</div>
						<?php } else if ('rating' === $column_id) { ?>
						<?php
							global $wpdb;
							global $current_user;
								get_currentuserinfo();
							$commenter = $current_user;
						
							$dupe = $wpdb->prepare(
								"SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_content = %s AND comment_approved != 'trash' AND ( comment_author = %s ",
								wp_unslash( $product_id[0] ),
								wp_unslash( $customer_order->ID ),
								wp_unslash( $commenter->display_name)
							);

							if ($commenter->user_email) {
								$dupe .= $wpdb->prepare(
									'AND comment_author_email = %s ',
									wp_unslash( $commenter->user_email )
								);
							}
			
							$dupe .= $wpdb->prepare(
								') LIMIT 1'
							);
							// echo $dupe;
							$dupe_id = $wpdb->get_var( $dupe );
							
							if ($dupe_id) {
								$comment_meta = $wpdb->prepare(
									"SELECT meta_value FROM wp_commentmeta WHERE comment_id = %d AND meta_key = 'rating'",
									wp_unslash( $dupe_id )
								);
								$comment_meta .= $wpdb->prepare(
									' LIMIT 1'
								);
								$rating = $wpdb->get_var( $comment_meta );
							} else {
								$rating = null;
							}



							if ($dupe_id && $rating) { ?>
							<div id="review_form" class="review_form rated">
							<?php } else { ?>
							<div id="review_form" class="review_form">
							<?php }

							if ($diffDays < 1) { /* Only allow users to review orders in the past */

								$comment_form = array(
									/* translators: %s is product title */
									'title_reply'         => '',
									/* translators: %s is product title */
									'title_reply_to'      => esc_html__( 'Leave a rating for %s', 'woocommerce' ),
									'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
									'title_reply_after'   => '</span>',
									'comment_notes_after' => '',
									'label_submit'        => esc_html__( 'Leave a rating', 'woocommerce' ),
									'logged_in_as'        => '',
									'comment_field'       => '',
								);

								$name_email_required = (bool) get_option( 'require_name_email', 1 );
								$fields = array(
									'author' => array(
										'label'    => __( 'Name', 'woocommerce' ),
										'type'     => 'text',
										'value'    => $commenter->display_name,
										'required' => $name_email_required,
									),
									'email'  => array(
										'label'    => __( 'Email', 'woocommerce' ),
										'type'     => 'email',
										'value'    => $commenter->user_email,
									),
								);

								
								
								$comment_form['fields'] = array();
								foreach ( $fields as $key => $field ) {
									$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
									$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

									if ( $field['required'] ) {
										$field_html .= '&nbsp;<span class="required">*</span>';
									}

									$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

									$comment_form['fields'][ $key ] = $field_html;
								}

								$account_page_url = wc_get_page_permalink( 'myaccount' );
								if ( $account_page_url ) {
									/* translators: %s opening and closing link tags respectively */
									$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
								}

								$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating">' . esc_html__( '', 'woocommerce' ) . '</label><select name="rating" id="rating" data-rating="'. $rating .'">
										<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
										<option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
										<option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
										<option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
										<option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
										<option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
									</select></div>';
								if($dupe_id && $rating){
									$comment_form['comment_field'] .= '<p class="individual_rating">'. $rating .'</p>';
								}
								$comment_form['comment_field'] .= '<p class="comment-form-order-id" style="display:none"><label for="comment"></label><input type="hidden" name="comment" value="'.$customer_order->ID.'"></p>';
								comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ), $product_id[0] );
							} ?>
							</div>
						<?php } else if ('order-actions' === $column_id) { ?>
						<small class="booking-time">
							<?php echo $daysText; ?>
						</small>
						<?php } else { ?>
						<div></div>
						<?php } ?>
					<?php endforeach; ?>

				</div>
				
				<?php foreach($order->get_items() as $item_id => $item) { ?>
					<?php foreach($item->get_formatted_meta_data() as $meta_id => $meta) { ?>
						<div class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
							<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
								<div class="woocommerce-orders-table__cell woocommerce-orders-table__cell_<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
									<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) { ?>
										<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

									<?php } elseif ( 'order-number' === $column_id ) { ?>
										<?php $serviceName = esc_html($meta->value); 
										echo $serviceName; ?>

									<?php } elseif ( 'order-date' === $column_id ) { ?>
										<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>

									<?php } elseif ( 'order-status' === $column_id ) { ?>
										<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>

									<?php } elseif ( 'order-total' === $column_id ) { ?>
										<?php
										/* translators: 1: formatted order total 2: total order items */
										
										preg_match_all('!\d+\.*\d*!', esc_html($meta->key), $matches);
										echo '$'.$matches[0][0];
										?>

									<?php } elseif ( 'order-actions' === $column_id ) { ?>
										<?php
										// $actions = wc_get_account_orders_actions( $order );

										// if ( ! empty( $actions ) ) {
										// 	foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
										// 		echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
										// 	}
										// }
										?>
										
										<a href="<?php echo get_permalink( $product_id[0] ); ?>#<?php echo preg_replace('/\s|_/', '-', strtolower($serviceName)); ?>" class="order-actions">Re-order</a>

										<?php /* <div class="woocommerce-orders-table__cell woocommerce-orders-table__cell_<?php echo esc_attr( $column_id ); ?>">
											Burst Fade
										</div>
										<div class="woocommerce-orders-table__cell woocommerce-orders-table__cell_order-total" data-title="Total">
											$1.00
										</div>
										<div class="woocommerce-orders-table__cell woocommerce-orders-table__cell_order-actions" data-title="Actions">
											<a class="order-actions" href="https://staging-urbarbr.kinsta.cloud/product/martin/#burst-fade"><span class="order-actions">Re-order</span></a>
										</div> */ ?>

									<?php } ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	
	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php endif; ?>

			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>
	<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'Browse products', 'woocommerce' ); ?></a>
		<?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
<style>
	.booking-time {
		font-weight: 500;
		white-space: nowrap;
	}

	.woocommerce-orders-table-wrapper {
		margin-top: 30px;
		padding: 10px 30px;
		background: #88A1B855;
	}
	.woocommerce-orders-table-wrapper.past {
		background: #f8f9f9;
	}

	.individual_rating {
		margin: 0 6px 0;
		font-weight: 500;
		line-height: 30px;
	}

	form.comment-form {
		display: flex;
		flex-wrap: nowrap;
		justify-content: space-between;
	}

	form.comment-form .comment-form-rating {
		margin-bottom: 0;
		display: flex;
		flex-wrap: nowrap;
		align-items: center;
	}

	form.comment-form .form-submit {
		margin: 0;
	}

	form.comment-form .form-submit .submit {
		font-size: 15px;
		padding: 5px 0 !important;
		margin-left: 10px !important;
	}

	.review_form.rated form.comment-form input[name="submit"] {
		display: none;
	}

	.review_form.rated form.comment-form .comment-form-rating .stars {
		pointer-events: none;
	}

	form.comment-form input[name="submit"] {
		background-color: transparent !important;
		background: transparent;
		/*-color: #90C3F2 !important; */
		text-transform: none;
		padding: 0 !important;

		border: 0 !important;
		margin: 0 !important;
		font-weight: 500 !important;
		color: var(--urbarbr-softblue) !important;
	}

	form.comment-form .comment-form-rating label[for="rating"] {
		display: none;
	}

	form.comment-form .comment-form-rating .stars {
		margin-bottom: 0;
	}

	.stars span {
		display: flex;
		font-size: 0;
	}

	.stars a {
		width: 24px;
		text-align: center;
		text-decoration: none;
	}

	.stars a:before, .stars a:hover~a:before, .stars a.active~a:before, .stars.selected:hover a:hover~a:before {
		content: "\f149";
		color: #FFC702;
		font-weight: 100;
	}

	.stars a:before {
		font-size: 20px;
		font-family: "woodmart-font";
	}

	.stars:hover a:before, .stars.selected a:before, .stars.selected:hover a:before {
		content: "\f148";
		color: #EABE12;
	}

	.profile-img {
		height: 44px;
		width: 44px;
		min-width: 44px;
		margin-right: 20px;
		background-size: cover !important;
		background-repeat: no-repeat !important;
		background-position: center !important;
		object-fit: cover;
		text-align: center;
		clip-path: circle();
	}

	.barber-profile-column {
		display: flex;
		flex-wrap: nowrap;
		align-items: center;
	}

	.barber-profile-column span {
		/* width:160px; */
	}

	.order-actions {
		color: #0A9E44;
		cursor: pointer;
	}



	.woocommerce-orders-table.woocommerce-MyAccount-orders {
		margin-bottom: 15px;
		font-weight: 600;
		color: var(--urbarbr-shade);
		font-size: 16px;
	}

	.woocommerce-orders-table__row.order {
		display: flex;
		margin-top: 10px;
		background: white;
		border: 0;
		font-weight: 600;
		color: var(--urbarbr-shade);
	}
	.woocommerce-orders-table__row.order:first-child {
		margin-top: 0;
	}

	.woocommerce-orders-table__row.order>.woocommerce-orders-table__cell_order-number {
		margin-right: auto;
	}

	.woocommerce-orders-table__row.order>.woocommerce-orders-table__cell {
		padding: 15px 20px;
		white-space: nowrap;
	}

	.woocommerce-orders-table__row.barber>.woocommerce-orders-table__header {
		margin-right: auto;
		padding: 15px 0px;
	}


	.woocommerce-orders-table__row.barber {
		display: flex;
		align-items: center;
	}

	.woocommerce-orders-table__row.barber>.review_form {
		padding: 0 15px;
	}

	.woocommerce-orders-table__row.barber> :not(:last-child) {
		padding-right: 15px;
	}

	.woocommerce-orders-table__row.order>.woocommerce-orders-table__cell:not(:last-child) {
		padding-right: 0;
	}

	@media (max-width: 360px) {
		.woocommerce-orders-table.woocommerce-MyAccount-orders {
			font-size: 14px;
		}
		.woocommerce-orders-table-wrapper {
			padding: 10px 20px;
		}
	}

	@media (max-width: 991px) {
		.woocommerce-orders-table__row.barber {
			flex-wrap: wrap;
		}
		.woocommerce-orders-table__row.barber>.review_form {
			width: 100%;
			order: 1;
			padding: 6px 0 5px !important;
		}
	}

	#review_form input[type="submit"].loading {
		padding-left: 30px !important;
		background-image: url("data:image/svg+xml;base64,PHN2ZyB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDE1MCAxNTAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+DQogICAgPHN0eWxlPg0KICAgICAgICBzdmcgew0KICAgICAgICAgICAgd2lkdGg6IDMwcHg7DQogICAgICAgICAgICBhbmltYXRpb246IGxvYWRpbmcgM3MgbGluZWFyIGluZmluaXRlOw0KICAgICAgICAgICAgLXdlYmtpdC10cmFuc2Zvcm0tb3JpZ2luOiBjZW50ZXIgY2VudGVyOw0KICAgICAgICAgICAgLW1vei10cmFuc2Zvcm0tb3JpZ2luOiBjZW50ZXIgY2VudGVyOw0KICAgICAgICAgICAgLW1zLXRyYW5zZm9ybS1vcmlnaW46IGNlbnRlciBjZW50ZXI7DQogICAgICAgICAgICAtby10cmFuc2Zvcm0tb3JpZ2luOiBjZW50ZXIgY2VudGVyOw0KICAgICAgICAgICAgdHJhbnNmb3JtLW9yaWdpbjogY2VudGVyIGNlbnRlcjsNCiAgICAgICAgfQ0KICAgICAgICBjaXJjbGUgew0KICAgICAgICAgICAgY29sb3I6ICM2MjgxQjM7DQogICAgICAgICAgICBhbmltYXRpb246IGxvYWRpbmctY2lyY2xlIDJzIGxpbmVhciBpbmZpbml0ZTsNCiAgICAgICAgICAgIHN0cm9rZTogY3VycmVudENvbG9yOw0KICAgICAgICAgICAgZmlsbDogdHJhbnNwYXJlbnQ7DQogICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybS1vcmlnaW46IGNlbnRlciBjZW50ZXI7DQogICAgICAgICAgICAtbW96LXRyYW5zZm9ybS1vcmlnaW46IGNlbnRlciBjZW50ZXI7DQogICAgICAgICAgICAtbXMtdHJhbnNmb3JtLW9yaWdpbjogY2VudGVyIGNlbnRlcjsNCiAgICAgICAgICAgIC1vLXRyYW5zZm9ybS1vcmlnaW46IGNlbnRlciBjZW50ZXI7DQogICAgICAgICAgICB0cmFuc2Zvcm0tb3JpZ2luOiBjZW50ZXIgY2VudGVyOw0KICAgICAgICB9DQogICAgICAgIEBrZXlmcmFtZXMgbG9hZGluZyB7DQogICAgICAgICAgICAwJSB7DQogICAgICAgICAgICAgICAgdHJhbnNmb3JtOiByb3RhdGUoMCk7DQogICAgICAgICAgICB9DQoNCiAgICAgICAgICAgIDEwMCUgew0KICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKDM2MGRlZyk7DQogICAgICAgICAgICB9DQogICAgICAgIH0NCiAgICAgICAgQGtleWZyYW1lcyBsb2FkaW5nLWNpcmNsZSB7DQogICAgICAgICAgICAwJSB7DQogICAgICAgICAgICAgICAgc3Ryb2tlLWRhc2hvZmZzZXQ6IDANCiAgICAgICAgICAgIH0NCg0KICAgICAgICAgICAgMTAwJSB7DQogICAgICAgICAgICAgICAgc3Ryb2tlLWRhc2hvZmZzZXQ6IC02MDA7DQogICAgICAgICAgICB9DQogICAgICAgIH0NCiAgICA8L3N0eWxlPg0KICAgIDxjaXJjbGUgY3g9Ijc1IiBjeT0iNzUiIHI9IjYwIiBzdHJva2UtZGFzaG9mZnNldD0iMCIgc3Ryb2tlLWRhc2hhcnJheT0iMzAwIiBzdHJva2UtbWl0ZXJsaW1pdD0iMTAiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLXdpZHRoPSIxNCIgLz4NCiAgICA8c3R5bGUgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGh0bWwiIHR5cGU9InRleHQvY3NzIj48L3N0eWxlPg0KPC9zdmc+")
		background-size: 20px;
		background-position: 5px;
	}
</style>

<script>
jQuery(document).ready(function($){
	
	$( 'select[name="rating"]' ).each(function(){
		if($(this).data('rating')){
			rating_text = '<p class="stars selected"><span>';
			if($(this).data('rating') == '1'){
				rating_text += '<a class="star-1 active" href="#">1</a>\
				<a class="star-2" href="#">2</a>\
				<a class="star-3" href="#">3</a>\
				<a class="star-4" href="#">4</a>\
				<a class="star-5" href="#">5</a>';
			}else if($(this).data('rating') == '2'){
				rating_text += '<a class="star-1" href="#">1</a>\
				<a class="star-2 active" href="#">2</a>\
				<a class="star-3" href="#">3</a>\
				<a class="star-4" href="#">4</a>\
				<a class="star-5" href="#">5</a>';
			}else if($(this).data('rating') == '3'){
				rating_text += '<a class="star-1" href="#">1</a>\
				<a class="star-2" href="#">2</a>\
				<a class="star-3 active" href="#">3</a>\
				<a class="star-4" href="#">4</a>\
				<a class="star-5" href="#">5</a>';
			}else if($(this).data('rating') == '4'){
				rating_text += '<a class="star-1" href="#">1</a>\
				<a class="star-2" href="#">2</a>\
				<a class="star-3" href="#">3</a>\
				<a class="star-4 active" href="#">4</a>\
				<a class="star-5" href="#">5</a>';
			}else{
				rating_text += '<a class="star-1" href="#">1</a>\
				<a class="star-2" href="#">2</a>\
				<a class="star-3" href="#">3</a>\
				<a class="star-4" href="#">4</a>\
				<a class="star-5 active" href="#">5</a>';
			}
			rating_text += '</span></p>';
		}else{
			rating_text = '<p class="stars"><span>\
				<a class="star-1" href="#">1</a>\
				<a class="star-2" href="#">2</a>\
				<a class="star-3" href="#">3</a>\
				<a class="star-4" href="#">4</a>\
				<a class="star-5" href="#">5</a>\
				</span></p>';
		}
		$(this).hide().before(rating_text);
	});
	
	
	console.log($('.comment-respond p.stars a'));
	$(document).on( 'click', '.comment-respond p.stars a', function(e) {
		e.preventDefault();
		console.log('clicked');
		var $star   	= $( this ),
			$rating 	= $( this ).closest( '.comment-respond' ).find( 'select[name="rating"]' ),
			$container 	= $( this ).closest( '.stars' );

		$rating.val( $star.text() );
		console.log( $star.text());
		$star.siblings( 'a' ).removeClass( 'active' );
		$star.addClass( 'active' );
		$container.addClass( 'selected' );

		return false;
	} )

	$(document).on( 'click', '.comment-respond input[name="submit"]', function() {
		console.log('clicked submit');
		var $rating = $( this ).closest( '.comment-respond' ).find( 'select[name="rating"]' ),
			rating  = $rating.val();

		if ( $rating.length > 0 && ! rating && wc_single_product_params.review_rating_required === 'yes' ) {
			window.alert( wc_single_product_params.i18n_required_rating_text );

			return false;
		}



		let form = $(this).parents('form'); console.log(573,'form:', form);
		if (!$('.status').length) form.prepend('<small class="status"></small>');
		let statusdiv = form.find('.status');

		$.ajax({
			type: "POST",
			url: form.attr('action'),
			data: form.serialize(),
			error: function(XMLHttpRequest, textStatus, errorThrown) {
					console.log(1,XMLHttpRequest);
					console.log(2,textStatus);
					console.log(3,errorThrown);

				if (errorThrown == "Conflict") var text = "You've already submitted a rating.";
				else var text = "Please select a star rating";

				statusdiv.html(`<span class="error">${text}</span>`);
			},
			success: function(data, textStatus) {
					console.log(4,data);
					console.log(5,textStatus);
				if (data == "success" || textStatus == "success") {
					statusdiv.html('<span class="success">Thanks for submitting your rating.</span>');
				}
				else {
					statusdiv.html('<span class="error">Please wait a minute before submitting your next rating.</span>');
					commentform.find('textarea[name=comment]').val('');
				}
			}
		});

		return false;
		
	} );
})

</script>