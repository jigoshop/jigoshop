<?php
/**
 * Coupon Data
 *
 * Function for displaying the product data meta boxes
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */


/**
 * Coupon data meta box
 * 
 * Displays the meta box
 */
function jigoshop_coupon_data_meta_box( $post ) {
	global $jigoshop;
	
	wp_nonce_field( 'jigoshop_save_data', 'jigoshop_meta_nonce' );
	
	?>

	<div id="coupon_options" class="panel jigoshop_options_panel">
		<?php
			
			echo '<div class="options_group">';
			
			// Coupon Types
			$coupon_types = array(
				'fixed_cart'        => __('Cart Discount', 'jigoshop'),
				'percent'           => __('Cart % Discount', 'jigoshop'),
				'fixed_product'     => __('Product Discount', 'jigoshop'),
				'percent_product'   => __('Product % Discount', 'jigoshop')
			);
			echo jigoshop_form::select( 'coupon_type', __( 'Coupon Type', 'jigoshop' ), $coupon_types );

			// Amount
			echo jigoshop_form::input( 'coupon_amount', __( 'Coupon Amount', 'jigoshop' ), __('Enter an amount e.g. 9.99.','jigoshop'), null, null, '0.00' );
				
			// Usage limit
			echo jigoshop_form::input( 'usage_limit', __( 'Usage Limit', 'jigoshop' ), __(sprintf('Times used: %s', !empty($coupon['usage']) ? $coupon['usage'] : '0'), 'jigoshop'), null, null, '0' );

			// Order subtotal
			echo jigoshop_form::input( 'order_total_min', __( 'Order Subtotal', 'jigoshop' ), __('Set the required subtotal for this coupon to be valid on an order.','jigoshop'), null, null, __('No min','jigoshop') );


		?>
	</div>
	<?php	
}

/**
 * Coupon Data Save
 * 
 * Function for processing and storing all coupon data.
 */
//add_action('jigoshop_process_shop_coupon_meta', 'jigoshop_process_shop_coupon_meta', 1, 2);

function jigoshop_process_shop_coupon_meta( $post_id, $post ) {
	global $wpdb, $jigoshop_errors;
	
		
}