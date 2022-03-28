<?php
/**
 * Email Footer
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-footer.php.
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
?>
															</div>
														</td>
													</tr>
												</table>
												<!-- End Content -->
											</td>
										</tr>
									</table>
									<!-- End Body -->
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<!-- Footer -->
						<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
							<tr>
								<td valign="top">
									<table border="0" cellpadding="10" cellspacing="0" width="100%">
										<tr>
											<td colspan="2" valign="middle" id="credit">
												<?php 
													// echo wp_kses_post( wpautop( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) ); 
												?>
												<p style="font-family: 'Inter'; font-style: normal; font-weight: 400; font-size: 15px; color: #9F9F9F;">Need Help? Visit our <a href="" style="color: #365277;">Help Center</a></p>
											</td>
										</tr>
										<tr>
											<td colspan="2" valign="middle" style="text-align: center;">
												<a rel="noopener noreferrer nofollow" href="https://www.instagram.com/urbarbr" target="_blank"><img src="<?php echo get_site_url() . "/wp-content/uploads/2022/03/Group-46.png"?>"></a>
												<a rel="noopener noreferrer nofollow" href="https://www.facebook.com/urbarbr" target="_blank"><img src="<?php echo get_site_url() . "/wp-content/uploads/2022/03/Group-45.png"?>"></a>
												<a rel="noopener noreferrer nofollow" href="https://www.tiktok.com/@urbarbr" target="_blank"><img src="<?php echo get_site_url() . "/wp-content/uploads/2022/03/Group-13.png"?>"></a>
											</td>
										</tr>
										<tr>
											<td colspan="2" valign="middle" style="text-align: center;">
												<p style="font-family: 'Poppins'; font-style: normal;font-weight: 400; font-size: 15px; color: #9F9F9F;">Copyright 2022 UrBarbr. All rights reserved. We appreciate you!</p>
											</td>
										</tr>
										<tr>
											<td colspan="2" valign="middle" style="text-align: center;">
												<p style="font-family: 'LIBRARY 3 AM'; font-style: normal; font-weight: 400; font-size: 20px; color: #303030;">UrBarbr</p>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<!-- End Footer -->
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>