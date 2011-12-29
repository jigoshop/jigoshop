(function($) {
	// @todo: varmeta is a temporary name.. this should be a global jigoshop var

	$(function() {
		
		// This is a unique ID for add_variation
		var ID = 0;

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

		// @todo: this should be an ID
		.on('click', '.add_variation', function(e) {
			
			// Disable default action
			e.preventDefault();

			// Replace the ID with a unique ID
			html = varmeta.actions.create.panel.replace(/__ID__/gi, ID++ +'_new');

			// Append a new variation panel
			$(html).hide().prependTo('.jigoshop_variations').slideDown('fast');
		})

		// @todo: this should be an ID
		.on('click', '.remove_variation', function(e) {
			// Disable default action
			e.preventDefault();

			// Only remove if the user is sure
			if ( ! confirm(varmeta.actions.remove.confirm) )
				return false;
			
			// Set up the variables
			var $parent		= $(this).parent().parent();
				variation	= $(this).attr('rel');
				data 		= {
					action: varmeta.actions.remove.action,
					variation_id: variation,
					security: varmeta.actions.remove.nonce,
				};
			
			// If the variation already exists
			if ( variation.indexOf('_new') < 0 ) {

				// Start the block to simulate AJAX requests
				$parent.block({ message: null, overlayCSS: { background: '#fff url('+varmeta.plugin_url+'/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

				// Remove the variation from the posts array
				$.post(varmeta.ajax_url, data, function(response) {
					$parent.fadeOut('slow', function() {
						$parent.remove();
					});
				});

			} 
			else {

				// Variation hasn't been saved so just remove the panel
				$parent.fadeOut('slow', function() {
					$parent.remove();
				});
			}
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
				$img.attr('src', varmeta.plugin_url+'/assets/images/placeholder.png');
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

				// Why do we need this? -Rob
				// formfield = $('.upload_image_id', $parent).attr('name');

				// Show thickbox
				tb_show('', 'media-upload.php?post_id'+post_id+'&type=image&TB_iframe=true');
			}
		});
	});


})(window.jQuery);