(function($) {

	// On document Load
	$(function() {

		$('#product-type').select2({ width: '200px' });
		$('.backorders_field').hide();

		// Set up tabs
		jigoshop_start_tabs();

		// Setup options
		jigoshop_product_type_options();

		// Set up jigoshop sale datepicker
		jigoshop_sale_picker();

		// Jigoshop stock options
		jigoshop_stock_options();

		// Sortables
		jigoshop_sortables();

		// Orders
		jigoshop_orders();

		// Attributes
		jigoshop_attributes();

		// File Upload
		jigoshop_file_upload();

		// Ensure a tax class is set on products
		jigoshop_default_product_taxclass();
	});

	function jigoshop_start_tabs() {
		
		var $tabs = $('.tabs');

		// First show tabs & hide each panel
		$tabs.show();
		$('div.panels').each(function(){
			$('div.panel:not(div.panel:first)', this).hide();
		});

		$tabs.find('a').on('click', function(e){
			e.preventDefault();

			var $panels = $(this).closest('.panels');
			$('.tabs li', $panels).removeClass('active');
			$(this).parent().addClass('active');
			$('div.panel', $panels).hide();
			$( $(this).attr('href') ).show();
		});
		
	}

	function jigoshop_stock_options() {
		$('#manage_stock').on('change',function() {
			if ($(this).is(':checked')) {
				$('.stock_status_field').hide();
				$('.stock_field').show();
				$('.backorders_field').slideDown(100);
				$('#stock').focus();
			}
			else {
				$('.backorders_field').slideUp(100);
				$('.stock_field').hide();
				$('.stock_status_field').show();
			}
		}).change();
	}

	function jigoshop_product_type_options() {
		$('select#product-type').change(function(){

			$('body').removeClass('simple_product downloadable_product grouped_product virtual_product variable_product external_product')
				.addClass( $(this).val() + '_product' );
		}).change();
	}

	function jigoshop_default_product_taxclass() {
		var $taxclasses = $('.tax_classes_field input');
		// the first tax class will always be 'standard', turn it on if no tax class selected
		if (! $taxclasses.is(':checked')) $taxclasses.eq(0).attr('checked', true);
	}

	function jigoshop_sale_picker() {
		// Sale price schedule
		var sale_schedule_set = false;
		$('.sale_price_dates_fields input').each(function(){
			if ( $(this).val() ) {
				sale_schedule_set = true;
			}
		});
		if (sale_schedule_set) {
			$('.sale_schedule').hide();
			$('.sale_price_dates_fields').show();
		} else {
			$('.sale_schedule').show();
			$('.sale_price_dates_fields').hide();
		}

		$('.sale_schedule').click(function(e){
			e.preventDefault();
			$(this).hide();
			$('.sale_price_dates_fields').slideDown(100, function(){
				$('#sale_price_dates_from').focus();
			});
		});

		$('.cancel_sale_schedule').click(function(e){
			e.preventDefault();
			$('.sale_schedule').show();
			$('.sale_price_dates_fields').slideUp(100, function() {
				var option = this.id == "sale_price_dates_from" ? "minDate" : "maxDate";
				$(this).closest('p').find('input').datepicker( "option", option, null ).val(null);
			});
		});

		var dates = $( "#sale_price_dates_from, #sale_price_dates_to" ).datepicker({
			dateFormat: 'yy-mm-dd',
			gotoCurrent: true,
			hideIfNoPrevNext: true,
			numberOfMonths: 1,
			minDate: 'today',
			onSelect: function( selectedDate ) {
				var option = this.id == "sale_price_dates_from" ? "minDate" : "maxDate",
					instance = $( this ).data( "datepicker" ),
					date = $.datepicker.parseDate(
						instance.settings.dateFormat ||
						$.datepicker._defaults.dateFormat,
						selectedDate, instance.settings );
				dates.not( this ).datepicker( "option", option, date );
			}
		});
	}

	function jigoshop_sortables() {
		$('.jigoshop_attributes_wrapper').sortable({
			items:'.attribute',
			// containment: 'parent', // Applies strict containment meaning only vertical movement is capable
			handle: '.handle',
			distance: 15,
			placeholder: "ui-state-highlight",
			forcePlaceholderSize: true,
			stop:function(event,ui){
				ui.item.removeAttr('style');
				row_indexes();
			}
		});
	}

	/**
	 * Parses attribute positions & applies to hidden fields
	 */
	function row_indexes() {
		$('.jigoshop_attributes_wrapper .attribute').each(function(index, el) {
				$('.attribute_position', el).val(
					parseInt( $(el).index('.jigoshop_attributes_wrapper .attribute') )
				);
		});
	}

	function jigoshop_orders() {

		$(document.body).on('click', '#order_items_list button.remove_row', function(e) {
			e.preventDefault();
			var answer = confirm(jigoshop_params.remove_item_notice);
			if (answer){
				$(this).parent().parent().remove();
			}
		});

		$(document.body).on('click', 'button.calc_totals', function(e) {
			e.preventDefault();
			var answer = confirm( jigoshop_params.cart_total );
			if ( answer ){
				
				// stuff the normal round function, we'll return it at end of function
				// replace with alternative, still doesn't work across diff browsers though
				// TODO: we shouldn't be doing any tax calcs in javascript
				Math._round = Math.round;
				Math.round = function( number, precision )
				{
					if ( typeof( precision ) == "undefined" ) precision = 0;
					else precision = Math.abs( parseInt( precision )) || 0;
					var coefficient = Math.pow( 10, precision );
					return Math._round( number * coefficient ) / coefficient;
				}
				
				var taxBeforeDiscount = "<?php Jigoshop_Base::get_options()->get_option('jigoshop_tax_after_coupon'); ?>";
				var itemTotal = 0;
				var subtotal = 0;
				var totalTax = 0;
				var total = 0;

				var item_count = $('#order_items_list tr.item').size();
				var discount = parseFloat($('input#order_discount').val());
				var shipping = parseFloat($('input#order_shipping').val());
				var shipping_tax = parseFloat($('input#order_shipping_tax').val());

				if ( isNaN( discount) ) discount = 0;
				if ( isNaN( shipping ) ) shipping = 0;
				if ( isNaN( shipping_tax ) ) shipping_tax = 0;

				// Items
				if ( item_count > 0 ) {
					for ( i=0 ; i < item_count ; i++ ) {

						itemCost 	= parseFloat($('input[name^=item_cost]:eq(' + i + ')').val());
						itemQty 	= parseInt($('input[name^=item_quantity]:eq(' + i + ')').val());
						itemTax		= parseFloat($('input[name^=item_tax_rate]:eq(' + i + ')').val());

						if ( isNaN( itemCost ) ) itemCost = 0;
						if ( isNaN( itemTax ) )  itemTax  = 0;

						totalItemTax = 0;

						totalItemCost = parseFloat( itemCost * itemQty );

						if ( itemTax && itemTax > 0 ) {
							
							// get tax rate into a decimal value
							taxRate = itemTax / Math.pow(10,2);
							
							// this will give 4 decimal places or precision
							itemTax = itemCost * taxRate;
							
							// round to 3 decimal places
							itemTax1 = Math.round( itemTax, 3 );
							
							// round again to 2 decimal places
							finalItemTax = Math.round( itemTax1, 2 );
							
							// get the total tax for the product including quantities
							totalItemTax = finalItemTax * itemQty;

						}
						
						// total the tax across all products
						totalTax = totalTax + totalItemTax;
						
						// total all products without tax
						subtotal = subtotal + totalItemCost;

					}
				}
				
				totalTax = totalTax + parseFloat(shipping_tax);
				
				// total it all up
				if ( taxBeforeDiscount == 'no' )
					total = parseFloat(subtotal) - parseFloat(discount) + parseFloat(totalTax) + parseFloat(shipping);
				else
					total = parseFloat(subtotal) + parseFloat(totalTax) - parseFloat(discount) + parseFloat(shipping);

				if ( total < 0 ) total = 0;

				$('input#order_subtotal').val( subtotal.toFixed(2) );
				$('input#order_tax').val( totalTax.toFixed(2) );
				$('input#order_shipping_tax').val( shipping_tax.toFixed(2) );
				$('input#order_total').val( total.toFixed(2) );
				
				Math.round = Math._round;   // return normal round function we altered at the start of function
			}

		});


		$(document.body).on('click', 'button.billing-same-as-shipping', function(e){
			e.preventDefault();
			var answer = confirm(jigoshop_params.copy_billing);
			if (answer){
				$('input#shipping_first_name').val( $('input#billing_first_name').val() );
				$('input#shipping_last_name').val( $('input#billing_last_name').val() );
				$('input#shipping_company').val( $('input#billing_company').val() );
				$('input#shipping_address_1').val( $('input#billing_address_1').val() );
				$('input#shipping_address_2').val( $('input#billing_address_2').val() );
				$('input#shipping_city').val( $('input#billing_city').val() );
				$('input#shipping_postcode').val( $('input#billing_postcode').val() );
				$('input#shipping_country').val( $('input#billing_country').val() );
				$('input#shipping_state').val( $('input#billing_state').val() );
			}
		});

		$('button.add_shop_order_item').click(function(e) {
			e.preventDefault();
			var item_id = $("#order_product_select").val();
			if (item_id) {
				$('table.jigoshop_order_items').block({ message: null, overlayCSS: { background: '#fff url(' + jigoshop_params.assets_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

				var data = {
					action: 		'jigoshop_add_order_item',
					item_to_add: 	item_id,
					security: 		jigoshop_params.add_order_item_nonce
				};

				$.post( jigoshop_params.ajax_url, data, function(response) {

					$('table.jigoshop_order_items tbody#order_items_list').append( response );
					$('table.jigoshop_order_items').unblock();
					$("#order_product_select").select2('val', '');
					$("#order_product_select").css('border-color', '');

				});

			} else {
				$("#order_product_select").css('border-color', 'red');
			}
		});

	}

	function jigoshop_attributes() {
		// Initial Ordering
		var jigoshop_attributes_table_items = $('.jigoshop_attributes_wrapper').children('.attribute').get();
		jigoshop_attributes_table_items.sort(function(a, b) {
		   var compA = Number($(a).attr('rel'));
		   var compB = Number($(b).attr('rel'));
		   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
		})
		$(jigoshop_attributes_table_items).each( function(idx, itm) { $('.jigoshop_attributes_wrapper').append(itm); } );

		// Polyfill for custom attributes not closing
		$(document.body).on('click', '.custom .handle, .custom .handlediv', function(){
			$(this).parent().toggleClass('closed');
		});

		// Custom attributes autogenerate name
		$(document.body).on('keyup', '.attribute-name', function(e) {

			if( ! $(this).val() )
				val = 'Custom Attribute';
			else
				val = $(this).val();

			$(this).parents('.attribute').find('.handle').text(val);
		});

		// Remove attribute
		$(document.body).on('click', 'button.hide_row', function(e) {
			e.preventDefault();
			var answer = confirm(jigoshop_params.confirm_remove_attr)
			if (answer){
				$parent = $(this).parent();
				$parent.fadeOut('slow', function() {
					$parent.find('select, input[type=text], input[type=checkbox], textarea').not('.attribute-name').val(null).trigger('attribute_clear_value');
				});


				// Re-enable the option
				$("select.attribute_taxonomy option[value='"+$(this).parent().data('attribute-name')+"']").attr('disabled', false);
			}
		});

		// Add rows
		$('button.add_attribute').click(function(){

			var attribute = $('select.attribute_taxonomy').val();
			var type = $('select.attribute_taxonomy').find(':selected').data('type');

			// Remove the demo if it exists
			$('.demo.attribute').remove();

			// Disable select option
			if( $('select.attribute_taxonomy option:selected').val() ) {
				$('select.attribute_taxonomy')
					.find('option:selected').attr('disabled', true)
					.parent().val(null);
			}

			if (!attribute) {
				var size = $('.attribute').size();
				
				// Add custom attribute row
				var $custom_panel = $('\
					<div class="postbox attribute custom">\
						<button type="button" class="hide_row button">Remove</button>\
						<div class="handlediv" title="Click to toggle"><br></div>\
						<h3 class="handle">'+jigoshop_params.custom_attr_heading+'</h3>\
\
						<input type="hidden" name="attribute_is_taxonomy[' + size + ']" value="0">\
						<input type="hidden" name="attribute_enabled[' + size + ']" value="1">\
						<input type="hidden" name="attribute_position[' + size + ']" class="attribute_position" value="[' + size + ']">\
\
						<div class="inside">\
							<table>\
								<tr>\
									<td class="options">\
										<input class="attribute-name" type="text" name="attribute_names[' + size + ']" autofocus="autofocus" tabindex="'+size+'" />\
										<div>\
											<label>\
												<input type="checkbox" checked="checked" name="attribute_visibility[' + size + ']" value="1">\
												'+jigoshop_params.display_attr_label+'\
											</label>\
\
											<label class="attribute_is_variable">\
												<input type="checkbox" checked="checked" name="attribute_variation[' + size + ']" value="1">\
												'+jigoshop_params.variation_attr_label+'\
											</label>\
										</div>\
									</td>\
									<td class="value">\
											\
										<textarea name="attribute_values[' + size + ']" tabindex="'+size+'"></textarea>\
										\
									</td>\
								</tr>\
							</table>\
						</div>\
					</div>\
				');

				$custom_panel.hide().prependTo('.jigoshop_attributes_wrapper').slideDown( 150, function() {
					$(this).find('.attribute-name').focus()
				});

			} else {

				// var size = $('table.jigoshop_attributes tbody tr').size();
				// Reveal taxonomy row
				var thisrow = $('.attribute.' + attribute);

				// Enable all mutiselect items by default
				if (type == 'multiselect'){
					thisrow.find('td.value .multiselect-controls a.check-all').click();
				}

				$('.jigoshop_attributes_wrapper').prepend( thisrow );
				$(thisrow).slideDown('fast');
				row_indexes();

			}
		});

		var multiselectClicked = function(){
			if ($(this).is(':checked')){
				$(this).parent().addClass('selected');
			} else {
				$(this).parent().removeClass('selected');
			}
		};

		$('.multiselect input').click(multiselectClicked);

		$('.multiselect-controls a').click(function(e) {
			e.preventDefault();
			var items = $(this).parent().prev().find('input[type=checkbox]');
			if ($(this).hasClass('toggle')){
				items.each(function(){
					$(this).attr('checked', !$(this).is(':checked'));
					multiselectClicked.call(this);
				});
			} else if ($(this).hasClass('check-all')){
				items.attr('checked', true);
				items.parent().addClass('selected');
			} else if ($(this).hasClass('uncheck-all')){
				items.attr('checked', false);
				items.parent().removeClass('selected');
			} else if ($(this).hasClass('show-all')) {
				$(this).parent().prev().addClass('show_all_enabled');
				$(this).remove();
			}
		});
	}

	function jigoshop_file_upload() {
		$('.upload_file_button').click( function(e) {

			// Disable default action
			e.preventDefault();

			// Set up variables
			var $this   = $(this);
			    $file   = $this.prev();
			    post_id = $this.data('postid');

			window.send_to_editor = function(html) {

				// Attach the file URI to the relevant
				$file.val( $(html).attr('href') );

				// Hide thickbox
				tb_remove();
			}

			// Show thickbox
			tb_show('', 'media-upload.php?post_id=' + post_id + '&type=downloadable_product&from=jigoshop_product&TB_iframe=true');
		});
	}

})(window.jQuery);