jQuery.fn.animateHighlight = function(highlightColor, duration) {
	var highlightBg = highlightColor || "#FFFF9C";
	var animateMs = duration || 1500;
	var originalBg = this.css("backgroundColor");
	this.stop().css("background-color", highlightBg).animate({backgroundColor: originalBg}, animateMs);
};

jQuery(function() {

	// Lightbox
	if (jigoshop_params.load_fancybox) {
		jQuery('a.zoom').fancybox({
			'transitionIn'	:	'elastic',
			'transitionOut'	:	'elastic',
			'speedIn'		:	600,
			'speedOut'		:	200,
			'overlayShow'	:	true
		});
	}

	// Star ratings
	jQuery('#rating').hide().before('<p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p>');

	jQuery('p.stars a').click(function(){
		jQuery('#rating').val(jQuery(this).text());
		jQuery('p.stars a').removeClass('active');
		jQuery(this).addClass('active');
		return false;
	});

	// Price slider
	var min_price = parseInt(jQuery('.price_slider_amount #min_price').val());
	var max_price = parseInt(jQuery('.price_slider_amount #max_price').val());

	if (jigoshop_params.min_price) {
		current_min_price = jigoshop_params.min_price;
	} else {
		current_min_price = min_price;
	}

	if (jigoshop_params.max_price) {
		current_max_price = jigoshop_params.max_price;
	} else {
		current_max_price = max_price;
	}

	jQuery('.price_slider').slider({
		range: true,
		min: min_price,
		max: max_price,
		values: [ current_min_price, current_max_price ],
		create : function( event, ui ) {
			jQuery( ".price_slider_amount span" ).html( jigoshop_params.currency_symbol + current_min_price + " - " + jigoshop_params.currency_symbol + current_max_price );
			jQuery( ".price_slider_amount #min_price" ).val(current_min_price);
			jQuery( ".price_slider_amount #max_price" ).val(current_max_price);
		},
		slide: function( event, ui ) {
			jQuery( ".price_slider_amount span" ).html( jigoshop_params.currency_symbol + ui.values[ 0 ] + " - " + jigoshop_params.currency_symbol + ui.values[ 1 ] );
			jQuery( "input#min_price" ).val(ui.values[ 0 ]);
			jQuery( "input#max_price" ).val(ui.values[ 1 ]);
		}
	});

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

	/* states */
    var states_json = jigoshop_params.countries.replace(/&quot;/g, '"');
    var states = jQuery.parseJSON( states_json );

    jQuery('select.country_to_state').change(function(){

        var country = jQuery(this).val();
        var state_box = jQuery('#' + jQuery(this).attr('rel'));

        var input_name = jQuery(state_box).attr('name');
        var input_id = jQuery(state_box).attr('id');

        if (states[country]) {
            var options = '';
            var state = states[country];
            var state_selected = jigoshop_params.billing_state;
            if (input_name == 'calc_shipping_state') {
                state_selected = jQuery('#calc_shipping_state').val();
            }
            else if ( input_name == 'billing-state' ) {
            	state_selected = jigoshop_params.billing_state;
            }
            else {
                state_selected = jigoshop_params.shipping_state;
            }
            for(var index in state) {

                if (state_selected == index) {
                    options = options + '<option value="' + index + '" selected="selected">' + state[index] + '</option>';
                } else {
                    options = options + '<option value="' + index + '">' + state[index] + '</option>';
                }
            }
            if (jQuery(state_box).is('input')) {
                // Change for select
        		jQuery(state_box).prev().append(' <span class="required">*</span>');
                jQuery(state_box).replaceWith('<select name="' + input_name + '" id="' + input_id + '"><option value="">' + jigoshop_params.select_state_text + '</option></select>');
                state_box = jQuery('#' + jQuery(this).attr('rel'));
            }
            jQuery(state_box).html(options);
        } else {
            if (jQuery(state_box).is('select')) {
        		jQuery(state_box).prev().find('span.required').remove();
                jQuery(state_box).replaceWith('<input class="input-text" type="text" placeholder="' + jigoshop_params.state_text + '" name="' + input_name + '" id="' + input_id + '" />');
                state_box = jQuery('#' + jQuery(this).attr('rel'));
            }
        }

    }).change();

	/* Tabs */
	jQuery('#tabs .panel:not(#tabs .panel)').hide();
	jQuery('div#tabs ul.tabs li > a').click(function(){
		var href = jQuery(this).attr('href');
		jQuery('#tabs li').removeClass('active');
		jQuery('div.panel').hide();
		jQuery('div' + href).show();
		jQuery(this).parent().addClass('active');
		jQuery.cookie('current_tab', href);
		return false;
	});
	if (jQuery('#tabs li.active').size()==0) {
		jQuery('#tabs li:first a').click();
	} else {
		jQuery('#tabs li.active a').click();
	}

	/* Shipping calculator */

	jQuery('.shipping-calculator-form').hide();

	jQuery('.shipping-calculator-button').click(function() {
	  jQuery('.shipping-calculator-form').slideToggle('slow', function() {
		// Animation complete.
	  });
	});

	// Stop anchors moving the viewport

	jQuery(".shipping-calculator-button").click(function() {return false;});

	jQuery("input[name=shipping_rates]").click(function(){
		var dataString = 'shipping_rates=' + jQuery(this).val();
		var cart_url = jQuery("input[name=cart-url]").val();
		jQuery('.cart_totals_table').block({message: null, overlayCSS: {background: '#fff url(' + jigoshop_params.assets_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6}});
		jQuery.ajax({
			type: "POST",
			url: cart_url,
			data: dataString,
			success: function(ret) {
				var jqObj = jQuery(ret);
				jQuery('.cart_totals_table').replaceWith(jqObj.find('.cart_totals_table'));
				jQuery('.cart_totals_table').unblock();
			}
		});
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

if ( jigoshop_params.is_checkout ) {

	var updateTimer;
	var jqxhr;

	function update_checkout() {

		if (jqxhr) jqxhr.abort();

		var method        = jQuery('#shipping_method').val();
		var coupon        = jQuery('#coupon_code').val();
		var payment_method= jQuery('input[name=payment_method]:checked').val();
		var country       = jQuery('#billing-country').val();
		var state         = jQuery('#billing-state').val();
		var postcode      = jQuery('input#billing-postcode').val();

		if (jQuery('#shiptobilling input').is(':checked') || jQuery('#shiptobilling input').size()==0) {
			var s_country = jQuery('#billing-country').val();
			var s_state   = jQuery('#billing-state').val();
			var s_postcode= jQuery('input#billing-postcode').val();

		} else {
			var s_country = jQuery('#shipping-country').val();
			var s_state   = jQuery('#shipping-state').val();
			var s_postcode= jQuery('input#shipping-postcode').val();
		}

		jQuery('#order_methods, #order_review').block({message: null, overlayCSS: {background: '#fff url(' + jigoshop_params.assets_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6}});

		var data = {
			action:             'jigoshop_update_order_review',
			security:           jigoshop_params.update_order_review_nonce,
			shipping_method:    method,
			country:            country,
			state:              state,
			postcode:           postcode,
			s_country:          s_country,
			s_state:            s_state,
			s_postcode:         s_postcode,
			payment_method:     payment_method,
			coupon_code:        coupon,
			post_data:          jQuery('form.checkout').serialize()
		};

		jqxhr = jQuery.ajax({
			type:       'POST',
			url:        jigoshop_params.ajax_url,
			data:       data,
			success:    function( response ) {
				/* Prevent stacking of errors. */
				jQuery('.jigoshop_error, .jigoshop_message').remove();
				jQuery('#order_methods, #order_review').remove();
				jQuery('#order_review_heading').after(response);
				jQuery('#order_review input[name=payment_method]:checked').click();
			}
		});

	}

	jQuery(function(){

		jQuery('p.password').hide();

		jQuery('input.show_password').change(function(){
			jQuery('p.password').slideToggle();
		});

		jQuery('div.shipping-address').hide();

		jQuery('#shiptobilling input').change(function(){
			jQuery('div.shipping-address').hide();
			if (!jQuery(this).is(':checked')) {
				jQuery('div.shipping-address').slideDown();
			}
		}).change();

		if (jigoshop_params.option_guest_checkout=='yes') {

			jQuery('div.create-account').hide();

			jQuery('input#createaccount').change(function(){
				jQuery('div.create-account').hide();
				if (jQuery(this).is(':checked')) {
					jQuery('div.create-account').slideDown();
				}
			}).change();

		}

		jQuery(document.body).on('click', '.payment_methods input.input-radio', function(){
			jQuery('div.payment_box').hide();
			if (jQuery(this).is(':checked')) {
				jQuery('div.payment_box.' + jQuery(this).attr('ID')).slideDown();
			}
		});

		jQuery('#order_review input[name=payment_method]:checked').click();

		jQuery('form.login').hide();

		jQuery('a.showlogin').click(function(e){
			e.preventDefault();
			jQuery('form.login').slideToggle();
		});

		/* Update totals */
		
		jQuery(document.body).on('change', '#shipping_method', function(){
			clearTimeout(updateTimer);
			update_checkout();
		});
		jQuery(document.body).on('change', '#coupon_code', function(e){
			clearTimeout(updateTimer);
			update_checkout();
		});
		jQuery(document.body).on('change', 'input#billing-country, input#billing-state, #billing-postcode, input#shipping-country, input#shipping-state, #shipping-postcode', function(e){
			clearTimeout(updateTimer);
			update_checkout();
		});
		jQuery(document.body).on('change', 'select#billing-country, select#billing-state, select#shipping-country, select#shipping-state, #shiptobilling input', function(e){
			clearTimeout(updateTimer);
			update_checkout();
		});

		/* AJAX Form Submission */
		jQuery('form.checkout').submit(function(){
			var form = this;
			jQuery(form).block({message: null, overlayCSS: {background: '#fff url(' + jigoshop_params.assets_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6}});
			jQuery.ajax({
				type: 		'POST',
				url: 		jigoshop_params.checkout_url,
				data: 		jQuery(form).serialize(),
				success: 	function( code ) {
					jQuery('.jigoshop_error, .jigoshop_message').remove();
					try {
						success = jQuery.parseJSON( code );
						window.location = decodeURI(success.redirect);
					}
					catch(err) {
						jQuery(form).prepend( code );
						jQuery(form).unblock();
						jQuery.scrollTo(jQuery(form).parent(), {easing:'swing'});
					}
				},
				dataType: 	"html"
			});
			return false;
		});

	});
}

//message fade in
jQuery(document).ready(function(){
	jQuery('.jigoshop_error, .jigoshop_message').css('opacity', 0);
	setTimeout(function(){jQuery('.jigoshop_error, .jigoshop_message').animate({opacity:1}, 1500);},100);
});