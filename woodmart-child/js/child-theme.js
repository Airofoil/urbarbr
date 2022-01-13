jQuery(document).ready(function ($){

    $('.single_add_to_cart_button').click( function( event ) {
        if( ! confirm( 'Are you sure you want to add the product?' ) ) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
    
});