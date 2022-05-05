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
		if ($(this).is(':valid')) {
            $(this).addClass('entered');

            if ($('#your-location-search').length) {
                $('#your-location-search').on('blur', setTimeout(() => {
                    let location = document.getElementById('your-location-search');
                    if (!location.value) {
                        $(location).removeClass('invalid');
                        return;
                    }

                    if (location.value.length < 4) {
                        $(location).addClass('invalid');
                        return;
                    }

                    $.getJSON(`https://maps.googleapis.com/maps/api/geocode/json?address=${location.value}&key=AIzaSyBrFVuDdduHECkgQNAsFuv0XgBW-3jLw60&sensor=false`, function(data) { console.log(41, data);
                        if (data.status !== 'OK' || !data["results"]) {
                            $(location).addClass('invalid');
                            return;
                        }
                        if (data["results"][0]) {
                            $(location).removeClass('invalid');
                            document.getElementById('location_coords').value = data["results"][0].geometry.location.lat + ',' + data["results"][0].geometry.location.lng;
                            document.cookie = `location_lat_long=${data["results"][0].geometry.location.lat + ',' + data["results"][0].geometry.location.lng}; path=/`;
                        }
                    });
                }, 60));
            }
        }

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
        if ($(this).parent() && $(this).parent().find('input').first()){
            $(this).parent().find('input').removeClass('entered');
            $(this).parent().find('input').first().val('');
        }
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

    $('body.home .searchsubmit.btn').click(function(event){
        if ( $(".home .filter-option").text() == "" || $(".home .filter-option").text() == "Select a service") {
            // $(".booking-services-search button.btn").css('border-color', 'red!important');
            // $(".booking-services-search button.btn").css('border-width', '1px!important');
            // $(".booking-services-search button.btn").css('height', 'auto');
            $('.booking-services-search button.btn').addClass('error-select-button');
            if(!$('#error-search-service').length) {
                $( '<div class="search-error-message" id="error-search-service">please select a service</div>' ).insertAfter( ".booking-services-search button.btn" );
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

        //write to cookie here
        var searchData = {
            "service": $(".home .filter-option").text().toLowerCase(),
            "date": $("#booking-date-search").val(),
            "time": $('#booking-time').val()
        };
        document.cookie = "lastSearch="+JSON.stringify(searchData);
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
              var slotsFound=0;
			  for(var [key,slotinfo] of Object.entries(response.records)){
                if(slotinfo.available == 1){
                  var startDateTime = new Date(slotinfo.date).getTime();
                  var endDateTime = startDateTime + slotinfo.duration*1000;
                  var minimumStartTime = new Date();
                  minimumStartTime.setHours(minimumStartTime.getHours() + 1);
                  minimumStartTime=minimumStartTime.getTime();
                  if(formattedDateTime >= startDateTime && formattedDateTime <=endDateTime && formattedDateTime > minimumStartTime){
                      $('[data-id='+slotinfo.product_id+']').show();
                      slotsFound +=1;
                  }
				}
              }
              console.log(response);
              if(slotsFound==0){
                  console.log("No Slots Found");
                  var buttonHtml="<p>There are no barbers available at this time</p><a href='https://staging-urbarbr.kinsta.cloud/product-category/barber/' class='btn wd-load-more'><span class='load-more-lablel'>View All Barbers</span></a>"
                  $('.wd-loop-footer.products-footer a.wd-products-load-more').hide();
                  $('.wd-loop-footer.products-footer').append(buttonHtml)
              }
            },
            error: function(xhr) {
              //Do Something to handle error
            }
          });
    }

    $("body").on('DOMSubtreeModified', ".wc-bookings-time-block-picker", updateValidEnddate);
    function updateValidEnddate(){
        $('#wc-bookings-form-end-time option[value!=0]').hide();
        //service*duration + buffer time.
        var allowedBlock = $('.wc-pao-addon-checkbox:checked').length * 3;
        if($('#wc-bookings-form-end-time option[value="'+allowedBlock+'"]').length){
            $('#wc-bookings-form-end-time option[value="'+allowedBlock+'"]').show();
            $('#wc-bookings-form-end-time option[value="'+allowedBlock+'"]').prop('selected','true');
            $('.wc-bookings-booking-form-button').removeClass('disabled');
            $('.wc_bookings_field_duration').val(allowedBlock);
        }else{
            $('#wc-bookings-form-end-time option[value="0"]').show();
            $('#wc-bookings-form-end-time option[value="0"]').prop('selected','true');
        }
    }
    $('.wc-pao-addon-checkbox').on('change',function(){
        var servicesCount = $('.wc-pao-addon-checkbox:checked').length;
        Cookies.set("servicesCount", servicesCount);
        $('.selection-start-date.ui-datepicker-current-day').trigger('click');
    })

    $('input[placeholder*="posts"]').each(function() { // Change any 'posts' input placeholders to 'barbers'
        $(this).attr('placeholder',$(this).attr('placeholder').replace('posts', 'barbers'));
    });

    $('.create-account-button').attr('href',window.location.origin + '/registration-form');

    if ($('body').hasClass('page-id-8724')) { // Replace the 'Product Name' table headings with 'Barber Name' on the Favourites page
        $('.product-name .tinvwl-full').text('Barber Name');
        $('.product-name .tinvwl-mobile').text('Barber');

        $('.tinv-wishlist .wishlist_item').each(function() { // Replace the 'Add to Cart' button with a direct 'View Barber' link to the barber, much like the thumbnail link
            $(this).find('.product-action').html(`<a href="${$(this).find('.product-thumbnail > a').attr('href')}" class="single_add_to_cart_button button">View Barber</a>`);
        });
    }

    if ($('body').hasClass('single-product')) {
        if(typeof(Cookies.get('lastSearch'))=='string'){
            var searchValues = JSON.parse(Cookies.get('lastSearch'));
            if(searchValues.service){
                $(".wc-pao-addon-container .wc-pao-addon-checkbox[value='"+searchValues.service+"']").click();
            }
            if(searchValues.date){
                var dateSet=false;
                var timeSet=false;

                let dates= searchValues.date.split("-");
                let month=parseInt(dates[1])-1;
                let day=parseInt(dates[2]);
                searchValues = JSON.parse(Cookies.get('lastSearch'));
                let times=searchValues.time.split(":");
                let pastNoon="am";
                if(times[0]>=12){
                    pastNoon="pm";
                    
                }
                if(times[0]>12){
                    times[0]-=12;
                }
                let timeValue=times[0]+":"+times[1]+" "+pastNoon;
                console.log("Time value is: "+timeValue);


                let timerId = "";
                

                function setDateTime() {if(dateSet==false){
                    console.log("Setting time");
                        if($(".wc-bookings-date-picker .bookable[data-month='"+month+"'] a[data-date='"+day+"']").length > 0){
                            $(".wc-bookings-date-picker .bookable[data-month='"+month+"'] a[data-date='"+day+"']").parent().trigger("click");
                            dateSet=true;
                        }
                    }

                    if(timeSet==false){
                        if($("#wc-bookings-form-start-time").length > 0){
                            //$("#wc-bookings-form-start-time option:contains('"+timeValue+"')").attr('selected', 'selected');
                            //$("#wc-bookings-form-start-time").trigger("change");
                            timeSet=true;
                        }
                    }
                    if(dateSet == true && timeSet == true){
                        clearTimeout(timerId);
                    } else {
                        timerId=setTimeout(setDateTime, 1000);
                    }
                    
                }
                setDateTime();
            }
        }

    }

});
