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
function jigoshop_coupon_data_box( $post ) {
	global $jigoshop;
	
	wp_nonce_field( 'jigoshop_save_data', 'jigoshop_meta_nonce' );
	
	?>
		<style type="text/css">
			#edit-slug-box { display:none }
		</style>
		<div id="coupon_options" class="panel jigoshop_options_panel">
	<?php
			
		echo '<div class="options_group">';
		
		// Coupon Types
		$args = array(
			'id'            => 'coupon_type',
			'label'         => __( 'Coupon Type', 'jigoshop' ),
			'tip'           => __('Cart &ndash; Applies to the whole Cart.  Product &ndash; Applies to individual products only.  You must specify individual products.','jigoshop'),
			'options'       => jigoshop_coupons::get_coupon_types(),
		);
		echo Jigoshop_Form::select( $args );

		// Amount
		$args = array(
			'id'            => 'coupon_amount',
			'label'         => __( 'Coupon Amount', 'jigoshop' ),
			'desc'          => __('Enter an amount e.g. 9.99.','jigoshop'),
			'tip'           => __('Amount this coupon is worth. If it is a percentange, just include the number without the percentage sign.','jigoshop'),
			'placeholder'   => '0.00'
		);
		echo Jigoshop_Form::input( $args );
			
		// Usage limit
		$args = array(
			'id'            => 'usage_limit',
			'label'         => __( 'Usage Limit', 'jigoshop' ),
			'desc'          => __(sprintf('Times used: %s', !empty($coupon['usage']) ? $coupon['usage'] : '0'), 'jigoshop'),
			'tip'           => __('Control how many times this coupon may be used.','jigoshop'),
			'placeholder'   => '0'
		);
		echo Jigoshop_Form::input( $args );

		// Order total minimum
		$args = array(
			'id'            => 'order_total_min',
			'label'         => __( 'Order total min', 'jigoshop' ),
			'desc'          => __('Set the required minimum subtotal for this coupon to be valid on an order.','jigoshop'),
			'tip'           => __('Set the required subtotal for this coupon to be valid on an order.','jigoshop'),
			'placeholder'   => __('No min','jigoshop')
		);
		echo Jigoshop_Form::input( $args );

		// Order total maximum
		$args = array(
			'id'            => 'order_total_max',
			'label'         => __( 'Order total max', 'jigoshop' ),
			'desc'          => __('Set the required maximum subtotal for this coupon to be valid on an order.','jigoshop'),
			'placeholder'   => __('No max','jigoshop')
		);
		echo Jigoshop_Form::input( $args );

		// Payment methods
		$payment_methods = array();
		$available_gateways = jigoshop_payment_gateways::get_available_payment_gateways();
		if ( ! empty($available_gateways) )
			foreach ( $available_gateways as $id => $info )
				$payment_methods[$id] = $info->title;
		$args = array(
			'id'            => 'coupon_pay_methods',
			'label'         => __( 'Payment Methods', 'jigoshop' ),
			'tip'           => __('Which payment methods are allowed for this coupon to be effective?','jigoshop'),
			'multiple'      => true,
			'placeholder'   => __('Any method','jigoshop'),
			'options'       => $payment_methods
		);
		echo Jigoshop_Form::select( $args );

		$args = array(
			'id'            => 'individual_use',
			'label'         => __('Individual Use','jigoshop'),
			'desc'          => __('Prevent other coupons from being used while this one is applied to the Cart.','jigoshop'),
			'value'         => false
		);
		echo Jigoshop_Form::checkbox( $args );

		$args = array(
			'id'            => 'coupon_free_shipping',
			'label'         => __('Free shipping','jigoshop'),
			'desc'          => __('Show the Free Shipping method on the Checkout with this enabled.','jigoshop'),
			'value'         => false
		);
		echo Jigoshop_Form::checkbox( $args );
		
	?>
		</div>
	<?php	
}

/**
 * Coupon Data Save
 * 
 * Function for processing and storing all coupon data.
 */
add_action( 'jigoshop_process_shop_coupon_meta', 'jigoshop_process_shop_coupon_meta', 1, 2 );

function jigoshop_process_shop_coupon_meta( $post_id, $post ) {

	global $wpdb, $jigoshop_errors;
	
	$type 			= strip_tags( stripslashes( $_POST['coupon_type'] ));
	$amount 		= strip_tags( stripslashes( $_POST['coupon_amount'] ));
	$usage_limit 	= ( isset( $_POST['usage_limit'] ) && $_POST['usage_limit'] > 0 ) ? (int) $_POST['usage_limit'] : '';
	$minimum_amount = strip_tags( stripslashes( $_POST['order_total_min'] ));
	$maximum_amount = strip_tags( stripslashes( $_POST['order_total_max'] ));
	$individual     = isset( $_POST['individual_use'] );
	$free_shipping  = isset( $_POST['coupon_free_shipping'] );
	
	if ( isset( $_POST['coupon_pay_methods'] )) {
		$pay_methods = (array) $_POST['coupon_pay_methods'];
	} else {
		$pay_methods = '';
	}
		
	update_post_meta( $post_id, 'coupon_type', $type );
	update_post_meta( $post_id, 'coupon_amount', $amount );
	update_post_meta( $post_id, 'usage_limit', $usage_limit );
	update_post_meta( $post_id, 'order_total_min', $minimum_amount );
	update_post_meta( $post_id, 'order_total_max', $maximum_amount );
	update_post_meta( $post_id, 'individual_use', $individual );
	update_post_meta( $post_id, 'coupon_free_shipping', $free_shipping );
	
	update_post_meta( $post_id, 'coupon_pay_methods', $pay_methods );
		
}