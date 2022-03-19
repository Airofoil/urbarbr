jQuery(document).ready(function ($){

    /* Change Wishlist to Favourites on barber page */
    setTimeout(() => {
        if ($('.tinvwl_add_to_wishlist-text').length) $('.tinvwl_add_to_wishlist-text').text('Add to Favourites');
    }, 20);

	$('input.wc-pao-addon-checkbox').on('click',function(){
        if ($('input.wc-pao-addon-checkbox:checked').length > 0) {
            $('.single_add_to_cart_button').removeClass('no-service-disable');
        }else{
            $('.single_add_to_cart_button').addClass('no-service-disable');
        }
    })
	$(".single_add_to_cart_button").ready(function () {
		console.log($(".single_add_to_cart_button"));
        if ($('input.wc-pao-addon-checkbox:checked').length > 0) {
            $('.single_add_to_cart_button').removeClass('no-service-disable');
        } else {
            $('.single_add_to_cart_button').addClass('no-service-disable');
        }
    });

    $('.single_add_to_cart_button').click( function( event ) {
        if($('input.wc-pao-addon-checkbox:checked').length > 0){

//             if($('div.wd-cart-empty').length) {

//             } else {
//                 if( ! confirm( 'Do you want to remove the existing booking and add this to cart?' ) ) {
//                     event.preventDefault();
//                     event.stopPropagation();
//                 }
//             }

        }else{
            event.preventDefault();
            event.stopPropagation();
            confirm("Please select service.");
        }
    });
    
    setTimeout(function() {
        $("#billing_address_1").attr("placeholder", "Enter address and select from the dropdown")
    }, 100);

    $('h5.widget-title').unbind().click(e => $(e.target).toggleClass('open')); /* ++ For the footer toggleable menus */

    $('.searchform input').on('change, blur', function() {
		if ($(this).is(':valid')) $(this).addClass('entered');
		if (!$('.searchform.woodmart-ajax-search input:not(.entered):not([type="hidden"])').length) {
			$('.searchform .searchsubmit').addClass('entered');
		}
	})

    $('.wd-tools-element.wd-header-mobile-nav > a').click(a => {
        $(a).toggleClass('open');
    });
	
    // insert payment title
    $('<h3 id="payment_method">Payment Method</h3>').insertBefore('#payment');
	//setTimeout(() => {
	// 		$.datetimepicker.setDateFormatter('moment');
	// 		$('#booking-date-search').datetimepicker.setDateFormatter('moment');
	
// 	//setTimeout(() => 
// 	$('#booking-date-search').datetimepicker();
	//, 1000);

	//}, 1000);
	/*{
        //sideBySide: true,
        //format: 'YYYY-MM-DD H:i',
        minDate: 0,
        //maxDate: 60
//      minDate: moment({h:9}),
//      maxDate: moment({h:16})
    });*/

});
