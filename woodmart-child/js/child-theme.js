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
        $("#billing_address_1").attr("placeholder", "Enter and select address")
    }, 100);

    $('h5.widget-title').unbind().click(e => $(e.target).toggleClass('open')); /* ++ For the footer toggleable menus */

    $('.searchform input').on('change blur', function() {
		if ($(this).is(':valid')) $(this).addClass('entered');
		if (!$('.searchform.woodmart-ajax-search input:not(.entered):not([type="hidden"])').length) {
			$('.searchform .searchsubmit').addClass('entered');
		}
        if ($('#booking-date-search').hasClass('entered') && $('#your-location-search').hasClass('entered')) {
            $('.searchform .searchsubmit').prop('disabled','').addClass('entered');
        }
	});

    $('body').on('click', function() {
        if ($('.mobile-nav.wd-opened').length) $('.wd-header-mobile-nav > a').addClass('nav-open');
        else $('.wd-header-mobile-nav > a').removeClass('nav-open');
    });
	
    $('button.input-clear').unbind().on('click', function() {
        if ($(this).parent() && $(this).parent().find('input').first()) $(this).parent().find('input').first().val('');
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
    
    $("#your-location-search").on("keyup", function(){
        if($(this).val()!=""){
            $(".your-location-search .dropdown-menu").addClass("location_drop_down_hide");
        }
        else {
            $(".your-location-search .dropdown-menu").removeClass("location_drop_down_hide");
        }
    });

    // $('.your-location-search').on('input', function() {
    //     $(".your-location-search .dropdown-menu").addClass("location_drop_down_hide");
    // });

    $('.searchsubmit.btn').click(function(event){
        if ( $(".home .filter-option").text() == "" || $(".home .filter-option").text() == "Select a service") {
            // $(".booking-services-search button.btn").css('border-color', 'red!important');
            // $(".booking-services-search button.btn").css('border-width', '1px!important');
            // $(".booking-services-search button.btn").css('height', 'auto');
            $('.booking-services-search button.btn').addClass('error-select-button');
            if(!$('#error-search-service').length) {
                $( '<div class="search-error-message" id="error-search-service">please select a sercive</div>' ).insertAfter( ".booking-services-search button.btn" );
            } else {
                $("#error-search-service").css('display', 'inline-block');
            }
            event.preventDefault();
        } else {
            $('.booking-services-search button.btn').removeClass('error-select-button');
            $("#error-search-service").css('display', 'none');
        }

        // alert($('#booking-date-search.entered').length);

        if( !$('#booking-date-search.entered').length ) {
            $('#booking-date-search').addClass('error-date-select-field');
            if(!$('#error-search-date-select').length) {
                $( '<div class="search-error-message" id="error-search-date-select">select date & time</div>' ).insertAfter( "#booking-date-search" );
                $( '<div class="search-error-message" id="error-search-date-select-mobile">when?</div>' ).insertAfter( "#booking-date-search" );
            } else {
                $("#error-search-date-select").css('display', 'inline-block');
                $("#error-search-date-select-mobile").css('display', 'inline-block');
            }
            event.preventDefault();
        } else {
            $('#booking-date-search').removeClass('error-date-select-field');
            $("#error-search-date-select").css('display', 'none');
            $("#error-search-date-select-mobile").css('display', 'none');
        }

        if( !$('#your-location-search.entered').length ) {
            $('#your-location-search').addClass('error-date-select-field');
            if(!$('#error-search-location').length) {
                $( '<div class="search-error-message" id="error-search-location">enter your address</div>' ).insertAfter( ".your-location-search .dropdown-menu" );
                $( '<div class="search-error-message" id="error-search-location-mobile">where?</div>' ).insertAfter( ".your-location-search .dropdown-menu" );
            } else {
                $("#error-search-location").css('display', 'inline-block');
                $("#error-search-location-mobile").css('display', 'inline-block');
            }
            event.preventDefault();
        } else {
            $('#your-location-search').removeClass('error-date-select-field');
            $("#error-search-location").css('display', 'none');
            $("#error-search-location-mobile").css('display', 'none');
        }
    });
	
	if($('.product-grid-item[data-mindate]').length){
        var products='';
        var minDate='';
        var maxDate='';
        var formattedDate='';
        $( ".product-grid-item" ).each(function( index ) {
            products +=$(this).data('id');
            products +=",";
            minDate = $(this).data('mindate');
            maxDate = $(this).data('maxdate');
            formattedDate = $(this).data('formatteddate');

        });
        products = products.replace(/,*$/, "");
        console.log(products);

        $.ajax({
            url: "/wp-json/wc-bookings/v1/products/slots",
            type: "get",
            data: { 
              min_date: minDate, 
              max_date: maxDate, 
              product_ids: products
            },
            success: function(response) {
			  var formattedDateTime = new Date(formattedDate).getTime();
			  for(var [key,slotinfo] of Object.entries(response.records)){
                if(slotinfo.available == 1){
                  var startDateTime = new Date(slotinfo.date).getTime();
                  var endDateTime = startDateTime + slotinfo.duration*1000;
                  if(formattedDateTime >= startDateTime && formattedDateTime <=endDateTime){
                      $('[data-id='+slotinfo.product_id+']').show();
                  }
				}
              }
              console.log(response);
            },
            error: function(xhr) {
              //Do Something to handle error
            }
          });
    }

});
