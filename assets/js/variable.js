(function($) {
	// @todo: varmeta is a temporary name.. this should be a global jigoshop var

	var i = 0;
	$('button.add_variation').live('click', function(e) {

		// Disable default action
		e.preventDefault();

		// Replace the ID with a unique ID
		html = varmeta.actions.create.panel.replace(/__ID__/gi, i++ +'_new');

		// Append a new variation panel
		$(html).hide().prependTo('.jigoshop_variations').slideDown('fast');

	});

	$('button.remove_variation').live('click', function(e) {

		// Disable default action
		e.preventDefault();

		if ( confirm(varmeta.actions.remove.confirm) ) {
			
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
					$parent.fadeOut(300, function() {
						$parent.remove();
					});
				});

			} 
			else {

				// Variation hasn't been saved so just remove the panel
				$parent.fadeOut(300, function() {
					$parent.remove();
				});
			}

		}
	});


	$('.upload_image_button').live('click', function(e) {

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

})(window.jQuery);