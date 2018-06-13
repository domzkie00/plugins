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

    document.ondragstart = function(event) {
        var el = $(event.target);
        var is_infusion_product = false;

        is_infusion_product =  el.attr('data-infprod');
        if(is_infusion_product) {
            $(event.target).trigger('click');
        }
    };

    $(document).on('hover', '.infprodBTN', function(){
        $('.infprodBTN').removeClass('selected');
        $(this).addClass('selected');
    });

    /*var formFields = [];
    var is_infusion_product = false;
    var product_id = null;
    var product_name = '';
    var product_price = 0;
    document.ondragstart = function(event) {
        var el = $(event.target);
        var formFieldsArea = $('#gform_fields');
        var currentElementsInContainer = null;
        is_infusion_product = false;

        is_infusion_product = el.attr('data-infprod');
        product_id = el.attr('data-id');
        product_name = el.attr('data-pname');
        product_price = parseFloat(el.attr('data-price'));
        product_price = product_price.toFixed(2);

        formFields = [];
        if(el.attr('data-infprod')) {
            formFieldsArea.find('li.gfield').each(function(){
                formFields.push(this);
            });
        }
    };

    $( "#gform_fields" ).sortable({
        update: function() {
            var updated_formFieldsArea = $('#gform_fields');
            var updated_formFields = [];
            var difference = '';

            if(is_infusion_product) {
                setTimeout(function(){
                    updated_formFieldsArea.find('li.gfield').each(function(){
                        updated_formFields.push(this);
                    });

                    $.grep(updated_formFields, function(el) {
                        if($.inArray(el, formFields) == -1) {
                            difference = el;
                        }
                    });

                    //displays
                    $(difference).find('label.gfield_label').text(product_name);
                    $(difference).find('span.ginput_product_price').text('$'+product_price);

                    //hidden inputs
                    $(difference).find('.ginput_container').find('input:first-child').val(product_name);
                    $(difference).find('.ginput_container').find('input:nth-child(2)').val(product_price);
                }, 1000);
            }
        }
    });*/
});