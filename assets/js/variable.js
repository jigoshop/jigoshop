(function($) {
	// @todo: varmeta is a temporary name.. this should be a global jigoshop var

	$(function() {

		// This is a unique ID for add_variation
		var ID = 0;

		// Unbind the standard postbox click event
		$('.jigoshop_variation.postbox h3').unbind('click.postboxes');

		// Set up the events bound to the root variable product options so events don't bubble to the document
		$('#variable_product_options')
		.on('change', '.product_type', function(e) {

			var $this	= $(this);
		 	      panel	= '.'+$this.val();
		 	      $root	= $this.parent().parent().parent()

			// Hide all the panels first then show the relevant one
			// TODO: can this be improved anyway?
			$root.find('.options').hide();
			$root.find(panel).show();
		})

		.on('click', '.postbox h3', function(e) {

			// the jquery event can still be triggered by other child elements, so we need to be explicit
			if ( e.target.tagName.toLowerCase() != 'h3' && !$(this).hasClass('handlediv'))
				return false;

			$(this).parent().toggleClass('closed');
		})

		// @todo: this should be an ID
		.on('click', '.add_variation', function(e) {

			// Disable default action
			e.preventDefault();

			// Remove the demo if it exists
			$('.demo.variation').remove();

			// Replace the ID with a unique ID
			html = varmeta.actions.create.panel.replace(/__ID__/gi, ID++ +'_new');

			// Append a new variation panel
			$(html).removeClass('closed').hide().prependTo('.jigoshop_variations').slideDown( 150 );
		})

		// @todo: this should be an ID
		.on('click', '.remove_variation', function(e) {

			// Disable default action
			e.preventDefault();

			// Only remove if the user is sure
			if ( ! confirm(varmeta.actions.remove.confirm) )
				return false;

			remove_variation($(this).parent());
		})

		.on('click', '.upload_image_button', function(e) {

			// Disable default action
			e.preventDefault();

			// Set up variables
			var $this		= $(this);
				$img		= $this.find('img');
				$imgID		= $this.find('input');
				$parent		= $this.parent();
				post_id		= $this.attr('rel');
				formfield	= $('.upload_image_id').attr('name');

			// Remove the image
			if ( $this.is('.remove') ) {

				// Set the hidden input value as null
				$imgID.val(null);

				// Replace the image with the placeholder
				$img.attr('src', varmeta.assets_url+'/assets/images/placeholder.png');
				$this.removeClass('remove');
			}
			else {

				window.send_to_editor = function( html ) {

					// Set up variables
					var $img		= $(html).find('img');
						imgsrc		= $img.attr('src');
						imgclass		= $img.attr('class');
						imgid 		= parseInt(imgclass.replace(/\D/g, ''), 10);

					// Set the vhidden input value with the thumb ID
					$imgID.val(imgid);

					// Replace the image with a preview of the image
					$('img', $parent).attr('src', imgsrc);
					$this.addClass('remove');

					// Hide thickbox
					tb_remove();
				}

				// @todo: Why do we need this? -Rob
				// formfield = $('.upload_image_id', $parent).attr('name');

				// Show thickbox
				tb_show('', 'media-upload.php?post_id'+post_id+'&type=image&TB_iframe=true');
			}
		})

		.on('click', '.upload_file_button', function(e) {

			// Disable default action
			e.preventDefault();

			// Set up variables
			var $this   = $(this);
			    $file   = $this.prev();
			    post_id = $this.parents('.jigoshop_variation').attr('rel');

			window.send_to_editor = function(html) {

				// Attach the file URI to the relevant
				$file.val( $(html).attr('href') );

				// Hide thickbox
				tb_remove();
			}

			// @todo: Why do we need this? -Rob
			// formfield = $(parent).attr('name');

			// Show thickbox
			tb_show('', 'media-upload.php?post_id=' + post_id + '&type=downloadable_product&from=jigoshop_variation&TB_iframe=true');
		})

		.on('click', '#do_actions', function(e) {

			// Disable default action
			e.preventDefault();

			if ( ! $('.jigoshop_variation').size() )
				return alert( varmeta.i18n.variations_required );

			// Cache variables
			var $this = $(this);
			$this.trigger( $this.prev().val() );
		})

		.on({
			remove_all: function(e) {
				if( ! confirm( varmeta.i18n.remove_all ) )
					return false;

				$('.jigoshop_variation').each( function() {
					remove_variation( $(this) );
				});
			},
			set_all_regular_prices: function(e) {
				value = prompt( varmeta.i18n.set_regular_price );
				$(' input[name*="regular_price"] ').val( value );
			},
			set_all_sale_prices: function(e) {
				value = prompt( varmeta.i18n.set_sale_price );
				$(' input[name*="sale_price"] ').val( value );
			},
			set_all_stock: function(e) {
				value = prompt( varmeta.i18n.set_stock );
				$(' input[name*="stock"] ').val( value );
			},
			set_all_weight: function(e) {
				value = prompt( varmeta.i18n.set_weight );
				$(' input[name*="weight"] ').val( value );
			},
			set_all_width: function(e) {
				value = prompt( varmeta.i18n.set_width );
				$(' input[name*="width"] ').val( value );
			},
			set_all_length: function(e) {
				value = prompt( varmeta.i18n.set_length );
				$(' input[name*="length"] ').val( value );
			},
			set_all_height: function(e) {
				value = prompt( varmeta.i18n.set_height );
				$(' input[name*="height"] ').val( value );
			}
		});

	});

	function remove_variation( $panel ) {

		// Set up the variables
		var variation	= $panel.attr('rel');
			data 		= {
				action: varmeta.actions.remove.action,
				variation_id: variation,
				security: varmeta.actions.remove.nonce,
			};

		// If the variation already exists
		if ( variation.indexOf('_new') < 0 ) {

			// Start the block to simulate AJAX requests
			$panel.block({ message: null, overlayCSS: { background: '#fff url('+varmeta.assets_url+'/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

			// Remove the variation from the posts array
			$.post(varmeta.ajax_url, data, function(response) {
				$panel.fadeOut('slow', function() {
					$panel.remove();
				});
			});

		}
		else {

			// Variation hasn't been saved so just remove the panel
			$panel.fadeOut('slow', function() {
				$panel.remove();
			});
		}
	}

})(window.jQuery);