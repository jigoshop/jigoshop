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
			$args = array(
				'id'            => 'coupon_type',
				'label'         => __( 'Coupon Type', 'jigoshop' ),
				'options'       => $coupon_types,
			);
			echo Jigoshop_Form::select( $args );

			// Amount
			$args = array(
				'id'            => 'coupon_amount',
				'label'         => __( 'Coupon Amount', 'jigoshop' ),
				'desc'          => __('Enter an amount e.g. 9.99.','jigoshop'),
				'placeholder'   => '0.00'
			);
			echo Jigoshop_Form::input( $args );
				
			// Usage limit
			$args = array(
				'id'            => 'usage_limit',
				'label'         => __( 'Usage Limit', 'jigoshop' ),
				'desc'          => __(sprintf('Times used: %s', !empty($coupon['usage']) ? $coupon['usage'] : '0'), 'jigoshop'),
				'placeholder'   => '0'
			);
			echo Jigoshop_Form::input( $args );

			// Order total minimum
			$args = array(
				'id'            => 'order_total_min',
				'label'         => __( 'Order total min', 'jigoshop' ),
				'desc'          => __('Set the required minimum subtotal for this coupon to be valid on an order.','jigoshop'),
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
				'desc'          => __('Which payment methods are allowed for this coupon to be effective?','jigoshop'),
				'options'       => $payment_methods
			);
			echo Jigoshop_Form::select( $args );


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
	
	// Add/Replace data to array
		$type 			= strip_tags(stripslashes( $_POST['discount_type'] ));
		$amount 		= strip_tags(stripslashes( $_POST['coupon_amount'] ));
		$usage_limit 	= (isset($_POST['usage_limit']) && $_POST['usage_limit']>0) ? (int) $_POST['usage_limit'] : '';
		$individual_use = isset($_POST['individual_use']) ? 'yes' : 'no';
		$expiry_date 	= strip_tags(stripslashes( $_POST['expiry_date'] ));
		$apply_before_tax = isset($_POST['apply_before_tax']) ? 'yes' : 'no';
		$free_shipping = isset($_POST['free_shipping']) ? 'yes' : 'no';
		$minimum_amount = strip_tags(stripslashes( $_POST['minimum_amount'] ));
		$customer_email = array_filter(array_map('trim', explode(',', strip_tags(stripslashes( $_POST['customer_email'] )))));
		
		if (isset($_POST['product_ids'])) {
			$product_ids = (array) $_POST['product_ids'];
			$product_ids = implode(',', array_filter(array_map('intval', $product_ids)));
		} else {
			$product_ids = '';
		}
		
		if (isset($_POST['exclude_product_ids'])) {
			$exclude_product_ids = (array) $_POST['exclude_product_ids'];
			$exclude_product_ids = implode(',', array_filter(array_map('intval', $exclude_product_ids)));
		} else {
			$exclude_product_ids = '';
		}
		
		$product_categories = (isset($_POST['product_categories'])) ? array_map('intval', $_POST['product_categories']) : array();
		$exclude_product_categories = (isset($_POST['exclude_product_categories'])) ? array_map('intval', $_POST['exclude_product_categories']) : array();
		
	// Save
		update_post_meta( $post_id, 'discount_type', $type );
		update_post_meta( $post_id, 'coupon_amount', $amount );
		update_post_meta( $post_id, 'individual_use', $individual_use );
		update_post_meta( $post_id, 'product_ids', $product_ids );
		update_post_meta( $post_id, 'exclude_product_ids', $exclude_product_ids );
		update_post_meta( $post_id, 'usage_limit', $usage_limit );
		update_post_meta( $post_id, 'expiry_date', $expiry_date );
		update_post_meta( $post_id, 'apply_before_tax', $apply_before_tax );
		update_post_meta( $post_id, 'free_shipping', $free_shipping );
		update_post_meta( $post_id, 'product_categories', $product_categories );
		update_post_meta( $post_id, 'exclude_product_categories', $exclude_product_categories );
		update_post_meta( $post_id, 'minimum_amount', $minimum_amount );
		update_post_meta( $post_id, 'customer_email', $customer_email );
		
		do_action('jigoshop_coupon_options');
		
}