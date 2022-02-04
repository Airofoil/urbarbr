
jQuery(document).ready(function ($) {
   
    $('input.wc-pao-addon-checkbox').on('click',function(){
        if ($('input.wc-pao-addon-checkbox:checked').length > 0) {
            $('.single_add_to_cart_button').removeClass('disabled');
        }else{
            $('.single_add_to_cart_button').addClass('disabled');
        }
    })
    $('.single_add_to_cart_button').click(function (event) {
        if ($('input.wc-pao-addon-checkbox:checked').length > 0) {

            //             if($('div.wd-cart-empty').length) {

            //             } else {
            //                 if( ! confirm( 'Do you want to remove the existing booking and add this to cart?' ) ) {
            //                     event.preventDefault();
            //                     event.stopPropagation();
            //                 }
            //             }

        } else {
            event.preventDefault();
            event.stopPropagation();
            confirm("Please select service.");
        }
    });


    $('#booking-date-search').datetimepicker({
        sideBySide: true,
        format: 'YYYY-MM-DD LT',
        minDate: new Date(),
        // 		minDate: moment({h:9}),
        //    	maxDate: moment({h:16})
    });


    // 	$.datetimepicker.setDateFormatter('moment');

});
