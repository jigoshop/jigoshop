(function($){
	"use strict";
	$(document).ready(function(){
		var updateTimer;
		var jqxhr;
		var $valid_checkout = false;

		// Init trigger
		$('body').on('jigoshop.checkout.init', function(){
			$('select.country_to_state').trigger('init');
		});

		function update_checkout(){
			if(jqxhr) jqxhr.abort();
			var $payment_method = $('input[name=payment_method]:checked');
			var $ship_to_billing = $('#shiptobilling-checkbox');
			var payment_id = $payment_method.attr('id');
			var method = $('#shipping_method').val();
			var coupon = $('#coupon_code').val();
			var payment_method = $payment_method.val();
			var country = $('#billing_country').val();
			var state = $('#billing_state').val();
			var postcode = $('input#billing_postcode').val();
			var s_country = country;
			var s_state = state;
			var s_postcode = postcode;
			if($ship_to_billing.length > 0 && !$ship_to_billing.is(':checked')){
				s_country = $('#shipping_country').val();
				s_state = $('#shipping_state').val();
				s_postcode = $('input#shipping_postcode').val();
			}

			$('#order_methods, #order_review').block({
				message: null,
				overlayCSS: {
					background: '#fff url(' + jigoshop_params.assets_url + '/assets/images/ajax-loader.gif) no-repeat center',
					opacity: 0.6
				}
			});

			jqxhr = $.ajax({
				type: 'POST',
				url: jigoshop_params.ajax_url,
				data: {
					action: 'jigoshop_update_order_review',
					security: jigoshop_params.update_order_review_nonce,
					shipping_method: method,
					country: country,
					state: state,
					postcode: postcode,
					s_country: s_country,
					s_state: s_state,
					s_postcode: s_postcode,
					payment_method: payment_method,
					coupon_code: coupon,
					post_data: $('form.checkout').serialize()
				},
				success: function(response){
					$('#order_methods, #order_review').remove();
					$('#order_review_heading').after(response);
					// ensure there is no duplicate #payment from themes
					$('div#payment:not(:last)').remove();
					// reset currently selected gateway
					$('#' + payment_id).attr('checked', true);
					$payment_method.click();
				}
			});
			$('body').trigger('jigoshop.update_checkout');
		}

		function validate_required(){
			$('.input-required').each(function(){
				var $this = $(this);
				var $parent = $this.closest('.form-row');
				var validated = true;
				// ensure fields aren't empty
				if($this.val() == '' || $this.val() == 'undefined'){
					validated = false;
				}
				// ignore shipping fields if billing fields are only ones allowed
				if($this.attr('id').indexOf('shipping') > -1 && $('#shiptobilling-checkbox').checked){
					validated = true;
				}
				// check postcodes and zips for valid format for country (uses jigoshop_validation class)
				if(validated && $this.attr('id').indexOf('postcode') > -1){
					if($this.attr('id') == 'billing_postcode'){
						var country = $('#billing_country').val();
					} else {
						var country = $('#shipping_country').val();
					}
					var stuff = {
						action: 'jigoshop_validate_postcode',
						security: jigoshop_params.update_order_review_nonce,
						postcode: $this.val(),
						country: country
					};
					$.ajax({
						type: 'GET',
						url: jigoshop_params.ajax_url,
						data: stuff,
						success: function(result){
							validated = result;
						}
					});
				}
				if(validated){
					$parent.removeClass('jigoshop-invalid').addClass('jigoshop-validated');
				} else {
					$parent.removeClass('jigoshop-validated').addClass('jigoshop-invalid');
				}
			});
			if($('.jigoshop-invalid').size() == 0){
				$valid_checkout = true;
			}
		}

		// ensure there is no duplicate #payment from themes
		$('div#payment:not(:last)').remove();
		// handle hiding and showing the login form
		$('form.login').hide();
		$('a.showlogin').click(function(e){
			e.preventDefault();
			$('form.login').slideToggle();
		});
		// handle hiding and showing the shipping fields
		$('#shiptobilling-checkbox').change(function(){
			if($(this).is(':checked')){
				$('div.shipping-address').slideUp();
			} else {
				$('div.shipping-address').slideDown();
			}
		}).change();
		// handle clicks on payment methods
		$(document.body).on('click', '.payment_methods input.input-radio', function(){
			if($('.payment_methods input.input-radio').length > 1){
				$('div.payment_box').filter(':visible').slideUp(250);
				if($(this).is(':checked')){
					$('div.payment_box.' + $(this).attr('ID')).slideDown(250);
				}
			} else {
				$('div.payment_box').show();
			}
		});

		$('input[name=payment_method]:checked').click();
		// handle selections from items requiring an update of totals
		$(document.body).on('change', '#shipping_method, #coupon_code, #billing_country, #billing_state, #billing_postcode, #shipping_country, #shipping_state, #shipping_postcode, #shiptobilling', function(){
			clearTimeout(updateTimer);
			update_checkout();
			validate_required();
		});

		var $create_account = $('#create_account'),
			$account_username = $('#account_username'),
			$account_password = $('#account_password'),
			$account_password_2 = $('#account_password_2');
		// handle account panel 'input-required' for guests allowed or not
		if(jigoshop_params.option_guest_checkout == 'no'){
			$create_account.next().append(' <span class="required">*</span>');
			$account_username.prev().append(' <span class="required">*</span>');
			$account_password.prev().append(' <span class="required">*</span>');
			$account_password_2.prev().append(' <span class="required">*</span>');
			$account_username
				.addClass('input-required')
				.closest('.form-row')
				.removeClass('jigoshop-validated jigoshop-invalid');
			$account_password
				.addClass('input-required')
				.closest('.form-row')
				.removeClass('jigoshop-validated jigoshop-invalid');
			$account_password_2
				.addClass('input-required')
				.closest('.form-row')
				.removeClass('jigoshop-validated jigoshop-invalid');
		} else {
			$('div.create-account').hide();
			$create_account.prev().find('span.required').remove();
		}
		$create_account.change(function(){
			if(jigoshop_params.option_guest_checkout == 'no'){
			} else if(!$(this).is(':checked')){
				$('div.create-account').slideUp();
				$account_username
					.removeClass('input-required')
					.closest('.form-row')
					.removeClass('jigoshop-validated jigoshop-invalid');
				$account_username.prev().find('span.required').remove();
				$account_password
					.removeClass('input-required')
					.closest('.form-row')
					.removeClass('jigoshop-validated jigoshop-invalid');
				$account_password.prev().find('span.required').remove();
				$account_password_2
					.removeClass('input-required')
					.closest('.form-row')
					.removeClass('jigoshop-validated jigoshop-invalid');
				$account_password_2.prev().find('span.required').remove();
			} else {
				$('div.create-account').slideDown();
				$account_username
					.addClass('input-required')
					.closest('.form-row')
					.removeClass('jigoshop-validated jigoshop-invalid');
				$account_username.prev().append(' <span class="required">*</span>');
				$account_password
					.addClass('input-required')
					.closest('.form-row')
					.removeClass('jigoshop-validated jigoshop-invalid');
				$account_password.prev().append(' <span class="required">*</span>');
				$account_password_2
					.addClass('input-required')
					.closest('.form-row')
					.removeClass('jigoshop-validated jigoshop-invalid');
				$account_password_2.prev().append(' <span class="required">*</span>');
			}
		}).change();
		// handle changes to Countries that don't require states and back again
		$('select.country_to_state').on('change init', function(){
			var country = $(this).val();
			var $state = $('#' + $(this).attr('rel'));
			var input_name = $state.attr('name');
			var input_id = $state.attr('id');

			if(jigoshop_countries[country]){
				if($state.is('input')){
					// Change for select
					var required = $state.prev().find('span.required');

					if(required.val() == undefined){
						$state.prev().append(' <span class="required">*</span>');
					}

					$state.replaceWith($(document.createElement('select')).attr('name', input_name).attr('id', input_id));
					$state = $('#'+input_id);
				}

				// Add new states
				$('option', $state).remove();
				$(document.createElement('option')).val('').html(jigoshop_params.select_state_text).appendTo($state);
				for(var state in jigoshop_countries[country]){
					$(document.createElement('option')).val(state).html(jigoshop_countries[country][state]).appendTo($state);
				}

				var selected = jigoshop_params.shipping_state;
				if(input_name == 'calc_shipping_state'){
					selected = $('#calc_shipping_state').val();
				} else if(input_name == 'billing_state'){
					selected = jigoshop_params.billing_state;
				}

				$('option[value='+selected+']', $state).attr('selected', 'selected');
			} else if($state.is('select')){
				var $parent = $state.closest('.form-row');
				$parent.removeClass('jigoshop-validated jigoshop-invalid');
				$state.prev().find('span.required').remove();
				$state.replaceWith(
					$(document.createElement('input')).addClass('input-text').attr('placeholder', jigoshop_params.state_text)
						.attr('name', input_name).attr('id', input_id).attr('type', 'text')
				);
			}

			$(this).trigger('jigoshop.checkout.state_box_changed');
		});

		// handle inline validation of all required checkout fields
		$('form.checkout').on('blur change', '.input-required', function(){
			var $this = $(this);
			var $parent = $this.closest('.form-row');
			var validated = true;
			// ensure required fields aren't empty
			if($this.val() == '' || $this.val() == 'undefined'){
				$parent.removeClass('jigoshop-validated').addClass('jigoshop-invalid');
				validated = false;
			}
			// validate postcode fields for both billing and shipping
			// (requires validation option enabled in General Settings, otherwise only checks for non-empty)
			if(validated && $this.attr('id').indexOf('postcode') > -1){
				if($this.attr('id') == 'billing_postcode'){
					var country = $('#billing_country').val();
				} else {
					var country = $('#shipping_country').val();
				}
				var stuff = {
					action: 'jigoshop_validate_postcode',
					security: jigoshop_params.update_order_review_nonce,
					postcode: $this.val(),
					country: country
				};
				$.ajax({
					type: 'GET',
					url: jigoshop_params.ajax_url,
					data: stuff,
					success: function(result){
						validated = result;
						if(!validated){
							$parent.removeClass('jigoshop-validated').addClass('jigoshop-invalid');
						} else {
							$parent.removeClass('jigoshop-invalid').addClass('jigoshop-validated');
						}
					}
				});
			}

			// ensure valid email addresses
			if(validated && $this.attr('id').indexOf('email') > -1){
				if($this.val()){
					/* http://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
					var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
					if(!pattern.test($this.val())){
						$parent.removeClass('jigoshop-validated').addClass('jigoshop-invalid');
						validated = false;
					}
				}
			}

			if(validated){
				$parent.removeClass('jigoshop-invalid').addClass('jigoshop-validated');
			}
		});

		// AJAX Form Submission from 'Place Order' button
		$('form.checkout').submit(function(){
			validate_required();
			var form = this;
			$(form).block({
				message: null,
				overlayCSS: {
					background: '#fff url(' + jigoshop_params.assets_url + '/assets/images/ajax-loader.gif) no-repeat center',
					opacity: 0.6
				}
			});
			$.ajax({
				type: 'POST',
				url: jigoshop_params.checkout_url,
				data: $(form).serialize(),
				success: function(code){
					$('.jigoshop_error, .jigoshop_message').remove();
					try {
						var success = $.parseJSON(code);
						window.location = decodeURI(success.redirect);
					}
					catch(err) {
						$(form).prepend(code);
						$(form).unblock();
						$('html, body').animate({
							scrollTop: ( $('form.checkout').offset().top - 150 )
						}, 1000);
						return false;
					}
				},
				dataType: "html"
			});
			return false;
		});

		// Update on page load
		if(jigoshop_params.is_checkout == 1){
			$('body').trigger('init_checkout');
			$('body').trigger('jigoshop.checkout.init');
		}
	});
})(jQuery);
