jQuery(function($){
	"use strict";
	var params = jigoshop_params || { assets_url: '' };
	$.fn.payment = function(options){
		var settings = $.extend({
			redirect: 'Redirecting...',
			message: 'Thank you for your order. We are now redirecting you to make payment.'
		}, options);

		$(document.body).block({
			message: '<img src="'+params.assets_url+'/assets/images/ajax-loader.gif" alt="'+settings.redirect+'" />'+settings.message,
			overlayCSS: {
				background: "#fff",
				opacity: 0.6
			},
			css: {
				padding: 20,
				textAlign: "center",
				color: "#555",
				border: "3px solid #aaa",
				backgroundColor: "#fff",
				cursor: "wait"
			}
		});
		$(this).submit();
	};
});
