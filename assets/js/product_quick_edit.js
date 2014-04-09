(function($) {

	// we create a copy of the WP inline edit post function for Quick Edit
	var $wp_inline_edit = inlineEditPost.edit;

	// and then we overwrite the function with our own code
	inlineEditPost.edit = function( id ) {

		// "call" the original WP edit function, we don't want to leave WordPress hanging
		$wp_inline_edit.apply( this, arguments );

		// now we take care of our business

		// get the post ID for this Quick Edit Product
		var $post_id = 0;
		if ( typeof( id ) == 'object' ) $post_id = parseInt( this.getId( id ) );

		if ( $post_id > 0 ) {

			// determine the current edit row
			var $edit_row = $( '#edit-' + $post_id );

			var data = {
				action: 	'jigoshop_get_product_stock_price',
				security: 	jigoshop_quick_edit_params.get_stock_price_nonce,
				post_id:	$post_id
			};

			$.ajax( {
				type: 		'GET',
				url: 		jigoshop_quick_edit_params.ajax_url,
				data: 		data,
				dataType:	'json',
				success: 	function( response ) {
					$edit_row.find( 'input[name="stock"]' ).val( response.stock );
					$edit_row.find( 'input[name="price"]' ).val( response.price );
				}
			});

		}

	};

	$( 'body' ).on( 'click', '#bulk_edit', function() {

		// define the bulk edit row
		var $bulk_row = $( '#bulk-edit' );

		// get the selected post ids that are being edited
		var $post_ids = new Array();
		$bulk_row.find( '#bulk-titles' ).children().each( function() {
			$post_ids.push( $( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
		});

		// get the stock and price values to save for all the product ID's
		var $stock = $bulk_row.find( 'input[name="stock"]' ).val();
		var $price = $bulk_row.find( 'input[name="price"]' ).val();

		var data = {
			action: 		'jigoshop_save_bulk_edit',
			security: 		jigoshop_quick_edit_params.update_stock_price_nonce,
			post_ids:		$post_ids,
			stock:			$stock,
			price:			$price,
		};

		// save the data
		$.ajax({
			url: jigoshop_quick_edit_params.ajax_url,
			type: 'POST',
			async: false,
			cache: false,
			data: data
		});

	});

})(jQuery);