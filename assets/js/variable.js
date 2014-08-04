(function($) {
	// @todo: varmeta is a temporary name.. this should be a global jigoshop var

	$(function() {

		// This is a unique ID for add_variation
		var ID = 0;

		// Unbind the standard postbox click event
		setTimeout(function(){
			$('.jigoshop_variation.postbox h3').unbind('click.postboxes');
		}, 100);

		// Set up the events bound to the root variable product options so events don't bubble to the document
		$('#variable_product_options')
			.on('change', '.product_type', function(e){
				var $this = $(this);
				panel = '.' + $this.val();
				$root = $this.parent().parent().parent();
				// Hide all the panels first then show the relevant one
				// TODO: can this be improved anyway?
				$root.find('.options').hide();
				$root.find(panel).show();
			})
			.on('click', '.postbox h3', function(e){
				// the jquery event can still be triggered by other child elements, so we need to be explicit
				if(e.target.tagName.toLowerCase() != 'h3' && !$(this).hasClass('handlediv')){
					return;
				}

				$(this).parent().toggleClass('closed');
			})
			.on('click', '.add_variation', function(e){
				// Disable default action
				e.preventDefault();
				// Remove the demo if it exists
				$('.demo.variation').remove();
				// Replace the ID with a unique ID
				html = varmeta.actions.create.panel.replace(/__ID__/gi, ID++ + '_new');
				// Append a new variation panel
				$(html).removeClass('closed').hide().prependTo('.jigoshop_variations').slideDown(150);
				$(this).trigger('jigoshop_add_variation');
			})
			.on('click', '.remove_variation', function(e){
				// Disable default action
				e.preventDefault();
				// Only remove if the user is sure
				if(!confirm(varmeta.actions.remove.confirm)){
					return;
				}
				remove_variation($(this).parent());
			})
			.on('click', '.upload_image_button', function(e){
				e.preventDefault();
				var $this = $(this);
				var $img = $('img', $this);
				var $field = $('.upload_image_id', $this);
				if(!this.bound){
					$this.jigoshop_media({
						field: $field,
						thumbnail: $img,
						bind: false,
						library: {
							type: 'image'
						}
					});
					this.bound = true;
				}
				// Remove the image
				if($field.val() != ''){
					$field.val('');
					$img.attr('src', jigoshop_params.assets_url + '/assets/images/placeholder.png');
				} else {
					$(this).trigger('jigoshop_media');
				}
			})
			.on('click', '.upload_file_button', function(e){
				e.preventDefault();
				var $this = $(this);
				var $field = $this.prev();
				if(!this.bound){
					$this.jigoshop_media({
						field: $field,
						bind: false,
						callback: function(attachment){
							$field.val(attachment.changed.url);
						}
					});
					this.bound = true;
				}
				$(this).trigger('jigoshop_media');
			})
			.on('click', '#do_actions', function(e){
				// Disable default action
				e.preventDefault();
				if(!$('.jigoshop_variation').size()){
					alert(varmeta.i18n.variations_required);
					return;
				}
				// Cache variables
				var $this = $(this);
				$this.trigger($this.prev().val());
			})
			.on({
				remove_all: function(e){
					if(!confirm(varmeta.i18n.remove_all))
						return;
					$('.jigoshop_variation').each(function(){
						remove_variation($(this));
					});
				},
				set_all_regular_prices: function(e){
					value = prompt(varmeta.i18n.set_regular_price);
					$(' input[name*="regular_price"] ').val(value);
				},
				set_all_sale_prices: function(e){
					value = prompt(varmeta.i18n.set_sale_price);
					$(' input[name*="sale_price"] ').val(value);
				},
				set_all_stock: function(e){
					value = prompt(varmeta.i18n.set_stock);
					$(' input[name*="stock"] ').val(value);
				},
				set_all_weight: function(e){
					value = prompt(varmeta.i18n.set_weight);
					$(' input[name*="weight"] ').val(value);
				},
				set_all_width: function(e){
					value = prompt(varmeta.i18n.set_width);
					$(' input[name*="width"] ').val(value);
				},
				set_all_length: function(e){
					value = prompt(varmeta.i18n.set_length);
					$(' input[name*="length"] ').val(value);
				},
				set_all_height: function(e){
					value = prompt(varmeta.i18n.set_height);
					$(' input[name*="height"] ').val(value);
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