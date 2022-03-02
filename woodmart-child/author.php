<?php
/**
 * The main template file
 */

if( function_exists( 'woodmart_is_woo_ajax' ) && woodmart_is_woo_ajax() ) {
	do_action( 'woodmart_main_loop' );
	die();
}

get_header(); ?>

<?php 

	// Get content width and sidebar position
	$content_class = woodmart_get_content_class();

?>

<div class="site-content <?php echo esc_attr( $content_class ); ?>" role="main">

    <?php woodmart_get_opt( 'blog_author_bio' ) ?>

    <div class="author-info">
        <div class="author-avatar">
            <?php
            /**
             * Filter the author bio avatar size.
             *
             * @since Twenty Thirteen 1.0
             *
             * @param int $size The avatar height and width size in pixels.
             */
            $author_bio_avatar_size = apply_filters( 'twentythirteen_author_bio_avatar_size', 120 );
            echo get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size, '', 'author-avatar' );
            ?>
        </div><!-- .author-avatar -->

        <style>
            /* Darken the header to the dark blue */
            .whb-sticky-prepared .whb-main-header {
                background: #284158;
            }

            /* Max the content's width to 500px */
            body .site-content > .author-info {
                max-width: 500px;
                margin: auto;
                margin-top: 60px;
            }

            /* Override the max-heights of the author image - JDH (temporary) */
            body .author-info .avatar {
                max-width: 120px;
            }
            body .author-info .author-avatar {
                margin-top: -90px;
                margin-left: -60px;
            }

            /* Increase icon sizes */
            body .icons-size-default .wd-social-icon {
                width: 60px;
                height: 60px;
            }
            body .wd-social-icons.icons-size-default .wd-icon {
                font-size: 24px;
                line-height: 60px;
            }
            body .wd-social-icons .wd-social-icon {
                background-color: #284158;
                color: #FFF !important;
            }
            /* The Background for the profile page */
            body .website-wrapper {
                background: no-repeat center/cover;
                background-image: linear-gradient(#0000 5%, #FFF 45%),url(https://www.urbarbr.com.au/wp-content/uploads/2021/12/barberChairCenteredBG.png);
            }
            .main-page-wrapper, .whb-main-header {
                background: 0 !important;
            }
            body .wd-prefooter {
                padding: 0;
            }
        </style>

        <div class="author-description">
            <h1><?php echo get_the_author(); ?></h1>
            <p><b>@<?php echo get_the_author_meta('user_login'); ?></b></p>
        </div><!-- .author-description -->
        
        <div class="wd-social-icons icons-design-colored-alt icons-size-default color-scheme-dark social-follow social-form-circle text-center">
            <?php
            if (!empty(get_the_author_meta('facebook'))) : ?>
            <a rel="noopener noreferrer nofollow" href="<?php echo get_the_author_meta('facebook'); ?>" target="_blank" class="wd-social-icon social-facebook" aria-label="Facebook social link">
                <span class="wd-icon"></span>
            </a>
            <?php endif;

            if (!empty(get_the_author_meta('instagram'))) : ?>
            <a rel="noopener noreferrer nofollow" href="<?php echo get_the_author_meta('instagram'); ?>" target="_blank" class="wd-social-icon social-instagram" aria-label="Instagram social link">
                <span class="wd-icon"></span>
            </a>
            <?php endif;

            if (!empty(get_the_author_meta('linkedin'))) : ?>
            <a rel="noopener noreferrer nofollow" href="<?php echo get_the_author_meta('linkedin'); ?>" target="_blank" class="wd-social-icon social-linkedin" aria-label="LinkedIn social link">
                <span class="wd-icon"></span>
            </a>
            <?php endif;

            if (!empty(get_the_author_meta('user_email'))) : ?>
            <a rel="noopener noreferrer nofollow" href="mailto:<?php echo get_the_author_meta('user_email'); ?>" target="_blank" class="wd-social-icon social-email" aria-label="Email link">
                <span class="wd-icon"></span>
            </a>
            <?php endif;

            if (!empty(get_user_meta(get_current_user_id(), 'phone', true))) : ?>
            <a rel="noopener noreferrer nofollow" href="tel:<?php echo get_user_meta(get_current_user_id(), 'phone', true); ?>" target="_blank" class="wd-social-icon social-email" aria-label="Mobile number">
                <span class="wd-icon">
                    <svg version="1.1" id="phone" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 30 30" style="enable-background:new 0 0 93.465 93.465;" xml:space="preserve">
                    <g><path d="M92.781,74.906L73.302,55.428c-0.896-0.896-2.349-0.896-3.244,0L59.825,65.661c-2.782-0.769-10.176-3.475-18.683-11.979
                            c-8.826-8.827-12.402-17.625-13.289-20.113l10.173-10.173c0.431-0.43,0.673-1.014,0.673-1.623s-0.242-1.192-0.673-1.622
                            L18.547,0.672c-0.896-0.896-2.349-0.896-3.245,0L3.916,12.059c-0.148,0.147-0.273,0.313-0.376,0.495l-0.021,0.024
                            c-9.065,11.141,0.009,36.045,20.66,56.697c14.696,14.695,32.502,24.19,45.365,24.19c4.592,0,8.373-1.163,11.242-3.458l0.268-0.184
                            c0.117-0.08,0.225-0.169,0.324-0.27L92.781,78.15c0.431-0.431,0.673-1.015,0.673-1.622C93.454,75.92,93.211,75.337,92.781,74.906z"
                            /></g>
                    </svg>
                </span>
            </a>
            <?php endif; ?>

            <!-- <a rel="noopener noreferrer nofollow" href="/signup" class="wd-social-icon" aria-label="Discount link">
                <span class="wd-icon" style="font-weight: bold; font-family: Tahoma;">%</span>
            </a> -->

        </div>

        <br>
        
        <div class="profile-btns text-center">
            <?php if (get_the_author_meta('user_login') == "jake") : ?>
            <p><a href="tel:0450462335" class="btn btn-full-width"><i class="fa fa-phone" style="margin-right:5px"></i> 0450 462 335</a></p>
            <?php endif; ?>
            <p><a href="/signup" class="btn btn-full-width">30% OFF Referral Code</a></p>
            <p><a href="/" class="btn btn-full-width" style="/*background: #284158; color: white;*/">‹ Home Page</a></p>
        </div>

        <p class="author-area-info">
            <?php echo get_the_author_meta( 'description' ); ?>
            <?php echo get_the_author_meta( 'user_description' ); ?>

            <?php /*
            <a class="author-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
                <?php printf( wp_kses( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>', 'woodmart' ), array( 'span' => array('class') ) ), get_the_author() ); ?>
            </a>
            <?php /* $phone = get_user_meta(get_current_user_id(),'phone',true); echo $phone;?>1
            <?php $phone = get_user_meta(get_current_user_id(),'phone_number',true); echo $phone;?>2
            <?php $phone = get_user_meta(get_current_user_id(),'mobile',true); echo $phone;?>3
            <?php $phone = get_user_meta(get_current_user_id(),'phone-number',true); echo $phone;?>4
            <?php $phone = get_user_meta(get_current_user_id(),'billing_phone',true); echo $phone;?>5
            <?php $phone = get_user_meta(get_current_user_id(),'address',true); echo $phone;?>6
            <?php $phone = get_user_meta(get_current_user_id(),'shipping_company',true); echo $phone;?>7
            <?php $phone = get_user_meta(get_current_user_id(),'shipping_phone',true); echo $phone;
            */ ?>
        </p>
    </div><!-- .author-info -->

</div><!-- .site-content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
