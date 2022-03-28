<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';

// foreach ( $order->get_items() as $item_id => $item ) {
// 	// echo '<pre>'; print_r($item);  echo '</pre>';
// 	$booking_services = $item['addons'];
// 	echo '<pre>'; print_r($booking_services);  echo '</pre>';
// }

foreach ( $order->get_items() as $item_id => $item ) { 
	$strings = array();
	$product_id = $item['product_id'];

	$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'single-post-thumbnail', array(80, 80) );
	$product_name = $item->get_name();
	
	$product = $item->get_product();
	
	foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
		$strings[] = $meta->key . ' ' . $meta->value;
	}

	// echo '<pre>'; print_r($strings);  echo '</pre>';

	$services_count = 0;

	foreach ($strings as $string) {
		preg_match('/\d+\.?\d*/', $string, $matches);
		$service_prices[$services_count] = $matches[0];
		$service_items[$services_count] = substr($string, strpos($string, ") ") + 1);
		$services_count++;
	}

	// echo '<pre>'; print_r($service_prices);  echo '</pre>';
	// echo '<pre>'; print_r($service_items);  echo '</pre>';
}

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>


<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; border: unset;">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left; padding-left:0; border-bottom: 1px solid #E5E5E5;"><?php esc_html_e( 'Booking No.', 'woocommerce' ); ?></th>
			<td class="td" scope="col" style="text-align:right; border-bottom: 1px solid #E5E5E5;"><?php echo '0' . $order->get_order_number() ?></td>
		</tr>
		<tr>
			<th class="td" scope="col" style="text-align:left; padding-left:0; border-bottom: 1px solid #E5E5E5;"><?php esc_html_e( 'Booking Date', 'woocommerce' ); ?></th>
			<td class="td" scope="col" style="text-align:right; border-bottom: 1px solid #E5E5E5;"><?php echo wc_format_datetime( $order->get_date_created() ); ?></td>
		</tr>
		<tr>
			<th class="td" scope="col" style="text-align:left; padding-left:0; border-bottom: 1px solid #E5E5E5;"><?php esc_html_e( 'Booking Address', 'woocommerce' ); ?></th>
			<td class="td" scope="col" style="text-align:right; border-bottom: 1px solid #E5E5E5;"><?php echo $order->get_formatted_shipping_address() ?></td>
		</tr>
	</thead>
</table>
<?php /*echo wp_kses_post( $before . sprintf( __( '[Order #%s]', 'woocommerce' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );*/?>


<?php /* <h2>
	<?php
	if ( $sent_to_admin ) {
		$before = '<a class="link" href="' . esc_url( $order->get_edit_order_url() ) . '">';
		$after  = '</a>';
	} else {
		$before = '';
		$after  = '';
	}
	/* translators: %s: Order ID. * /
	echo wp_kses_post( $before . sprintf( __( '[Order #%s]', 'woocommerce' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
	?>


$booking = get_wc_booking( $booking_id );
$start_date = $booking->get_start_date();
</h2> */ ?>

<p style="margin-top: 30px;">Here's what you ordered:</p>

<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%;">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>; padding-left: 0;"><?php esc_html_e( 'Service', 'woocommerce' ); ?></th>
				<th class="td" scope="col" style="text-align:right;"><?php esc_html_e( 'Qty', 'woocommerce' ); ?></th>
				<th class="td" scope="col" style="text-align:right;"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="padding-left: 0;" colspan="3">
					<img style="width: 80px; border-radius: 10px;" src="<?php echo $image[0]; ?>" width="32"; height="32";>
					<span style="font-style: normal; font-weight: 400; color: #9F9F9F;"><?php echo $product_name ?></span>
				</td>
			</tr>
			<?php
			// echo wc_get_email_order_items( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			// 	$order,
			// 	array(
			// 		'show_sku'      => false,
			// 		'show_image'    => true,
			// 		'image_size'    => array( 32, 32 ),
			// 		'plain_text'    => false,
			// 		'sent_to_admin' => false,
			// 	)
			// );
			for ($i = 0; $i < $services_count; $i++) {
				?>
				<tr>
					<td style="padding-left: 0; font-style: normal; font-weight: 700; color: #303030;"><?php echo $service_items[$i]?></td>
					<td style="text-align: right;">1</td>
					<td style="text-align: right;"><?php echo "$" . $service_prices[$i]?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<tfoot>
			<?php
			$item_totals = $order->get_order_item_totals();

			if ( $item_totals ) {
				$i = 0;
				foreach ( $item_totals as $total ) {
					$i++;
					?>
					<tr>
						<td class="td total" colspan="3" style="text-align:right; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['label'] ); ?><?php echo " " . wp_kses_post( $total['value'] ); ?></td>
					</tr>
					<?php
				}
			}
			if ( $order->get_customer_note() ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
				</tr>
				<?php
			}
			?>
		</tfoot>
	</table>
</div>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
