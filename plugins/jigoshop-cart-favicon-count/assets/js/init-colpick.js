jQuery(document).ready(function($){
	$('.picker').each(function(){
		var color = $(this).val();
		$(this).css('border-color','#'+color);
		$(this).colpick({
			layout:'rgbhex',
			submit:0,
			color: color,
			colorScheme:'dark',
			onChange:function(hsb,hex,rgb,el,bySetColor) {
				$(el).css('border-color','#'+hex);
				if(!bySetColor) $(el).val(hex);
			}
		}).keyup(function(){
			$(this).colpickSetColor(this.value);
		});
	})
});