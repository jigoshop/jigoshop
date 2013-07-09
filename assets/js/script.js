jQuery.fn.animateHighlight = function(highlightColor, duration) {
	var highlightBg = highlightColor || "#FFFF9C";
	var animateMs = duration || 1500;
	var originalBg = this.css("backgroundColor");
	this.stop().css("background-color", highlightBg).animate({backgroundColor: originalBg}, animateMs);
};

jQuery(function() {

	// Lightbox
	if (jigoshop_params.load_fancybox) {
		jQuery('a.zoom').prettyPhoto({
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
	jQuery("div.quantity, td.quantity").append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />');
	jQuery(".plus").click(function()
	{
		var currentVal = parseInt(jQuery(this).prev(".qty").val());

		if (!currentVal || currentVal=="" || currentVal == "NaN") currentVal = 0;

		jQuery(this).prev(".qty").val(currentVal + 1);
	});

	jQuery(".minus").click(function()
	{
		var currentVal = parseInt(jQuery(this).next(".qty").val());
		if (currentVal == "NaN") currentVal = 0;
		if (currentVal > 0)
		{
			jQuery(this).next(".qty").val(currentVal - 1);
		}
	});



	/*################# VARIATIONS ###################*/

	//check if two arrays of attributes match
	function variations_match(attrs1, attrs2) {
		var match = true;
		for(name in attrs1) {
            var val1 = attrs1[name].toLowerCase();
			if ( typeof( attrs2[name] ) == 'undefined' ) {
				var val2 = 'undefined';
			} else {
				var val2 = attrs2[name].toLowerCase();
			}

			if(val1.length != 0 && val2.length != 0 && val1 != val2) {
				match = false;
			}
		}

		return match;
	}

	//search for matching variations for given set of attributes
	function find_matching_variations(attributes) {
		var matching = [];

		for(i = 0; i < product_variations.length; i++) {
			var variation = product_variations[i];
			if(variations_match(variation.attributes, attributes)) {
				matching.push(variation);
			}
		}

		return matching;
	}

	//disable option fields that are unavaiable for current set of attributes
	function update_variation_values(variations) {

        // Loop through selects and disable/enable options based on selections
        jQuery('.variations select').each(function( index, el ){

        	current_attr_select = jQuery(el);

        	// Disable all
        	current_attr_select.find('option:gt(0)').attr('disabled', 'disabled');

        	// Get name
	        var current_attr_name = current_attr_select.attr('name');

	        // Loop through variations
	        for(num in variations) {
	            var attributes = variations[num].attributes;

	            for(attr_name in attributes) {
	                var attr_val = attributes[attr_name];

	                if(attr_name == current_attr_name) {
	                    if (attr_val) {
	                    	current_attr_select.find('option[value="'+attr_val+'"]').removeAttr('disabled');
	                    } else {
	                    	current_attr_select.find('option').removeAttr('disabled');
	                    }
	                }
	            }
	        }
			
			// completely re-enable the previous select so 'Choose an option' isn't required to change selections
	        current_attr_select.parent().prev().find('select').find('option:gt(0)').removeAttr('disabled');

        });

	}

	//show single variation details (price, stock, image)
	function show_variation(variation) {
		var img = jQuery('div.images img:eq(0)');
		var link = jQuery('div.images a.zoom:eq(0)');
		var o_src = jQuery(img).attr('original-src');
		var o_link = jQuery(link).attr('original-href');

		var variation_image = variation.image_src;
		var variation_link = variation.image_link;

		jQuery('.single_variation').html( variation.price_html + variation.availability_html );

		if (!o_src) {
			jQuery(img).attr('original-src', jQuery(img).attr('src'));
		}

		if (!o_link) {
			jQuery(link).attr('original-href', jQuery(link).attr('href'));
		}

		if (variation_image && variation_image.length > 1) {
			jQuery(img).attr('src', variation_image);
			jQuery(link).attr('href', variation_link);
		} else {
			jQuery(img).attr('src', o_src);
			jQuery(link).attr('href', o_link);
		}

		jQuery('.product_meta .sku').remove();
		jQuery('.product_meta').append(variation.sku);

		jQuery('.shop_attributes').find('.weight').remove();
		if ( variation.a_weight ) {
			jQuery('.shop_attributes').append(variation.a_weight);
		}

		jQuery('.shop_attributes').find('.length').remove();
		if ( variation.a_length ) {
			jQuery('.shop_attributes').append(variation.a_length);
		}

		jQuery('.shop_attributes').find('.width').remove();
		if ( variation.a_width ) {
			jQuery('.shop_attributes').append(variation.a_width);
		}

		jQuery('.shop_attributes').find('.height').remove();
		if ( variation.a_height ) {
			jQuery('.shop_attributes').append(variation.a_height);
		}

		if ( ! variation.in_stock ) {
			jQuery('.single_variation').slideDown();
		} else {
			jQuery('.variations_button, .single_variation').slideDown();
		}
	}

	//when one of attributes is changed - check everything to show only valid options
	function check_variations() {
		jQuery('form input[name=variation_id]').val('');
		jQuery('.single_variation').text('');
		jQuery('.variations_button, .single_variation').slideUp();

		jQuery('.product_meta .sku').remove();
		jQuery('.shop_attributes').find('.weight').remove();
		jQuery('.shop_attributes').find('.length').remove();
		jQuery('.shop_attributes').find('.width').remove();
		jQuery('.shop_attributes').find('.height').remove();

		var all_set = true;
		var current_attributes = {};

		jQuery('.variations select').each(function(){
			if (jQuery(this).val().length == 0) {
				all_set = false;
			}

			current_attributes[jQuery(this).attr('name')] = jQuery(this).val();
		});
		var matching_variations = find_matching_variations(current_attributes);

		if(all_set) {
			var variation = matching_variations.pop();

			jQuery('form input[name=variation_id]').val(variation.variation_id);
			show_variation(variation);
		} else {
			update_variation_values(matching_variations);
		}
	}

	jQuery('.variations select').change(function(){
		//make sure that only selects before this one, and one after this are enabled
		var num = jQuery(this).data('num');

		if(jQuery(this).val().length > 0) {
			num += 1;
		}

		var selects = jQuery('.variations select');
		selects.filter(':lt('+num+')').removeAttr('disabled');
		selects.filter(':eq('+num+')').removeAttr('disabled').val('');
		selects.filter(':gt('+num+')').attr('disabled', 'disabled').val('');

		check_variations(jQuery(this));
	});

	//disable all but first select field
	jQuery('.variations select:gt(0)').attr('disabled', 'disabled');

	//numerate all selects
	jQuery.each(jQuery('.variations select'), function(i, item){
		jQuery(item).data('num', i);
	});

	//default attributes
	var initial_change = null; //which default attributes element trigger 
	var current_attributes = {}; 
	var number_of_variations = jQuery('form.variations_form .variations select').length;
	jQuery('form.variations_form .variations select').each(function(i) {
		current_attributes[jQuery(this).attr('name')] = jQuery(this).val();
	   
		if (jQuery(this).val() != '') {
			//if default attribute is set remember it
			if ( i == number_of_variations - 1 && find_matching_variations(current_attributes).length == 0) {
				//if all default attributes are set, checks if any variation matches. 
				// If not, break the loop and trigger one before last
				return false;
			}
			initial_change = jQuery(this);
		}
		else {
			//break loop if any of default attributes is not set
			return false;
		}
	});
	if (initial_change) {
		initial_change.change();
	}	
	
});


//message fade in
jQuery(document).ready(function(){
	jQuery('.jigoshop_error, .jigoshop_message').css('opacity', 0);
	setTimeout(function(){jQuery('.jigoshop_error, .jigoshop_message').animate({opacity:1}, 1500);},100);
});