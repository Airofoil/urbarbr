<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
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

foreach ( $order->get_items() as $item_id => $item ) { 

	// echo '<pre>'; print_r($item);  echo '</pre>';
	$booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
	foreach ( $booking_ids as $booking_id ) {
		$booking = new WC_Booking( $booking_id );
		$booking_time = $booking->get_start_date( null, null, wc_should_convert_timezone( $booking ) );

		// echo '<pre>'; print_r($booking_time);  echo '</pre>';
	}

	$strings = array();
	$product_id = $item['product_id'];

	$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail', array(80, 80) );
	$product_name = $item->get_name();
	
	$product = $item->get_product();
	
	foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
		$strings[] = $meta->key . ' ' . $meta->value;
	}

	$services_count = 0;

	foreach ($strings as $string) {
		preg_match('/\d+\.?\d*/', $string, $matches);
		$service_prices[$services_count] = $matches[0];
		$service_items[$services_count] = substr($string, strpos($string, ") ") + 1);
		$services_count++;
	}
}

$item_totals = $order->get_order_item_totals();

?>

<div class="woocommerce-order" style="text-align: center; max-width: 500px; padding: 50px;">

	<?php
	if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );
		?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<img style="width: 120px;" src="<?php echo get_site_url() . "/wp-content/uploads/2022/03/Group-18923.png"?>">
			<h2 style="font-weight: 700; color: #03090E;">Payment Successful</h2>
			<p style="color: #284158;">You successfully paid for your booking. You can now continue using UB Saloon</p>

			<h4 style="font-weight: 600; font-size: 20px; line-height: 20px; color: #292727; opacity: 0.8; margin: 30px 0px 15px;">Booking Details</h4>
			<div style="border: 1px solid #8896A2; box-sizing: border-box; border-radius: 10px; padding: 30px; margin-bottom: 40px;">
				<table class="td" cellspacing="0" cellpadding="6" style="width: 100%;">
					<thead>
						<tr>
							<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>; padding-left: 0; border-bottom: 1px solid #8896A2; font-size: 14px; font-weight: 500;"><?php esc_html_e( $product_name, 'woocommerce' ); ?></th>
							<th class="td" scope="col" style="text-align:right; border-bottom: 1px solid #8896A2; font-size: 14px; font-weight: 500;"><?php esc_html_e( $booking_time, 'woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						for ($i = 0; $i < $services_count; $i++) {
							?>
							<tr>
								<td style="padding-left: 0; font-style: normal; font-weight: 500; color: #70808F; font-size: 14px;"><?php echo $service_items[$i]?></td>
								<td style="text-align: right; font-style: normal; font-weight: 500; color: #70808F; font-size: 14px;"><?php echo "$" . $service_prices[$i]?></td>
							</tr>
							<?php
						}

						if ( $item_totals ) {
							$i = 0;
							foreach ( $item_totals as $key => $total ) {
								$i++;
								// echo '<pre>'; print_r($key);  echo '</pre>';
								if ($key == "payment_method" || $key == "order_total" || $key == "cart_subtotal") {
								} else {
								?>
									<tr>
										<td style="padding-left: 0; font-style: normal; font-weight: 700; color: #303030;"><?php echo wp_kses_post( $total['label'] ); ?></td>
										<td style="text-align: right;"><?php echo " " . wp_kses_post( $total['value'] ); ?></td>
									</tr>
								<?php
								}
							}
						}
						?>
					</tbody>
					<tfoot>
						<?php
						$item_totals = $order->get_order_item_totals();

						// echo '<pre>'; print_r($item_totals);  echo '</pre>';

						if ( $item_totals ) {
							?>
								<tr>
									<td class="td total" style="padding-left: 0; text-align:left; color: #03090E; border-top: 1px solid #8896A2; font-weight: 500; font-size: 14px;"><?php echo wp_kses_post( $item_totals['order_total']['label'] ); ?></td>
									<td class="td total" style="text-align:right; color: #03090E; border-top: 1px solid #8896A2; font-weight: 500; font-size: 14px;"><?php echo wp_kses_post( $item_totals['order_total']['value'] ); ?></td>
								</tr>
							<?php
						}
						?>
					</tfoot>
				</table>
			</div>

		<?php endif; ?>



	<?php else : ?>

		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'woocommerce' ), null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

	<?php endif; ?>

</div>
