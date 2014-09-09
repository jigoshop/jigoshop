jQuery(function($){
	// Fade out the status message
	$('.updated').delay(2500).fadeOut(1500);

	// Countries
	$('#jigoshop_allowed_countries').on('change', function(){
		if($(this).val() == 'specific'){
			$('#jigoshop_specific_allowed_countries').closest('tr').show();
		} else {
			$('#jigoshop_specific_allowed_countries').closest('tr').hide();
		}
	}).change();
});
