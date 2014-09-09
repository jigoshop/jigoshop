jQuery(function($){
	// Fade out the status message
	$('.updated').delay(2500).fadeOut(1500);

	// jQuery Tools range tool
	$(":range").rangeinput();

	// Countries
	$('select#jigoshop_allowed_countries').change(function(){
		// hide-show multi_select_countries
		if ($(this).val()=="specific") {
			$(this).parent().parent().next('tr').show();
		} else {
			$(this).parent().parent().next('tr').hide();
		}
	}).change();

	// permalink double save hack (do we need this anymore -JAP-)
	//$.get('<?php echo admin_url('options-permalink.php') ?>');
});
