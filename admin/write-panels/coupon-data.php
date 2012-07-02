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
	
	// disable the permalink slug display
	?>
		<style type="text/css">
			#edit-slug-box { display:none }
		</style>
		<div id="coupon_options" class="panel jigoshop_options_panel">

			<div class="options_group">

			<?php
				
			// Coupon Types
			$args = array(
				'id'            => 'type',
				'label'         => __( 'Coupon Type', 'jigoshop' ),
				'options'       => jigoshop_coupons::get_coupon_types(),
			);
			echo Jigoshop_Form::select( $args );
		
			// Amount
			$args = array(
				'id'            => 'amount',
				'label'         => __( 'Coupon Amount', 'jigoshop' ),
				'desc'          => __('Enter an amount e.g. 9.99.','jigoshop'),
				'tip'           => __('Amount this coupon is worth. If it is a percentange, just include the number without the percentage sign.','jigoshop'),
				'placeholder'   => '0.00'
			);
			echo Jigoshop_Form::input( $args );
				
			// Date From
			$coupon_date_from = get_post_meta( $post->ID, 'date_from', true);
			$args = array(
				'id'            => 'date_from',
				'label'         => __('Date From','jigoshop'),
				'desc'          => '',
				'tip'           => __('Choose between which dates this coupon is enabled.','jigoshop'),
				'class'         => 'short date-pick',
				'placeholder'   => __('Any date','jigoshop'),
				'value'         => ($coupon_date_from <> '') ? date( 'Y-m-d', $coupon_date_from ) : ''
			);
			echo Jigoshop_Form::input( $args );
		
			// Date To
			$coupon_date_to = get_post_meta( $post->ID, 'date_to', true);
			$args = array(
				'id'            => 'date_to',
				'label'         => __('Date To','jigoshop'),
				'desc'          => '',
				'tip'           => __('Choose between which dates this coupon is enabled.','jigoshop'),
				'class'         => 'short date-pick',
				'placeholder'   => __('Any date','jigoshop'),
				'value'         => ($coupon_date_to <> '') ? date( 'Y-m-d', $coupon_date_to ) : ''
			);
			echo Jigoshop_Form::input( $args );
		
			// Usage limit
			$usage = get_post_meta( $post->ID, 'usage', true);
			$args = array(
				'id'            => 'usage_limit',
				'label'         => __( 'Usage Limit', 'jigoshop' ),
				'desc'          => __(sprintf('Times used: %s', !empty( $usage ) ? $usage : '0'), 'jigoshop'),
				'tip'           => __('Control how many times this coupon may be used.','jigoshop'),
				'placeholder'   => '0'
			);
			echo Jigoshop_Form::input( $args );

			// Individual use
			$args = array(
				'id'            => 'individual_use',
				'label'         => __('Individual Use','jigoshop'),
				'desc'          => __('Prevent other coupons from being used while this one is applied to the Cart.','jigoshop'),
				'value'         => false
			);
			echo Jigoshop_Form::checkbox( $args );
		
			// Free shipping
			$args = array(
				'id'            => 'coupon_free_shipping',
				'label'         => __('Free shipping','jigoshop'),
				'desc'          => __('Show the Free Shipping method on the Checkout with this enabled.','jigoshop'),
				'value'         => false
			);
			echo Jigoshop_Form::checkbox( $args );
			
		?>
			</div><div class="options_group">
		<?php
			
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
			
		?>
			</div><div class="options_group">
		<?php
			
			// Include product ID's
			$selected = get_post_meta( $post->ID, 'products', true );
			$args = array(
				'id'            => 'products',
				'type'          => 'hidden',
				'label'         => __( 'Include Products', 'jigoshop' ),
				'desc'          => __('Control which products this coupon can apply to.','jigoshop')
			);
			echo Jigoshop_Form::input( $args );

			// Exclude product ID's
			$selected = get_post_meta( $post->ID, 'exclude_products', true );
			$args = array(
				'id'            => 'exclude_products',
				'type'          => 'hidden',
				'label'         => __( 'Exclude Products', 'jigoshop' ),
				'desc'          => __('Control which products this coupon cannot be applied to.','jigoshop')
			);
			echo Jigoshop_Form::input( $args );
			
		?>
			</div><div class="options_group">
		<?php
			
			// Include Categories
			$categories = get_terms( 'product_cat', array( 'hide_empty' => false ));
			$coupon_cats = array();
			foreach ( $categories as $category )
				$coupon_cats[$category->term_id] = $category->name;
			$args = array(
				'id'            => 'coupon_category',
				'label'         => __( 'Include Categories', 'jigoshop' ),
				'desc'          => __('Control which product categories this coupon can apply to.','jigoshop'),
				'multiple'      => true,
				'placeholder'   => __('Any category','jigoshop'),
				'options'       => $coupon_cats
			);
			echo Jigoshop_Form::select( $args );
			
			// Exclude Categories
			$args = array(
				'id'            => 'exclude_categories',
				'label'         => __( 'Exclude Categories', 'jigoshop' ),
				'desc'          => __('Control which product categories this coupon cannot be applied to.','jigoshop'),
				'multiple'      => true,
				'placeholder'   => __('Any category','jigoshop'),
				'options'       => $coupon_cats
			);
			echo Jigoshop_Form::select( $args );
			
		?>
			</div><div class="options_group">
		<?php
			
			// Payment methods
			$payment_methods = array();
			$available_gateways = jigoshop_payment_gateways::get_available_payment_gateways();
			if ( ! empty( $available_gateways )) foreach ( $available_gateways as $id => $info )
				$payment_methods[$id] = $info->title;
			$args = array(
				'id'            => 'coupon_pay_methods',
				'label'         => __( 'Payment Methods', 'jigoshop' ),
				'desc'          => __('Which payment methods are allowed for this coupon to be effective?','jigoshop'),
				'multiple'      => true,
				'placeholder'   => __('Any method','jigoshop'),
				'options'       => $payment_methods
			);
			echo Jigoshop_Form::select( $args );
		
			// javascript for product includes and excludes -- need to move this
		?>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('#date_from').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
					jQuery('#date_to').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
					
					jQuery("#products").select2({
						minimumInputLength: 3,
						multiple: true,
						ajax: {
							url: "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
							dataType: 'json',
							quietMillis: 100,
							data: function(term, page) {
								return {
									term:       term,
									action:     'jigoshop_json_search_products_and_variations',
									security:   '<?php echo wp_create_nonce( "search-products" ); ?>'
								};
							},
							results: function( data, page ) {
								return { results: data };
							}
						},
						initSelection: function(element) {
							var data = [];
							jQuery(element.val().split(",")).each(function() {
								var stuff = {
									action:     'jigoshop_json_search_products_and_variations',
									security:   '<?php echo wp_create_nonce( "search-products" ); ?>',
									term:       this
								};
								var value = jQuery.ajax({
									type: 		'GET',
									url:        "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
									dataType: 	"json",
									data: 		stuff,
									success: 	function( result ) {
										return result.text;
									}
								});
								data.push( { id: this, text: value } );
							});
							return data;
						}
					});
	
	
					jQuery("#exclude_products").select2({
						minimumInputLength: 3,
						multiple: true,
						ajax: {
							url: "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
							dataType: 'json',
							quietMillis: 100,
							data: function(term, page) {
								return {
									term:       term,
									action:     'jigoshop_json_search_products_and_variations',
									security:   '<?php echo wp_create_nonce( "search-products" ); ?>'
								};
							},
							results: function( data, page ) {
								return { results: data };
							}
						},
						initSelection: function(element) {
							var data = [];
							jQuery(element.val().split(",")).each(function() {
								var stuff = {
									action:     'jigoshop_json_search_products_and_variations',
									security:   '<?php echo wp_create_nonce( "search-products" ); ?>',
									term:       this
								};
								var value = jQuery.ajax({
									type: 		'GET',
									url:        "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
									dataType: 	"json",
									data: 		stuff,
									success: 	function( result ) {
										return result.text;
									}
								});
								data.push( { id: this, text: value } );
							});
							return data;
						}
					});
				});
			</script>
		</div></div>
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
	
	$type = strip_tags( stripslashes( $_POST['type'] ));
	$amount = strip_tags( stripslashes( $_POST['amount'] ));
	
	if ( !empty( $_POST['date_from'] )) {
		$coupon_date_from = strtotime( strip_tags( stripslashes( $_POST['date_from'] )));
	} else {
		$coupon_date_from = '';
	}
	
	if ( !empty( $_POST['date_to'] )) {
		$coupon_date_to = strtotime( strip_tags( stripslashes( $_POST['date_to'] ))) + (60 * 60 * 24 - 1);
	} else {
		$coupon_date_to = '';
	}
	
	$usage_limit = ( isset( $_POST['usage_limit'] ) && $_POST['usage_limit'] > 0 ) ? (int) strip_tags( stripslashes( $_POST['usage_limit'] )) : '';
	$individual = isset( $_POST['individual_use'] );
	$free_shipping = isset( $_POST['coupon_free_shipping'] );
	
	$minimum_amount = strip_tags( stripslashes( $_POST['order_total_min'] ));
	$maximum_amount = strip_tags( stripslashes( $_POST['order_total_max'] ));

	if ( isset( $_POST['products'] )) {
		$include_products = $_POST['products'];
	} else {
		$include_products = '';
	}
	
	if ( isset( $_POST['exclude_products'] )) {
		$exclude_products = $_POST['exclude_products'];
	} else {
		$exclude_products = '';
	}
	
	if ( isset( $_POST['coupon_category'] )) {
		$include_categories = $_POST['coupon_category'];
	} else {
		$include_categories = '';
	}
	
	if ( isset( $_POST['exclude_categories'] )) {
		$exclude_categories = $_POST['exclude_categories'];
	} else {
		$exclude_categories = '';
	}
	
	if ( isset( $_POST['coupon_pay_methods'] )) {
		$pay_methods = (array) $_POST['coupon_pay_methods'];
	} else {
		$pay_methods = '';
	}
		
	update_post_meta( $post_id, 'type',                 $type );
	update_post_meta( $post_id, 'amount',               $amount );
	update_post_meta( $post_id, 'date_from',            $coupon_date_from );
	update_post_meta( $post_id, 'date_to',              $coupon_date_to );
	update_post_meta( $post_id, 'usage_limit',          $usage_limit );
	update_post_meta( $post_id, 'individual_use',       $individual );
	update_post_meta( $post_id, 'coupon_free_shipping', $free_shipping );
	update_post_meta( $post_id, 'order_total_min',      $minimum_amount );
	update_post_meta( $post_id, 'order_total_max',      $maximum_amount );
	update_post_meta( $post_id, 'products',             $include_products );
	update_post_meta( $post_id, 'exclude_products',     $exclude_products );
	update_post_meta( $post_id, 'coupon_category',      $include_categories );
	update_post_meta( $post_id, 'exclude_categories',   $exclude_categories );
	update_post_meta( $post_id, 'coupon_pay_methods',   $pay_methods );

}

/**
 * Search for products and return json
 */
function jigoshop_json_search_products( $x = '', $post_types = array( 'product' )) {

	check_ajax_referer( 'search-products', 'security' );

	$term = (string) urldecode( stripslashes( strip_tags( $_GET['term'] )));

	if ( empty( $term )) die();

	if ( is_numeric( $term )) {

		$args = array(
			'post_type'     => $post_types,
			'post_status'   => 'publish',
			'posts_per_page'=> -1,
			'post__in'      => array(0, $term),
			'fields'        => 'ids'
		);
		$posts = get_posts( $args );

	} else {

		$args = array(
			'post_type'     => $post_types,
			'post_status'   => 'publish',
			'posts_per_page'=> -1,
			's'             => $term,
			'fields'        => 'ids'
		);

		$args2 = array(
			'post_type'     => $post_types,
			'post_status'   => 'publish',
			'posts_per_page'=> -1,
			'meta_query'    => array(
				array(
				'key'       => 'sku',
				'value'     => $term,
				'compare'   => 'LIKE'
				)
			),
			'fields'        => 'ids'
		);
		$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ));

	}

	$found_products = array();

	if ( $posts ) foreach ( $posts as $post ) {

		$SKU = get_post_meta( $post, '_sku', true );

		if ( isset( $SKU ) && $SKU ) $SKU = ' (SKU: ' . $SKU . ')';

		$found_products[] = array( 'id' => $post, 'text' => get_the_title( $post ) . $SKU );

	}

	echo json_encode( $found_products );

	die();
}
add_action( 'wp_ajax_jigoshop_json_search_products', 'jigoshop_json_search_products' );


function jigoshop_json_search_products_and_variations() {

	jigoshop_json_search_products( '', array( 'product', 'product_variation' ));

}
add_action( 'wp_ajax_jigoshop_json_search_products_and_variations', 'jigoshop_json_search_products_and_variations' );
