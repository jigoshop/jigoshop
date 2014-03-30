jQuery(document).ready(function($) {

  var states = jigoshop_countries;


	/* Shipping calculator */
	$('.shipping-calculator-form').hide();

	$('.shipping-calculator-button').click( function() {
		$('.shipping-calculator-form').slideToggle('slow', function() {
			// Animation complete.
		});
	});


	// Stop anchors moving the viewport
	$(".shipping-calculator-button").click( function() { return false; });

	$("input[name=shipping_rates]").click( function() {

		var dataString = 'shipping_rates=' + $(this).val();
		var cart_url = $("input[name=cart-url]").val();

		$('.cart_totals_table').block( {
			message: null,
			overlayCSS: {
				background: '#fff url(' + jigoshop_params.assets_url + '/assets/images/ajax-loader.gif) no-repeat center',
				opacity: 0.6
			}
		});

		$.ajax( {
			type: "POST",
			url: cart_url,
			data: dataString,
			success: function(ret) {
				var jqObj = $(ret);
				$('.cart_totals_table').replaceWith(jqObj.find('.cart_totals_table'));
				$('.cart_totals_table').unblock();
			}
		});

	});


    $('select.country_to_state').change( function() {

        var country = $(this).val();
        var state_box = $('#' + $(this).attr('rel'));
        var input_name = $(state_box).attr('name');
        var input_id = $(state_box).attr('id');

        if ( states[country] ) {
            var options = '';
            var state = states[country];
            var state_selected = jigoshop_params.billing_state;
            if ( input_name == 'calc_shipping_state' ) {
                state_selected = $('#calc_shipping_state').val();
            } else if ( input_name == 'billing-state' ) {
            	state_selected = jigoshop_params.billing_state;
            } else {
                state_selected = jigoshop_params.shipping_state;
            }
            for ( var index in state ) {
                if ( state_selected == index ) {
                    options = options + '<option value="' + index + '" selected="selected">' + state[index] + '</option>';
                } else {
                    options = options + '<option value="' + index + '">' + state[index] + '</option>';
                }
            }
            if ( $(state_box).is('input') ) {
                // Change for select
                var required = $(state_box).prev().find('span.required');
                if ( required.val() == undefined ) $(state_box).prev().append(' <span class="required">*</span>');
                $(state_box).replaceWith('<select name="' + input_name + '" id="' + input_id + '"><option value="">' + jigoshop_params.select_state_text + '</option></select>');
                state_box = $('#' + $(this).attr('rel'));
            }
            $(state_box).html(options);
        } else {
            if ( $(state_box).is('select') ) {
        		$(state_box).prev().find('span.required').remove();
                $(state_box).replaceWith('<input class="input-text" type="text" placeholder="' + jigoshop_params.state_text + '" name="' + input_name + '" id="' + input_id + '" />');
                state_box = $('#' + $(this).attr('rel'));
            }
        }

    });

});
