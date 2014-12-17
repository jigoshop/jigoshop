jQuery(function($) {
	"use strict";
	if(jigoshop_params.message_hide_time > 0){
		setTimeout(function(){
			$('.jigoshop_message').slideUp('normal', function(){
				$(this).remove();
			});
		}, jigoshop_params.message_hide_time);
	}
	if(jigoshop_params.error_hide_time > 0){
		setTimeout(function(){
			$('.jigoshop_error').slideUp('normal', function(){
				$(this).remove();
			});
		}, jigoshop_params.error_hide_time);
	}
	// Lightbox
	if(jigoshop_params.load_fancybox){
		$('a.zoom').prettyPhoto({
			animation_speed: 'normal', /* fast/slow/normal */
			slideshow: 5000, /* false OR interval time in ms */
			autoplay_slideshow: false, /* true/false */
			show_title: false,
			theme: 'pp_default', /* pp_default / light_rounded / dark_rounded / light_square / dark_square / facebook */
			horizontal_padding: 50,
			opacity: 0.7,
			overlay_gallery: false,
			deeplinking: false,
			social_tools: false
		});
	}
	// Quantity buttons
	$("div.quantity, td.quantity").append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />');
	$(".plus").click(function(){
		var currentVal = parseInt($(this).prev(".qty").val());
		if(!currentVal || currentVal == "" || currentVal == "NaN") currentVal = 0;
		$(this).prev(".qty").val(currentVal + 1);
	});
	$(".minus").click(function(){
		var currentVal = parseInt($(this).next(".qty").val());
		if(currentVal == "NaN") currentVal = 0;
		if(currentVal > 0){
			$(this).next(".qty").val(currentVal - 1);
		}
	});
	/*################# VARIATIONS ###################*/
	//check if two arrays of attributes match
	function variations_match(attrs1, attrs2){
		var match = true;
		for(var name in attrs1){
			var val1 = attrs1[name].toLowerCase();
			if(typeof( attrs2[name] ) == 'undefined'){
				var val2 = 'undefined';
			} else {
				var val2 = attrs2[name].toLowerCase();
			}
			if(val1.length != 0 && val2.length != 0 && val1 != val2){
				match = false;
			}
		}
		return match;
	}

	//search for matching variations for given set of attributes
	function find_matching_variations(attributes){
		var matching = [];
		for(var i = 0; i < product_variations.length; i++){
			var variation = product_variations[i];
			if(variations_match(variation.attributes, attributes)){
				matching.push(variation);
			}
		}
		return matching;
	}

	//disable option fields that are unavaiable for current set of attributes
		function update_variation_values(variations){

		// Loop through selects and disable/enable options based on selections
		$('.variations select').each(function(index, el){
			var current_attr_select = $(el);
			// Hide all
			current_attr_select.find('option:gt(0)').hide()
			// Get name
			var current_attr_name = current_attr_select.attr('name');
			// Loop through variations
			for(var num in variations){
				var attributes = variations[num].attributes;
				for(var attr_name in attributes){
					var attr_val = attributes[attr_name];
					if(attr_name == current_attr_name){
						if(attr_val){
							current_attr_select.find('option[value="' + attr_val + '"]').show();
						} else {
							current_attr_select.find('option').show();
						}
					}
				}
			}
			// completely re-enable the previous select so 'Choose an option' isn't required to change selections
			current_attr_select.parent().prev().find('select').find('option:gt(0)').show();
		});
	}

	//show single variation details (price, stock, image)
	function show_variation(variation){
		var img = $('div.images img:eq(0)');
		var link = $('div.images a.zoom:eq(0)');
		var o_src = $(img).attr('original-src');
		var o_link = $(link).attr('original-href');
		var variation_image = variation.image_src;
		var variation_link = variation.image_link;
		var var_display;
		if(variation.same_prices) var_display = variation.availability_html;
		else var_display = variation.price_html + variation.availability_html;
		$('.single_variation').html(var_display);
		if(!o_src){
			$(img).attr('original-src', $(img).attr('src'));
		}
		if(!o_link){
			$(link).attr('original-href', $(link).attr('href'));
		}
		if(variation_image && variation_image.length > 1){
			$(img).attr('src', variation_image);
			$(link).attr('href', variation_link);
		} else {
			$(img).attr('src', o_src);
			$(link).attr('href', o_link);
		}
		$('.product_meta .sku').remove();
		$('.product_meta').append(variation.sku);
		$('.shop_attributes').find('.weight').remove();
		if(variation.a_weight){
			$('.shop_attributes').append(variation.a_weight);
		}
		$('.shop_attributes').find('.length').remove();
		if(variation.a_length){
			$('.shop_attributes').append(variation.a_length);
		}
		$('.shop_attributes').find('.width').remove();
		if(variation.a_width){
			$('.shop_attributes').append(variation.a_width);
		}
		$('.shop_attributes').find('.height').remove();
		if(variation.a_height){
			$('.shop_attributes').append(variation.a_height);
		}
		if(!variation.in_stock){
			$('.single_variation').slideDown();
		} else if(!variation.no_price) {
			$('.variations_button, .single_variation').slideDown();
		} else {
			$('.single_variation').slideDown();
		}
	}

	//when one of attributes is changed - check everything to show only valid options
	function check_variations(){
		$('form input[name=variation_id]').val('');
		$('.single_variation').text('');
		$('.variations_button, .single_variation').slideUp();
		$('.product_meta .sku').remove();
		$('.shop_attributes').find('.weight').remove();
		$('.shop_attributes').find('.length').remove();
		$('.shop_attributes').find('.width').remove();
		$('.shop_attributes').find('.height').remove();
		var all_set = true;
		var current_attributes = {};
		$('.variations select').each(function(){
			if($(this).val().length == 0){
				all_set = false;
			}
			current_attributes[$(this).attr('name')] = $(this).val();
		});
		var matching_variations = find_matching_variations(current_attributes);
		if(all_set){
			var variation = matching_variations.pop();
			$('form input[name=variation_id]').val(variation.variation_id);
			show_variation(variation);
		} else {
			update_variation_values(matching_variations);
		}
	}

	$('.variations select').change(function(){
		//make sure that only selects before this one, and one after this are enabled
		var num = $(this).data('num');
		if($(this).val().length > 0){
			num += 1;
		}
		var selects = $('.variations select');
		selects.filter(':lt(' + num + ')').removeAttr('disabled');
		selects.filter(':eq(' + num + ')').removeAttr('disabled').val('');
		selects.filter(':gt(' + num + ')').attr('disabled', 'disabled').val('');
		check_variations($(this));
	});
	//disable all but first select field
	$('.variations select:gt(0)').attr('disabled', 'disabled');
	//numerate all selects
	$.each($('.variations select'), function(i, item){
		$(item).data('num', i);
	});
	//default attributes
	var initial_change = null; //which default attributes element trigger
	var current_attributes = {};
	var number_of_variations = $('form.variations_form .variations select').length;
	$('form.variations_form .variations select').each(function(i){
		current_attributes[$(this).attr('name')] = $(this).val();
		if($(this).val() != ''){
			//if default attribute is set remember it
			if(i == number_of_variations - 1 && find_matching_variations(current_attributes).length == 0){
				//if all default attributes are set, checks if any variation matches.
				// If not, break the loop and trigger one before last
				return false;
			}
			initial_change = $(this);
		}
		else {
			//break loop if any of default attributes is not set
			return false;
		}
	});
	if(initial_change){
		initial_change.change();
	}

	//JigoShop Favicon Cart Count
	 var favico = new Favico({
		bgColor : '#d00',
		textColor : '#fff',
		fontFamily : 'sans-serif',
		fontStyle : 'bold',
		position : 'down',
		type : 'circle',
		animation : 'slide',
	 });

	 favico.badge(jigoshop_params.favicon_count);
});
