jQuery(function($){
	$.fn.jigoshop_media = function(options){
		var frame = false;
		var settings = $.extend({
			field: $('#media-library-file'),
			thumbnail: false,
			callback: false,
			library: {},
			bind: true
		}, options);

		$(this).on('jigoshop_media', function(e){
			e.preventDefault();
			var $el = $(this);

			// If the media frame already exists, reopen it.
			if(frame){
				frame.open();
				return;
			}

			// Create the media frame.
			frame = wp.media({
				// Set the title of the modal.
				title: $el.data('title'),
				// Tell the modal to show only images.
				library: settings.library,
				// Customize the submit button.
				button: {
					// Set the text of the button.
					text: $el.data('button')
				}
			});

			// When an image is selected, run a callback.
			frame.on('select', function(){
				// Grab the selected attachment.
				var attachment = frame.state().get('selection').first();

				if(settings.field){
					settings.field.val(attachment.id);
				}
				if(settings.thumbnail){
					settings.thumbnail.attr('src', attachment.changed.url);
				}
				if(typeof(settings.callback) == 'function'){
					settings.callback(attachment);
				}
			});

			frame.open();
		});

		if(settings.bind){
			$(this).on('click', function(){
				$(this).trigger('jigoshop_media');
			});
		}
	};
});