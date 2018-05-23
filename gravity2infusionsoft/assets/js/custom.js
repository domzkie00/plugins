jQuery(document).ready(function ($) {
	if($('#infusionProductsTable').length) {
		$('#infusionProductsTable').DataTable();
	}

	$(document).on('change', '.toggle-infusion-products input', function(){
		var status = $(this).is(':checked');
		
		$.post(
            g2inf_admin_script.ajaxurl,
            { 
            action : 'set_infusion_products_use_status',
            data: status
            }, 
            function( result, textStatus, xhr ) {
                var data = JSON.parse(result);
            }).fail(function(error) {
                console.log(error);
            }).done(function() {
                
            }
        );
	});
});