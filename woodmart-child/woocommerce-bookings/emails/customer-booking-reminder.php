<?php
/**
 * Customer booking reminder email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/emails/customer-booking-reminder.php
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/bookings-templates/
 * @author  Automattic
 * @version 1.15.42
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<?php if ( $booking->get_order() ) : ?>
	<p>
	<?php
	/* translators: 1: billing first name */
	echo esc_html( sprintf( __( 'Hello %s', 'woocommerce-bookings' ), ( is_callable( array( $booking->get_order(), 'get_billing_first_name' ) ) ? $booking->get_order()->get_billing_first_name() : $booking->get_order()->billing_first_name ) ) );
	?>
	</p>
<?php endif; ?>

<?php 

	if ( $booking->get_order() ) {
		// echo '<pre>'; print_r($booking->get_order()->get_items());  echo '</pre>'; 
		foreach ( $booking->get_order()->get_items() as $item_id => $item ) { 
			$strings = array();
			
			foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
				$strings[] = $meta->key . ' ' . $meta->value;
			}
		
			$services_count = 0;
		
			foreach ($strings as $string) {
				preg_match('/\d+\.?\d*/', $string, $matches);
				// $service_prices[$services_count] = $matches[0];
				$service_items[$services_count] = substr($string, strpos($string, ") ") + 1);
				$services_count++;
			}
			// echo '<pre>'; print_r($service_prices);  echo '</pre>';
			// echo '<pre>'; print_r($service_items);  echo '</pre>';
		}
	}
?>

<p>
<?php
/* translators: 1: booking start date */
echo esc_html( sprintf( __( 'This is a reminder that your booking will take place on %1$s.', 'woocommerce-bookings' ), $booking->get_start_date() ) );
?>
</p>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<?php
		if ( count($service_items)>0 ) {
			$booked_services = implode( ', ', $service_items );
			?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php echo esc_html_e( 'Booked Service', 'woocommerce-bookings' ); ?></th>
				<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booked_services ); ?></td>
			</tr>
		<?php } ?>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Booked Barber', 'woocommerce-bookings' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->get_product()->get_title() ); ?></td>
		</tr>
		<?php if ( $booking->get_order() ) { ?>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Customer Name', 'woocommerce-bookings' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->get_order()->get_billing_first_name() ); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e( 'Booking ID', 'woocommerce-bookings' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->get_id() ); ?></td>
		</tr>
		<?php
		$resource = $booking->get_resource();
		$resource_label = $booking->get_product()->get_resource_label();

		if ( $booking->has_resources() && $resource ) :
			?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php echo ( '' !== $resource_label ) ? esc_html( $resource_label ) : esc_html__( 'Barber Name', 'woocommerce-bookings' ); ?></th>
				<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $resource->post_title ); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e( 'Booking Start Date', 'woocommerce-bookings' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->get_start_date() ); ?></td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e( 'Booking End Date', 'woocommerce-bookings' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $booking->get_end_date() ); ?></td>
		</tr>
		<?php if ( wc_should_convert_timezone( $booking ) ) : ?>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php esc_html_e( 'Time Zone', 'woocommerce-bookings' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( str_replace( '_', ' ', $booking->get_local_timezone() ) ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( $booking->has_persons() ) : ?>
			<?php
			foreach ( $booking->get_persons() as $id => $qty ) :
				if ( 0 === $qty ) {
					continue;
				}

				$person_type = ( 0 < $id ) ? get_the_title( $id ) : __( 'Person(s)', 'woocommerce-bookings' );
				?>
				<tr>
					<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php echo esc_html( $person_type ); ?></th>
					<td style="text-align:left; border: 1px solid #eee;"><?php echo esc_html( $qty ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
