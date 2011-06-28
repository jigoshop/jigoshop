<?php 
	if (!defined('ABSPATH')) :
		define('WP_INSTALLING', true); // Prevent all plugins loading!! This is a nasty hack, but it does make the file load much faster.
		$root = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
		require_once( $root.'/wp-load.php' );
		require_once( $root.'/wp-includes/wp-db.php' );
		require_once( dirname(dirname(dirname(__FILE__))).'/jigoshop.php' );
	endif;
	header("Content-type: text/javascript"); 
	
	if (isset($_GET['load_scripts'])) $load_scripts = explode(',', $_GET['load_scripts']); else $load_scripts = array();
?>

jQuery.fn.animateHighlight = function(highlightColor, duration) {
    var highlightBg = highlightColor || "#FFFF9C";
    var animateMs = duration || 1500;
    var originalBg = this.css("backgroundColor");
    this.stop().css("background-color", highlightBg).animate({backgroundColor: originalBg}, animateMs);
};

jQuery(function(){
	
	// Lightbox
	jQuery('a.zoom').fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	true
	});
	
	// Star ratings
	jQuery('#rating').hide().before('<p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p>');
	
	jQuery('p.stars a').click(function(){
		jQuery('#rating').val(jQuery(this).text());
		jQuery('p.stars a').removeClass('active');
		jQuery(this).addClass('active');
		return false;
	});

	// Price slider
	var min_price = jQuery('.price_slider_amount #min_price').val();
	var max_price = jQuery('.price_slider_amount #max_price').val();
	
	<?php	
		if (isset($_SESSION['min_price'])) echo 'current_min_price = '.$_SESSION['min_price'].';'; else echo 'current_min_price = min_price;';
		if (isset($_SESSION['max_price'])) echo 'current_max_price = '.$_SESSION['max_price'].';'; else echo 'current_max_price = max_price;';
	?>
	
	jQuery('.price_slider').slider({
		range: true,
		min: min_price,
		max: max_price,
		values: [ current_min_price, current_max_price ],
		create : function( event, ui ) {
			jQuery( ".price_slider_amount span" ).html( "<?php echo get_jigoshop_currency_symbol(); ?>" + current_min_price + " - <?php echo get_jigoshop_currency_symbol(); ?>" + current_max_price );
			jQuery( ".price_slider_amount #min_price" ).val(current_min_price);
			jQuery( ".price_slider_amount #max_price" ).val(current_max_price);
		},
		slide: function( event, ui ) {
			jQuery( ".price_slider_amount span" ).html( "<?php echo get_jigoshop_currency_symbol(); ?>" + ui.values[ 0 ] + " - <?php echo get_jigoshop_currency_symbol(); ?>" + ui.values[ 1 ] );
			jQuery( "input#min_price" ).val(ui.values[ 0 ]);
			jQuery( "input#max_price" ).val(ui.values[ 1 ]);
		}
	});
			
	// Quantity buttons
	jQuery("div.quantity, td.quantity").append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />');
	jQuery(".plus").click(function()
	{
	    var currentVal = parseInt(jQuery(this).prev(".qty").val());
	   
	    if (!currentVal || currentVal=="" || currentVal == "NaN") currentVal = 0;
	    
	    jQuery(this).prev(".qty").val(currentVal + 1); 
	});
	
	jQuery(".minus").click(function()
	{
	    var currentVal = parseInt(jQuery(this).next(".qty").val());
	    if (currentVal == "NaN") currentVal = 0;
	    if (currentVal > 0)
	    {
	        jQuery(this).next(".qty").val(currentVal - 1);
	    }
	});
	
	/* states */
	var states = jQuery.parseJSON('<?php echo json_encode(jigoshop_countries::$states); ?>');			
	
	jQuery('select.country_to_state').change(function(){
		
		var country = jQuery(this).val();
		var state_box = jQuery('#' + jQuery(this).attr('rel'));
		
		var input_name = jQuery(state_box).attr('name');
		var input_id = jQuery(state_box).attr('id');

		if (states[country]) {
			var options = '';
			var state = states[country];
			for(var index in state) {
				options = options + '<option value="' + index + '">' + state[index] + '</option>';
			}
			if (jQuery(state_box).is('input')) {
				// Change for select
				jQuery(state_box).replaceWith('<select name="' + input_name + '" id="' + input_id + '"><option value=""><?php _e('Select a state&hellip;', 'jigoshop'); ?></option></select>');
				state_box = jQuery('#' + jQuery(this).attr('rel'));
			}
			jQuery(state_box).append(options);
		} else {
			if (jQuery(state_box).is('select')) {
				jQuery(state_box).replaceWith('<input type="text" placeholder="<?php _e('state', 'jigoshop'); ?>" name="' + input_name + '" id="' + input_id + '" />');
				state_box = jQuery('#' + jQuery(this).attr('rel'));
			}
		}
		
	}).change();
	
	/* Tabs */
	jQuery('#tabs .panel:not(#tabs .panel)').hide();
	jQuery('#tabs li a').click(function(){
		var href = jQuery(this).attr('href');
		jQuery('#tabs li').removeClass('active');
		jQuery('div.panel').hide();
		jQuery('div' + href).show();
		jQuery(this).parent().addClass('active');
		jQuery.cookie('current_tab', href);
		return false;
	});
	if (jQuery('#tabs li.active').size()==0) {
		jQuery('#tabs li:first a').click();
	} else {
		jQuery('#tabs li.active a').click();
	}
	
	/* Shipping calculator */
	
	jQuery('.shipping-calculator-form').hide();
	
	jQuery('.shipping-calculator-button').click(function() {
	  jQuery('.shipping-calculator-form').slideToggle('slow', function() {
	    // Animation complete.
	  });
	}); 
	
	// Stop anchors moving the viewport

	jQuery(".shipping-calculator-button").click(function() { return false; });
	
});

<?php if (in_array('checkout', $load_scripts)) : ?>

var updateTimer;

function update_checkout() {

	var method = jQuery('#shipping_method').val();
	
	var country 	= jQuery('#billing-country').val();
	var state 		= jQuery('#billing-state').val();
	var postcode 	= jQuery('input#billing-postcode').val();
		
	if (jQuery('#shiptobilling input').is(':checked') || jQuery('#shiptobilling input').size()==0) {
		var s_country 	= jQuery('#billing-country').val();
		var s_state 	= jQuery('#billing-state').val();
		var s_postcode 	= jQuery('input#billing-postcode').val();
		
	} else {
		var s_country 	= jQuery('#shipping-country').val();
		var s_state 	= jQuery('#shipping-state').val();
		var s_postcode 	= jQuery('input#shipping-postcode').val();
	}
	
	jQuery('#order_methods, #order_review').block({ message: null, overlayCSS: { background: '#fff url(<?php echo jigoshop::plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
	jQuery.ajax({
		type: 		'POST',
		url: 		'<?php echo jigoshop_get_template_file_url('checkout/review_order.php', true); ?>',
		data: 		{ shipping_method: method, country: country, state: state, postcode: postcode, s_country: s_country, s_state: s_state, s_postcode: s_postcode },
		success: 	function( code ) {
						jQuery('#order_methods, #order_review').remove();
						jQuery('#order_review_heading').after(code);
						jQuery('#order_review input[name=payment_method]:checked').click();
					},
		dataType: 	"html"
	});

}
	
jQuery(function(){
	
	jQuery('p.password').hide();
	
	jQuery('input.show_password').change(function(){
		jQuery('p.password').slideToggle();
	});
	
	jQuery('div.shipping-address').hide();
	
	jQuery('#shiptobilling input').change(function(){
		jQuery('div.shipping-address').hide();
		if (!jQuery(this).is(':checked')) {
			jQuery('div.shipping-address').slideDown();
		}
	}).change();
	
	<?php if (get_option('jigoshop_enable_guest_checkout')=='yes') : ?>
		
		jQuery('div.create-account').hide();
		
		jQuery('input#createaccount').change(function(){
			jQuery('div.create-account').hide();
			if (jQuery(this).is(':checked')) {
				jQuery('div.create-account').slideDown();
			}
		}).change();
	
	<?php endif; ?>
	
	jQuery('.payment_methods input.input-radio').live('click', function(){
		jQuery('div.payment_box').hide();
		if (jQuery(this).is(':checked')) {
			jQuery('div.payment_box.' + jQuery(this).attr('ID')).slideDown();
		}
	});
	
	jQuery('#order_review input[name=payment_method]:checked').click();
	
	jQuery('form.login').hide();
	
	jQuery('a.showlogin').click(function(){
		jQuery('form.login').slideToggle();
	});
	
	/* Update totals */
	jQuery('#shipping_method').live('change', function(){
		clearTimeout(updateTimer);
		update_checkout();
	}).change();
	jQuery('input#billing-country, input#billing-state, #billing-postcode, input#shipping-country, input#shipping-state, #shipping-postcode').live('keydown', function(){
		clearTimeout(updateTimer);
		updateTimer = setTimeout("update_checkout()", '1000');
	});
	jQuery('select#billing-country, select#billing-state, select#shipping-country, select#shipping-state, #shiptobilling input').live('change', function(){
		clearTimeout(updateTimer);
		update_checkout();
	});
	
	/* AJAX Form Submission */
	jQuery('form.checkout').submit(function(){
		var form = this;
		jQuery(form).block({ message: null, overlayCSS: { background: '#fff url(<?php echo jigoshop::plugin_url(); ?>/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
		jQuery.ajax({
			type: 		'POST',
			url: 		jQuery(form).attr('action'),
			data: 		jQuery(form).serialize(),
			success: 	function( code ) {
							jQuery('.jigoshop_error, .jigoshop_message').remove();
							try {
								success = jQuery.parseJSON( code );					
								window.location = decodeURI(success.redirect);
							}
							catch(err) {
							  	jQuery(form).prepend( code );
								jQuery(form).unblock(); 
								jQuery.scrollTo(jQuery(form).parent(), { easing:'swing' });
							}
						},
			dataType: 	"html"
		});
		return false;
	});

});

<?php endif; exit; ?>