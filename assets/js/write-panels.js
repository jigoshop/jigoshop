jQuery( function($){

	// TABS
	jQuery('ul.tabs').show();
	jQuery('div.panel-wrap').each(function(){
		jQuery('div.panel:not(div.panel:first)', this).hide();
	});
	jQuery('ul.tabs a').click(function(){
		var panel_wrap =  jQuery(this).closest('div.panel-wrap');
		jQuery('ul.tabs li', panel_wrap).removeClass('active');
		jQuery(this).parent().addClass('active');
		jQuery('div.panel', panel_wrap).hide();
		jQuery( jQuery(this).attr('href') ).show();
		return false;
	});
	
	// ORDERS
	
	jQuery('#order_items_list button.remove_row').live('click', function(){
		var answer = confirm(jigoshop_wp.remove_item_notice);
		if (answer){
			jQuery(this).parent().parent().remove();
		}
		return false;
	});
	
	jQuery('button.calc_totals').live('click', function(){
		var answer = confirm(jigoshop_wp.cart_total);
		if (answer){
			
			var item_count = jQuery('#order_items_list tr').size();
			var subtotal = 0;
			var discount = jQuery('input#order_discount').val();
			var shipping = jQuery('input#order_shipping').val();
			var shipping_tax = parseFloat(jQuery('input#order_shipping_tax').val());
			var tax = 0;
			var itemTotal = 0;
			var total = 0;
			
			if (!discount) discount = 0;
			if (!shipping) shipping = 0;
			if (!shipping_tax) shipping_tax = 0;
			
			// Items
			if (item_count>0) {
				for (i=0; i<item_count; i++) {
					
					itemCost 	= jQuery('input[name^=item_cost]:eq(' + i + ')').val();
					itemQty 	= parseInt(jQuery('input[name^=item_quantity]:eq(' + i + ')').val());
					itemTax		= jQuery('input[name^=item_tax_rate]:eq(' + i + ')').val();
					
					if (!itemCost) itemCost = 0;
					if (!itemTax) itemTax = 0;
					
					totalItemCost = itemCost * itemQty;
					
					totalItemTax = 0;
					
					if (itemTax && itemTax>0) {
						
						taxRate = Math.round( ((itemTax / 100) + 1) *100)/100; // tax rate to 2 decimal places
						
						totalItemCost = totalItemCost * 100;
						
						totalItemTax = Math.round(totalItemCost*taxRate);
						
						//totalCostExTax = Math.round( (totalItemCost / taxRate) *100 )/100; // 2 decimal places
						
						//totalCostExTax = totalCostExTax / 100;
						
						totalItemTax = totalItemTax - totalItemCost;
						
						totalItemCost = totalItemCost / 100;
						
						totalItemTax = totalItemTax / 100;
						
						//totalItemTax = totalItemCost - totalCostExTax;
						
					}
					
					itemTotal = itemTotal + totalItemCost;
					
					tax = tax + totalItemTax;
				}
			}
			
			subtotal = itemTotal;
			
			/*if (jigoshop_wp.prices_include_tax == 'yes')
				total = parseFloat(subtotal) - parseFloat(discount) + parseFloat(shipping) + parseFloat(shipping_tax);
			else*/
				total = parseFloat(subtotal) + parseFloat(tax) - parseFloat(discount) + parseFloat(shipping) + parseFloat(shipping_tax);
			
			if (total < 0 ) total = 0;

			jQuery('input#order_subtotal').val( subtotal.toFixed(2) );
			jQuery('input#order_tax').val( tax.toFixed(2) );
			jQuery('input#order_shipping_tax').val( shipping_tax.toFixed(2) );
			jQuery('input#order_total').val( total.toFixed(2) );

		}
		return false;
	});
	
	jQuery('button.add_shop_order_item').click(function(){
		jQuery('table.jigoshop_order_items tbody').append('<tr><td><input type="text" name="item_id[]" placeholder="'+jigoshop_wp.ID+'" value="" /></td><td><input type="text" name="item_name[]" placeholder="'+jigoshop_wp.item_name+'" value="" /></td><td><input type="text" name="item_quantity[]" placeholder="'+jigoshop_wp.quantity+'" value="" /></td><td><input type="text" name="item_cost[]" placeholder="'+jigoshop_wp.cost_unit+'" value="" /></td><td><input type="text" name="item_tax_rate[]" placeholder="'+jigoshop_wp.tax_rate+'" value="" /></td><td class="center"><button type="button" class="remove_row button">&times;</button></td></tr>');
	});
	
	jQuery('button.billing-same-as-shipping').live('click', function(){
		var answer = confirm(jigoshop_wp.copy_billing);
		if (answer){
			jQuery('input#shipping_first_name').val( jQuery('input#billing_first_name').val() );
			jQuery('input#shipping_last_name').val( jQuery('input#billing_last_name').val() );
			jQuery('input#shipping_company').val( jQuery('input#billing_company').val() );
			jQuery('input#shipping_address_1').val( jQuery('input#billing_address_1').val() );
			jQuery('input#shipping_address_2').val( jQuery('input#billing_address_2').val() );
			jQuery('input#shipping_city').val( jQuery('input#billing_city').val() );
			jQuery('input#shipping_postcode').val( jQuery('input#billing_postcode').val() );
			jQuery('input#shipping_country').val( jQuery('input#billing_country').val() );
			jQuery('input#shipping_state').val( jQuery('input#billing_state').val() );			
		}
		return false;
	});
	
	// PRODUCT TYPE SPECIFIC OPTIONS
	$('select#product-type').change(function(){
		
		// Get value
		var select_val = jQuery(this).val();
		
		// Hide options
		$('#jigoshop-product-type-options .inside > div').hide();
		$('#'+select_val+'_product_options').show();
		
		// Show option
		if (select_val=='configurable') {
			jQuery('.inventory_tab, .pricing_tab').show();
		} else if (select_val=='simple') {
			jQuery('.inventory_tab, .pricing_tab').show();
		} else if (select_val=='grouped') {
			jQuery('.inventory_tab, .pricing_tab').hide();
		} else if (select_val=='downloadable') {
			jQuery('.inventory_tab, .pricing_tab').show();
		} else if (select_val=='virtual') {
			jQuery('.inventory_tab, .pricing_tab').show();
		}
		
		$('body').trigger('jigoshop-product-type-change', select_val, $(this) );
		
	}).change();

	// STOCK OPTIONS
	jQuery('input#manage_stock').change(function(){
		if (jQuery(this).is(':checked')) jQuery('div.stock_fields').show();
		else jQuery('div.stock_fields').hide();
	}).change();
	
	
	// DATE PICKER FIELDS
	Date.firstDayOfWeek = 1;
	Date.format = 'yyyy-mm-dd';
	jQuery('.date-pick').datePicker();
	jQuery('#sale_price_dates_from').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				jQuery('#sale_price_dates_to').dpSetStartDate(d.addDays(1).asString());
			}
		}
	);
	jQuery('#sale_price_dates_to').bind(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				jQuery('#sale_price_dates_from').dpSetEndDate(d.addDays(-1).asString());
			}
		}
	);
	
	
	// ATTRIBUTE TABLES
	
		// Initial order
		var jigoshop_attributes_table_items = jQuery('#attributes_list').children('tr').get();
		jigoshop_attributes_table_items.sort(function(a, b) {
		   var compA = jQuery(a).attr('rel');
		   var compB = jQuery(b).attr('rel');
		   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
		})
		jQuery(jigoshop_attributes_table_items).each( function(idx, itm) { jQuery('#attributes_list').append(itm); } );
		
		// Show
		function show_attribute_table() {
			jQuery('table.jigoshop_attributes, table.jigoshop_configurable_attributes').each(function(){
				if (jQuery('tbody tr', this).size()==0) 
					jQuery(this).parent().hide();
				else 
					jQuery(this).parent().show();
			});
		}
		show_attribute_table();
		
		function row_indexes() {
			jQuery('#attributes_list tr').each(function(index, el){ jQuery('.attribute_position', el).val( parseInt( jQuery(el).index('#attributes_list tr') ) ); });
		};
		
		// Add rows
		jQuery('button.add_attribute').click(function(){
			
			var size = jQuery('table.jigoshop_attributes tbody tr').size();
			
			var attribute_type = jQuery('select.attribute_taxonomy').val();
			
			if (!attribute_type) {
				
				// Add custom attribute row
				jQuery('table.jigoshop_attributes tbody').append('<tr><td class="center"><button type="button" class="button move_up">&uarr;</button><button type="button" class="move_down button">&darr;</button><input type="hidden" name="attribute_position[' + size + ']" class="attribute_position" value="' + size + '" /></td><td><input type="text" name="attribute_names[' + size + ']" /><input type="hidden" name="attribute_is_taxonomy[' + size + ']" value="0" /></td><td><input type="text" name="attribute_values[' + size + ']" /></td><td class="center"><input type="checkbox" checked="checked" name="attribute_visibility[' + size + ']" value="1" /></td><td>&nbsp;</td><td class="center"><button type="button" class="remove_row button">&times;</button></td></tr>');
				
			} else {
				
				// Reveal taxonomy row
				var thisrow = jQuery('table.jigoshop_attributes tbody tr.' + attribute_type);
				jQuery('table.jigoshop_attributes tbody').append( jQuery(thisrow) );
				jQuery(thisrow).show();
				row_indexes();
				
			}
	
			show_attribute_table();
		});
		
		jQuery('button.hide_row').live('click', function(){
			var answer = confirm("Remove this attribute?")
			if (answer){
				jQuery(this).parent().parent().find('select').val('');
				jQuery(this).parent().parent().hide();
				show_attribute_table();
			}
			return false;
		});
		
		jQuery('button.add_configurable_attribute').click(function(){
			jQuery('table.jigoshop_configurable_attributes tbody').append('<tr><td><button type="button" class="move_up button">&uarr;</button><button type="button" class="move_down button">&darr;</button></td><td><input type="text" name="configurable_attribute_names[]" /></td><td><textarea rows="3" cols="20" name="configurable_attribute_values[]"></textarea></td><td><button type="button" class="remove_row button">&times;</button></td></tr>');
			show_attribute_table();	
		});
		
		jQuery('#attributes_list button.remove_row').live('click', function(){
			var answer = confirm("Remove this attribute?")
			if (answer){
				jQuery(this).parent().parent().remove();
				show_attribute_table();
				row_indexes();
			}
			return false;
		});
		
		jQuery('button.move_up').live('click', function(){
			var row = jQuery(this).parent().parent();
			var prev_row = jQuery(row).prev('tr');
			jQuery(row).after(prev_row);
			row_indexes();
		});
		jQuery('button.move_down').live('click', function(){
			var row = jQuery(this).parent().parent();
			var next_row = jQuery(row).next('tr');
			jQuery(row).before(next_row);
			row_indexes();
		});


});