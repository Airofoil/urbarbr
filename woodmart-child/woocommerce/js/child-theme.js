jQuery(document).ready(function ($) {

    $('.single_add_to_cart_button').click(function (event) {
        if ($('input.wc-pao-addon-checkbox:checked').length > 0) {
            if (!confirm('Are you sure you want to add the product?')) {
                event.preventDefault();
                event.stopPropagation();
            }
        } else {
            event.preventDefault();
            event.stopPropagation();
            confirm("Please select service.");
        }
    });

});