jQuery(document).ready(function ($) {

    $('.single_add_to_cart_button').click(function (event) {
        if ($('input.wc-pao-addon-checkbox:checked').length > 0) {

            if ($('div.wd-cart-empty').length) {

            } else {
                if (!confirm('Do you want to remove the existing booking and add this to cart?')) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }

        } else {
            event.preventDefault();
            event.stopPropagation();
            confirm("Please select service.");
        }
    });

    $('#booking-date-search').datetimepicker({
        ownerDocument: document,
        contentWindow: window,
        value: '',
        rtl: false,
        format: 'Y/m/d H:i',
        formatTime: 'H:i',
        formatDate: 'Y/m/d',
        startDate: false,// new Date(), '1986/12/08', '-1970/01/05','-1970/01/05',
        step: 60,
        monthChangeSpinner: true,
        closeOnDateSelect: false,
        closeOnTimeSelect: true,
        closeOnWithoutClick: true,
        closeOnInputClick: true,
        openOnFocus: true,
        timepicker: true,
        datepicker: true,
        weeks: false,
    });

    $.datetimepicker.setDateFormatter('moment');


});