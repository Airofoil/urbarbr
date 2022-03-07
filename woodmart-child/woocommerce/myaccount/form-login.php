<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' ); ?>
	<div class="registration-wrapper" >

		<h2 style="text-align: center;"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></h2>
		<!-- ----<p style="text-align: center;">Become a member - don't miss out on deals, offers, discounts and bonus vouchers</p> -->
		<form class="woocommerce-form woocommerce-form-login login" method="post">

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username">Email address <span class="required"></span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required"></span></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" />
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="form-row last">
				<div class="clear">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme form-item-left">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" style="font-size:12px;" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
					</label>
					<p class="woocommerce-LostPassword lost_password form-item-right">
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'woocommerce' ); ?></a>
					</p>	
				</div>
				<div class="clear">
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button type="submit" style ="width: 35%; border-radius: 3px" class="woocommerce-button button woocommerce-form-login__submit form-item-right" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">Log in</button>
					<a class ="form-item-left" style="text-decoration: underline;" href="/registration-form">Become a member</a>
				</div>
			</div>
		
			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>
	</div>


<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
