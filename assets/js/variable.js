(function($) {
	// NOTE: varmeta is a temporary name.. this should be a global jigoshop var

	

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
			if ( variation ) {
				// Start the block to simulate AJAX requests
				$parent.block({ message: null, overlayCSS: { background: '#fff url('+varmeta.plugin_url+'/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

				// Remove the variation from the posts array
				$.post(varmeta.ajax_url, data, function(response) {
					$parent.fadeOut('300', function() {
						$parent.remove();
					});
				});

			} 
			else {

				// Variation hasn't been saved so just remove the panel
				$parent.fadeOut('300', function() {
					$parent.remove();
				});
			}

		}
	});


})(window.jQuery);