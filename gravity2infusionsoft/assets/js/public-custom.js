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

});