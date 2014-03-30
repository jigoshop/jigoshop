jQuery(document).ready( function($) {

  var states = jigoshop_countries;

	var updateTimer;
	var jqxhr;

	var $valid_checkout = false;


	// Update on page load
	if ( jigoshop_params.is_checkout == 1 ) {
		$('body').trigger('init_checkout');
	}

	// Init trigger
	$('body').bind('init_checkout', function() {
		$('#billing_country, #shipping_country, .country_to_state').change();
	});

	// Update trigger
	$('body').bind('update_checkout', function() {
		clearTimeout(updateTimer);
		update_checkout();
		validate_required();
	});


	function update_checkout() {

		if (jqxhr) jqxhr.abort();

		var payment_id		= $('#payment input[name=payment_method]:checked').attr('id');
		var method			= $('#shipping_method').val();
		var coupon			= $('#coupon_code').val();
		var payment_method	= $('input[name=payment_method]:checked').val();
		var country			= $('#billing-country').val();
		var state			= $('#billing-state').val();
		var postcode		= $('input#billing-postcode').val();

		if ( $('#shiptobilling input').is(':checked') || $('#shiptobilling input').size() == 0 ) {
			var s_country	= $('#billing-country').val();
			var s_state		= $('#billing-state').val();
			var s_postcode	= $('input#billing-postcode').val();

		} else {
			var s_country	= $('#shipping-country').val();
			var s_state		= $('#shipping-state').val();
			var s_postcode	= $('input#shipping-postcode').val();
		}

		$('#order_methods, #order_review').block( {
			message: null,
			overlayCSS: {
				background: '#fff url('+jigoshop_params.assets_url+'/assets/images/ajax-loader.gif) no-repeat center',
				opacity: 0.6
			}
		});

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
			post_data:          $('form.checkout').serialize()
		};

		jqxhr = $.ajax( {
			type:       'POST',
			url:        jigoshop_params.ajax_url,
			data:       data,
			success:    function( response ) {
				$('#order_methods, #order_review').remove();
				$('#order_review_heading').after(response);
				// ensure there is no duplicate #payment from themes
				$('div#payment:not(:last)').remove();
				// reset currently selected gateway
				$('#'+payment_id).attr('checked',true);
				$('#payment input[name=payment_method]:checked').click();
			}
		});

	}


	function validate_required() {

		$('.input-required').each( function() {

			var $this = $(this);
			var $parent = $this.closest('.form-row');
			var validated = true;

			// ensure fields aren't empty
			if ( $this.val() == '' || $this.val() == 'undefined' )
			{
				validated = false;
			}

			// ignore shipping fields if billing fields are only ones allowed
			if ( $this.attr('id').indexOf('shipping') > -1 && $('#shiptobilling-checkbox').checked ) {
				validated = true;
			}

			// check postcodes and zips for valid format for country (uses jigoshop_validation class)
			if ( validated && $this.attr('id').indexOf('postcode') > -1 ) {
				if ( $this.attr('id') == 'billing-postcode' ) {
					var country = $('#billing-country').val();
				} else {
					var country = $('#shipping-country').val();
				}
				var stuff = {
					action:		'jigoshop_validate_postcode',
					security:   jigoshop_params.update_order_review_nonce,
					postcode:	$this.val(),
					country:	country
				};
				$.ajax( {
					type: 		'GET',
					url:        jigoshop_params.ajax_url,
					data: 		stuff,
					success: 	function( result ) {
						validated = result;
					}
				});
			}

			if ( validated )
			{
				$parent.removeClass( 'jigoshop-invalid' ).addClass( 'jigoshop-validated' );
			} else {
				$parent.removeClass( 'jigoshop-validated' ).addClass( 'jigoshop-invalid' );
			}
		});

		if ( $('.jigoshop-invalid').size() == 0 ) {
			$valid_checkout = true;
		}

	}


	// use select2 for all selects
//	$('select').select2({ width: 'off' });

	// ensure there is no duplicate #payment from themes
	$('div#payment:not(:last)').remove();


	// handle hiding and showing the login form
	$('form.login').hide();
	$('a.showlogin').click( function(e) {
		e.preventDefault();
		$('form.login').slideToggle();
	});


	// handle hiding and showing the shipping fields
	$('div.shipping-address').hide();
	$('#shiptobilling input').change( function() {
		$('div.shipping-address').hide();
		if (!$(this).is(':checked')) {
			$('div.shipping-address').slideDown();
		}
	}).change();


	// handle clicks on payment methods
	$(document.body).on('click', '.payment_methods input.input-radio', function() {
		if ( $('.payment_methods input.input-radio').length > 1 ) {
			$('div.payment_box').filter(':visible').slideUp(250);
			if ( $(this).is(':checked') ) {
				$('div.payment_box.' + $(this).attr('ID')).slideDown(250);
			}
		} else {
			$('div.payment_box').show();
		}
	});
	$('#payment input[name=payment_method]:checked').click();


	// handle selections from items requiring an update of totals
	$(document.body).on('change', '#shipping_method, #coupon_code, #billing-country, #billing-state, #billing-postcode, #shipping-country, #shipping-state, #shipping-postcode, #shiptobilling', function() {
		clearTimeout(updateTimer);
		update_checkout();
		validate_required();
	});


	// handle account panel 'input-required' for guests allowed or not
	if ( jigoshop_params.option_guest_checkout == 'no' ) {
		$('#createaccount').next().append(' <span class="required">*</span>');
		$('#account-username').prev().append(' <span class="required">*</span>');
		$('#account-password').prev().append(' <span class="required">*</span>');
		$('#account-password-2').prev().append(' <span class="required">*</span>');
		$('#account-username')
			.addClass('input-required')
			.closest('.form-row')
			.removeClass('jigoshop-validated jigoshop-invalid'
		);
		$('#account-password')
			.addClass('input-required')
			.closest('.form-row')
			.removeClass('jigoshop-validated jigoshop-invalid'
		);
		$('#account-password-2')
			.addClass('input-required')
			.closest('.form-row')
			.removeClass('jigoshop-validated jigoshop-invalid'
		);
	} else {
		$('div.create-account').hide();
		$('#createaccount').prev().find('span.required').remove();
	}
	$('#createaccount').change( function() {
		if ( jigoshop_params.option_guest_checkout == 'no' ) {
		} else if ( ! $(this).is(':checked') ) {
			$('div.create-account').slideUp();
			$('#account-username')
				.removeClass('input-required')
				.closest('.form-row')
				.removeClass('jigoshop-validated jigoshop-invalid'
			);
			$('#account-username').prev().find('span.required').remove();
			$('#account-password')
				.removeClass('input-required')
				.closest('.form-row')
				.removeClass('jigoshop-validated jigoshop-invalid'
			);
			$('#account-password').prev().find('span.required').remove();
			$('#account-password-2')
				.removeClass('input-required')
				.closest('.form-row')
				.removeClass('jigoshop-validated jigoshop-invalid'
			);
			$('#account-password-2').prev().find('span.required').remove();
		} else {
			$('div.create-account').slideDown();
			$('#account-username')
				.addClass('input-required')
				.closest('.form-row')
				.removeClass('jigoshop-validated jigoshop-invalid'
			);
			$('#account-username').prev().append(' <span class="required">*</span>');
			$('#account-password')
				.addClass('input-required')
				.closest('.form-row')
				.removeClass('jigoshop-validated jigoshop-invalid'
			);
			$('#account-password').prev().append(' <span class="required">*</span>');
			$('#account-password-2')
				.addClass('input-required')
				.closest('.form-row')
				.removeClass('jigoshop-validated jigoshop-invalid'
			);
			$('#account-password-2').prev().append(' <span class="required">*</span>');
		}
	}).change();


	// handle changes to Countries that dont' require states and back again
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
            	var $parent = $(state_box).closest('.form-row');
            	$parent.removeClass( 'jigoshop-validated jigoshop-invalid' );
        		$(state_box).prev().find('span.required').remove();
                $(state_box).replaceWith('<input class="input-text" type="text" placeholder="' + jigoshop_params.state_text + '" name="' + input_name + '" id="' + input_id + '" />');
                state_box = $('#' + $(this).attr('rel'));
            }
        }

    });


	// handle inline validation of all required checkout fields
	$('form.checkout').on( 'blur change', '.input-required', function() {

		var $this = $(this);
		var $parent = $this.closest('.form-row');
		var validated = true;

		// ensure required fields aren't empty
		if ( $this.val() == '' || $this.val() == 'undefined' )
		{
			$parent.removeClass( 'jigoshop-validated' ).addClass( 'jigoshop-invalid' );
			validated = false;
		}

		// validate postcode fields for both billing and shipping
		// (requires validation option enabled in General Settings, otherwise only checks for non-empty)
		if ( validated && $this.attr('id').indexOf('postcode') > -1 ) {
			if ( $this.attr('id') == 'billing-postcode' ) {
				var country = $('#billing-country').val();
			} else {
				var country = $('#shipping-country').val();
			}
			var stuff = {
				action:		'jigoshop_validate_postcode',
				security:   jigoshop_params.update_order_review_nonce,
				postcode:	$this.val(),
				country:	country
			};
			$.ajax( {
				type: 		'GET',
				url:        jigoshop_params.ajax_url,
				data: 		stuff,
				success: 	function( result ) {
					validated = result;
					if ( ! validated ) {
						$parent.removeClass( 'jigoshop-validated' ).addClass( 'jigoshop-invalid' );
					} else {
						$parent.removeClass( 'jigoshop-invalid' ).addClass( 'jigoshop-validated' );
					}
				}
			});
		}

		// ensure valid email addresses
		if ( validated && $this.attr('id').indexOf('email') > -1 ) {
			if ( $this.val() ) {
				/* http://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
				var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

				if ( ! pattern.test( $this.val()  ) ) {
					$parent.removeClass( 'jigoshop-validated' ).addClass( 'jigoshop-invalid' );
					validated = false;
				}
			}
		}

		if ( validated )
		{
			$parent.removeClass( 'jigoshop-invalid' ).addClass( 'jigoshop-validated' );
		}

	});


	// AJAX Form Submission from 'Place Order' button
	$('form.checkout').submit( function() {
		validate_required();
		var form = this;
		$(form).block( {
			message: null,
			overlayCSS: {
				background: '#fff url(' + jigoshop_params.assets_url + '/assets/images/ajax-loader.gif) no-repeat center',
				opacity: 0.6
			}
		});
		$.ajax( {
			type: 		'POST',
			url: 		jigoshop_params.checkout_url,
			data: 		$(form).serialize(),
			success: 	function( code ) {
				$('.jigoshop_error, .jigoshop_message').remove();
				try {
					success = $.parseJSON( code );
					window.location = decodeURI(success.redirect);
				}
				catch (err) {
					$(form).prepend( code );
					$(form).unblock();
					$('html, body').animate( {
						scrollTop: ( $('form.checkout').offset().top - 150 )
					}, 1000);
					return false;
				}
			},
			dataType: 	"html"
		});
		return false;
	});

});
