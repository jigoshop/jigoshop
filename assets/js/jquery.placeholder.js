(function($) {

	/**
	 * Spoofs placeholders in browsers that don't support them (eg Firefox 3)
	 *
	 * Copyright 2011 Dan Bentley
	 * Licensed under the Apache License 2.0
	 *
	 * Author: Dan Bentley [github.com/danbentley]
	 */

	// Return if native support is available.
	if ("placeholder" in document.createElement("input")) return;

	$(document).ready(function(){
		$(':input[placeholder]').each(function() {
			setupPlaceholder($(this));
		});

		$('form').submit(function(e) {
			clearPlaceholdersBeforeSubmit($(this));
		});
	});

	function setupPlaceholder(input) {

		var placeholderText = input.attr('placeholder');

		if (input.val() === '') input.val(placeholderText);
		input.bind({
			focus: function(e) {
				if (input.val() === placeholderText) input.val('');
			},
			blur: function(e) {
				if (input.val() === '') input.val(placeholderText);
			}
		});
	}

	function clearPlaceholdersBeforeSubmit(form) {
		form.find(':input[placeholder]').each(function() {
			var el = $(this);
			if (el.val() === el.attr('placeholder')) el.val('');
		});
	}
})(jQuery);
