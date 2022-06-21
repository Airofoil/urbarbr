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
            //--$(this).addClass('entered');

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

		if (!$('.searchform.woodmart-ajax-search input:not(.entered):not([type="hidden"])').length || ($('#booking-date-search').val() && $('#your-location-search').val())) {
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
    
    /*-$("#your-location-search").on("keyup", function(){
        if($(this).val()!=""){
            $(".your-location-search .dropdown-menu").addClass("location_drop_down_hide");
        }
        else {
            $(".your-location-search .dropdown-menu").removeClass("location_drop_down_hide");
        }
    }); */

    // $('.your-location-search').on('input', function() {
    //     $(".your-location-search .dropdown-menu").addClass("location_drop_down_hide");
    // });

    $('body.home #your-location-search, body.home #booking-date-search, body.home .booking-services-search button').on('click focus', function() {
        $(this).removeClass('error-select-button error-date-select-field');
        $(this).parent().find('.search-error-message').fadeOut();
    });

    $('body.home .searchsubmit.btn').on('click', function(event){
        if ( !$('.booking-services-search').val() ) {
            // $(".booking-services-search button.btn").css('border-color', 'red!important');
            // $(".booking-services-search button.btn").css('border-width', '1px!important');
            // $(".booking-services-search button.btn").css('height', 'auto');
            $('.booking-services-search button.btn').addClass('error-select-button');
            if(!$('#error-search-service').length) {
                $( '<div class="search-error-message" id="error-search-service">please select a service</div>' ).insertAfter( ".booking-services-search button.btn" );
            } else {
                $("#error-search-service").fadeIn();
            }
            event.preventDefault();
        } else {
            $('.booking-services-search button.btn').removeClass('error-select-button');
            $("#error-search-service").fadeOut();
        }

        // alert($('#booking-date-search.entered').length);

        if( !$('#booking-date-search').val() ) {
            $('#booking-date-search').addClass('error-date-select-field');
            if(!$('#error-search-date-select').length) {
                $( '<div class="search-error-message" id="error-search-date-select">select date & time</div>' ).insertAfter( "#booking-date-search" );
                $( '<div class="search-error-message" id="error-search-date-select-mobile">when?</div>' ).insertAfter( "#booking-date-search" );
            } else {
                $("#error-search-date-select").fadeIn();
                $("#error-search-date-select-mobile").fadeIn();
            }
            event.preventDefault();
        } else {
            $('#booking-date-search').removeClass('error-date-select-field');
            $("#error-search-date-select").fadeOut();
            $("#error-search-date-select-mobile").fadeOut();
        }

        if( !$('#your-location-search').val() ) {
            $('#your-location-search').addClass('error-date-select-field');
            if(!$('#error-search-location').length) {
                $( '<div class="search-error-message" id="error-search-location">enter your address</div>' ).insertAfter( ".your-location-search .dropdown-menu" );
                $( '<div class="search-error-message" id="error-search-location-mobile">where?</div>' ).insertAfter( ".your-location-search .dropdown-menu" );
            } else {
                $("#error-search-location").fadeIn();
                $("#error-search-location-mobile").fadeIn();
            }
            event.preventDefault();
        } else {
            $('#your-location-search').removeClass('error-date-select-field');
            $("#error-search-location").fadeOut();
            $("#error-search-location-mobile").fadeOut();
        }

        //write to cookie here
        var serviceText = "";
        if($(".home .filter-option").length>0){
            serviceText = $(".home .filter-option").text();
        }
        var searchData = {
            "service": serviceText,
            "date": $("#booking-date-search").val(),
            "time": $('#booking-time').val()
        };
        document.cookie = "lastSearch="+JSON.stringify(searchData);
    });
	
	//setTimeout(() => {
    if ($('.product-grid-item').length) {
        
        var barberList = $('.products.elements-grid');
        var barbers = barberList.children('.product-grid-item');

        if($('.product-grid-item[data-mindate]').length){ // If there is a min-date set, filter the products by their available date and time slots
            
            $('.wd-loop-footer.products-footer a.wd-products-load-more').hide(); // Hide the 'Load more' button
            var products = '';
            var minDate = '';
            var maxDate = '';
            var formattedDate = '';

            barbers.each(function () {
                products += $(this).data('id');
                products += ",";
                minDate = $(this).data('mindate');
                maxDate = $(this).data('maxdate');
                formattedDate = $(this).data('formatteddate'); console.log('formattedDate:',formattedDate);

                if ($(this).data('distance')) $(this).find('.jac-products-header-top-left').append(`<div class="distance" style="float:unset;padding:0;">${$(this).data('distance')} km</div>`); // Add the distance field on the tile
            });

            products = products.replace(/,*$/, ""); console.log(products);
            
            console.log('Finding available slots...');
            $.ajax({
                url: "/wp-json/wc-bookings/v1/products/slots",
                type: "get",
                data: {
                    min_date: minDate,
                    max_date: maxDate,
                    product_ids: products
                },
                success: function (response) { console.log('  success:',response);
                    var formattedDateTime = new Date(formattedDate.replace(' ','T')).getTime();
                    var paramTime = new URLSearchParams(window.location.search).get('booking-time');
                    var slotsFound = 0;

                    for (i in response.records) { //-console.log(response.records[i].product_id, $('[data-id=' + response.records[i].product_id + ']').find('.jac-barber-name').text().trim(), response.records[i]);
                        if (response.records[i].available == 1) {
                            if (formattedDate.slice(formattedDate.length - 5) == "00:00" && !paramTime) { // If the booking time is not provided, or the default 00:00 ...
                                console.log('No time provided, showing product',response.records[i].product_id)
                                $('[data-id=' + response.records[i].product_id + ']').show(); // Show the product
                                slotsFound += 1;
                            }
                            else { // ... Otherwise, check if the booking time fits the slot
                                var startDateTime = new Date(response.records[i].date).getTime();
                                var endDateTime = startDateTime + response.records[i].duration * 60 * 1000;
                                var minimumStartTime = new Date();
                                minimumStartTime.setHours(minimumStartTime.getHours() + 1); // The current datetime, preventing slots in the past from being selectable
                                minimumStartTime = minimumStartTime.getTime();
                                //-console.log('    ',formattedDateTime >= startDateTime, formattedDateTime <= endDateTime, formattedDateTime > minimumStartTime,formattedDateTime,startDateTime,formattedDateTime,endDateTime,formattedDateTime,minimumStartTime)
                                if (formattedDateTime >= startDateTime && formattedDateTime < endDateTime && formattedDateTime > minimumStartTime) {
                                    $('[data-id=' + response.records[i].product_id + ']').show(); // Show that product, since it has a matching time slot available
                                    slotsFound += 1;
                                    //-console.log('Match:',new Date(formattedDateTime),new Date(startDateTime),new Date(endDateTime),new Date(minimumStartTime),$('[data-id=' + response.records[i].product_id + ']').find('.jac-barber-name').text().trim())
                                }
                            }
                        }
                    }

                    if (slotsFound == 0) {
                        console.log("No Slots Found");
                        var buttonHtml = "<p>There are no barbers available at this time</p><a href='/product-category/barber/' class='btn wd-load-more'><span class='load-more-lablel'>View All Barbers</span></a>"
                        $('.wd-loop-footer.products-footer a.wd-products-load-more').hide(); // Hide the load more button
                        if ($('.wd-loop-footer.products-footer').length) $('.wd-loop-footer.products-footer').append(buttonHtml); // Append the message and button to the footer, if it exists, else before the product grid
                        else $(buttonHtml).insertBefore($('.products.elements-grid'));
                    } else {
                        $('.wd-loop-footer.products-footer a.wd-products-load-more').show();
                    }
                },
                error: function (xhr) {
                    console.error(xhr);
                }
            });
        }

        if ($('.product-grid-item[data-distance]').length) {
            var sortList = Array.prototype.sort.bind(barbers); // Bind barbers to the sort method so we don't have to travel up all these properties more than once.

            sortList(function(a, b) {
                var aDistance = $(a).data('distance'); // Cache distance value from the first element (a) and the next sibling (b)
                var bDistance = $(b).data('distance');

                if (!aDistance) return 1;
                if (!bDistance) return -1;

                if (aDistance < bDistance) { //-console.log('placing',$(a).find('.jac-barber-name').text().trim(),'before',$(b).find('.jac-barber-name').text().trim() )
                    return -1; // Returning -1 will place element `a` before element `b`
                }
                if (aDistance > bDistance) { //-console.log('placing',$(b).find('.jac-barber-name').text().trim(),'before',$(a).find('.jac-barber-name').text().trim() )
                    return 1; // Returning 1 will do the opposite
                }

                return 0; // Returning 0 leaves them as-is
            });
            
            barberList.append(barbers);
        }
    }
	/*@@else {
		console.log("No nearby Barbers found");
		var buttonHtml="<p>There are no barbers available at this location</p><a href='https://staging-urbarbr.kinsta.cloud/product-category/barber/' class='btn wd-load-more'><span class='load-more-lablel'>View All Barbers</span></a>"
		$('.wd-loop-footer.products-footer a.wd-products-load-more').hide();
		$('.wd-loop-footer.products-footer').append(buttonHtml)
	} */
    //-if ($('.product-grid-item').length) filterBarbers(); // See above

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

    $( 'header' ).on( 'change', '.booking-services-search', function() {
        if($('header .searchform .booking-services-search .filter-option').length){
            
            //console.log("Selector changed to "+$('header .searchform .booking-services-search .filter-option').text());
            if(typeof(Cookies.get('lastSearch'))=='string'){
                var searchData = JSON.parse(Cookies.get('lastSearch'));
                searchData.service=$('header .searchform .booking-services-search .filter-option').text();
                document.cookie = "lastSearch="+JSON.stringify(searchData)+"; path=/";
            } else {
                var searchData = {
                    "service": $('header .searchform .booking-services-search .filter-option').text()
                };
                document.cookie = "lastSearch="+JSON.stringify(searchData)+"; path=/";
            }
        }
        
    });

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
                //$(".wc-pao-addon-container .wc-pao-addon-checkbox[value='"+searchValues.service+"']").click();
                $(".wc-pao-addon-container .wc-pao-addon-wrap:contains('"+searchValues.service+"') .wc-pao-addon-checkbox").click();
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

                let timerId = "";

                function setDateTime() {
                    if(dateSet==false){
                        if($(".wc-bookings-date-picker .bookable[data-month='"+month+"'] a[data-date='"+day+"']").length > 0){
                            $(".wc-bookings-date-picker .bookable[data-month='"+month+"'] a[data-date='"+day+"']").parent().trigger("click");
                            
                            dateSet=true;
                            $(".wc-bookings-date-picker .bookable[data-month='"+month+"'] a[data-date='"+day+"']").parent().trigger("input");
                            $(".wc-bookings-date-picker .bookable[data-month='"+month+"'] a[data-date='"+day+"']").parent().trigger("change");
                        }
                    }

                    if(timeSet==false){
                        if($("#wc-bookings-form-start-time").length > 0){
                            /*$("#wc-bookings-form-start-time option[selected='selected']").prop("selected", false);
                            $("#wc-bookings-form-start-time option:contains('"+timeValue+"')").prop('selected', 'selected');

                            $("#wc-bookings-form-start-time option:contains('"+timeValue+"')").change();

                            $("#wc-bookings-form-start-time").val($("#wc-bookings-form-start-time option:contains('"+timeValue+"')").val()).change();
                            $(".wc-bookings-time-block-picker").trigger("DOMSubtreeModified");
                            $('#wc-bookings-form-start-time').change();
                            $('#wc-bookings-form-start-time').trigger('change');
                            console.log("Ran change commands");*/
                            
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

