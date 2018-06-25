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

		var article_mapped_email = $(this).closest('article').attr('data-ismapped-email');
		if(article_mapped_email) {
			var createISCustomerModal = $('#ISCreateCustomer');
				createISCustomerModal.find('.mapped-email').text(article_mapped_email);
				createISCustomerModal.find('#email').val(article_mapped_email);
				createISCustomerModal.modal('show');
		}

		if(!article_mapped_email) {
			$(form).trigger('submit');
		}
	});

	if(create_IS_customer_modal) {
		if(multi_posts) {
			$.each(multi_posts, function(){
				if(this['is_id'] == 'No IS record found.' && this['mapped_email']) {
					$(create_IS_customer_modal).appendTo('body');

					setTimeout(function(){
						var createISCustomerModal = $('#ISCreateCustomer');
						createISCustomerModal.removeAttr('data-backdrop');
						createISCustomerModal.removeAttr('data-keyboard');
					}, 200);

					var p_id = this['id'];
					var email = this['mapped_email'];
					$('article').each(function(){
						var art_id = $(this).attr('id');
						var exp = art_id.split('-');
						art_id = parseInt(exp[1]);
						
						if(p_id == art_id) {
							$(this).attr('data-ismapped-email', email);
						}
					});
				}
			});
		} else {
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
	}

	$(document).on('submit', '.ISCreateCustomer form', function(e){
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
	});

	function getUrlParameter(sParam) {
	    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;

	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');

	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? true : sParameterName[1];
	        }
	    }
	};

	if(getUrlParameter('cout') && getUrlParameter('fvals')) {
		var vals = getUrlParameter('fvals');
		vals = vals.split(',');

		var f_arr = [];
		$.each(vals, function(){
			f_arr.push(parseInt(this));
		});

		var cnt = 0;
		$('.ginput_quantity').each(function(){
			$(this).val(f_arr[cnt]);
			cnt++;
		});

		if(validate_IS_ccard_modal && ($('.gform_confirmation_wrapper').length === 0)) {
			$(validate_IS_ccard_modal).appendTo('body');
			var validateISCCardModal = $('#ISCreditCard');
				validateISCCardModal.find('#is-cid').val(contact_id);
				$('#ISCreditCard').modal('show');
		}
	}

	if($('.gform_footer .gform_button.button').length) {
		var gform_id = $('.gform_footer .gform_button.button').closest('form').attr('id');
		
		$(document).on('submit', '#'+gform_id, function(e){
			var fields = null;
			$(this).find('.ginput_quantity').each(function(){
				var val = $(this).val();
				var validateISCCardModal = $('#ISCreditCard');

				validateISCCardModal.find('.submit-btn-area button[type="submit"]').css('opacity', 0);
				validateISCCardModal.find('.submit-btn-area .loader').fadeIn();

				if(fields === null) {
					fields = val;
				} else {
					fields += ',' + val;
				}
			});

			if(!getUrlParameter('cout') && !getUrlParameter('fvals')) {
				fields = JSON.stringify(fields);
				fields = fields.replace(/\"/g, "");
				window.location.href = window.location.href + "?cout=" + contact_id + "&fvals=" + fields + "&gform_id=" + gform_id;

				e.preventDefault();
			}
		});
	}

	$(document).on('submit', '#ISCreditCard', function(e){
		var form_values = $(this).find('form').serializeArray();
		var gform_id = getUrlParameter('gform_id');

		form_values.push({name: 'field_values', value: getUrlParameter('fvals')});

		$.post(
            g2inf_script.ajaxurl,
            { 
            action : 'createContactCCardToIS',
            data: form_values
            }, 
            function( result, textStatus, xhr ) {
                var data = JSON.parse(result);
                if(data.result) {
                	$('#'+gform_id).trigger('submit');
                }
            }).fail(function(error) {
                console.log(error);
            }).done(function() {
                
            }
        );

		e.preventDefault();
	});
});