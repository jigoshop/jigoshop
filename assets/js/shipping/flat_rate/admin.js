"use strict";
jQuery(function($){
	$('select#jigoshop_flat_rate_availability').change(function(){
		if($(this).val() == 'specific'){
			$(this).parent().parent().next('tr').show();
		} else {
			$(this).parent().parent().next('tr').hide();
		}
	}).change();
});
