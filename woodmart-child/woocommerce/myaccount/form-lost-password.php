<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.2
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>
	<div class="registration-wrapper" >
	<h2 style="text-align: center;">Recover Password</h2>

		<form method="post" class="woocommerce-ResetPassword lost_reset_password">

			<p class="woocommerce-form-row woocommerce-form-row--first form-row">
				<label style="text-align: center; font-size: 14px" for="user_login"><?php esc_html_e( 'Enter your email to recover your password', 'woocommerce' ); ?></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" />
			</p>

			<div class="clear"></div>

			<?php do_action( 'woocommerce_lostpassword_form' ); ?>

			<p class="woocommerce-form-row form-row">
				<input type="hidden" name="wc_reset_password" value="true" />
				<button type="submit"  style ="width: max-content; border-radius: 3px" class="woocommerce-Button button form-item-right" value="<?php esc_attr_e( 'Reset password', 'woocommerce' ); ?>">Send reset link</button><?php /* esc_html_e( 'Send OTP', 'woocommerce' ); */ ?>
				<a class ="form-item-left" style="text-decoration: underline;" href="/my-account">Try to log in</a>
			</p>

			<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
		</form>
	</div>
<?php
do_action( 'woocommerce_after_lost_password_form' );
