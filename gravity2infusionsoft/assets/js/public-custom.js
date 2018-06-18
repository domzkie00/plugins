jQuery(document).ready(function ($) {

	$(document).on('click', '.gform_button.button', function(e){
		e.preventDefault();
		var form = '#' + $(this).closest('form').attr('id');
		var exp = form.split('_');
		var id = exp[1];
		var values = {};

		$('#gform_fields_'+id).find('li').each(function(){
			$(this).find('.ginput_container').find('input').each(function(){
				var inputID = $(this).attr('id');
				if(inputID) {
					if(inputID.indexOf('ginput') != -1) {
						var exp = inputID.split('_');
						if(exp[1] === 'quantity') {
							values['#'+inputID] = $(this).val();
						}
					}
				}
			});
		});

		/*$.each(values, function(key, val){
			if($(key).length) {
				$(key).val(val);
			}
		});*/

		$(form).trigger('submit');
	});

	if(create_IS_customer_modal) {
		$(create_IS_customer_modal).appendTo('body');

		setTimeout(function(){
			var createISCustomerModal = $('#ISCreateCustomer');
			createISCustomerModal.find('.mapped-email').text(mapped_email);
			createISCustomerModal.find('#email').val(mapped_email);
			createISCustomerModal.modal('show');

			setTimeout(function(){
				createISCustomerModal.find('#fname').focus();
			}, 100);
		}, 200);
	}

	$(document).on('submit', '#ISCreateCustomer form', function(e){
		var form = $(this).closest('form');
		var form_values = form.serializeArray();
		var createISCustomerModal = $('#ISCreateCustomer');

		createISCustomerModal.find('.submit-btn-area button[type="submit"]').css('opacity', 0);
		createISCustomerModal.find('.submit-btn-area .loader').fadeIn();

		$.post(
            g2inf_script.ajaxurl,
            { 
            action : 'createContactToIS',
            data: form_values
            }, 
            function( result, textStatus, xhr ) {
                var data = JSON.parse(result);
                if(data.result) {
                	var alertMsg = "<div class='alert alert-success success-contact-create'>"+
			          "Successfully created a customer contact record in InfusionSoft."+
			        "</div>";

                	$('.entry-content .gform_wrapper').prepend(alertMsg);
                	createISCustomerModal.modal('hide');

                	setTimeout(function(){
                		$('.success-contact-create').fadeOut(300, function(){
                			$(this).remove();	
                		});
                	}, 5000);
                }
            }).fail(function(error) {
                console.log(error);
            }).done(function() {
                
            }
        );

        e.preventDefault();
	})

});