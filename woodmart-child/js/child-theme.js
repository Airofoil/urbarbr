jQuery(document).ready(function ($){
<<<<<<< HEAD
    $('input.wc-pao-addon-checkbox').on('click',function(){
=======
	$('input.wc-pao-addon-checkbox').on('click',function(){
>>>>>>> df506173c7a594e81b1c74de25263592bafc4364
        if ($('input.wc-pao-addon-checkbox:checked').length > 0) {
            $('.single_add_to_cart_button').removeClass('no-service-disable');
        }else{
            $('.single_add_to_cart_button').addClass('no-service-disable');
        }
    })
<<<<<<< HEAD
      $(".single_add_to_cart_button").ready(function () {
          console.log($(".single_add_to_cart_button"));
=======
	  $(".single_add_to_cart_button").ready(function () {
		  console.log($(".single_add_to_cart_button"));
>>>>>>> df506173c7a594e81b1c74de25263592bafc4364
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
    

    $('#booking-date-search').datetimepicker({
        //sideBySide: true,
        //format: 'YYYY-MM-DD H:i',         
        minDate: 0,
        //maxDate: 60
//      minDate: moment({h:9}),
//      maxDate: moment({h:16})
    });

    setTimeout(function() {
        $("#billing_address_1").attr("placeholder", "Enter address and select from the dropdown")
    }, 100);

//  $.datetimepicker.setDateFormatter('moment');

});
