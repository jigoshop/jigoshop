<?php
/**
 * JigoShop Write Panels
 * 
 * Sets up the write panels used by products and orders (custom post types)
 *
 * @author 		Jigowatt
 * @category 	Admin Write Panels
 * @package 	JigoShop
 */

include('write-panels/product-data.php');
include('write-panels/product-data-save.php');
include('write-panels/product-type.php');
include('write-panels/order-data.php');
include('write-panels/order-data-save.php');

/**
 * Init the meta boxes
 * 
 * Inits the write panels for both products and orders. Also removes unused default write panels.
 *
 * @since 		1.0
 */
add_action( 'add_meta_boxes', 'jigoshop_meta_boxes' );

function jigoshop_meta_boxes() {
	add_meta_box( 'jigoshop-product-data', __('Product Data', 'jigoshop'), 'jigoshop_product_data_box', 'product', 'normal', 'high' );
	add_meta_box( 'jigoshop-product-type-options', __('Product Type Options', 'jigoshop'), 'jigoshop_product_type_options_box', 'product', 'normal', 'high' );
	
	add_meta_box( 'jigoshop-order-data', __('Order Data', 'jigoshop'), 'jigoshop_order_data_meta_box', 'shop_order', 'normal', 'high' );
	add_meta_box( 'jigoshop-order-items', __('Order Items <small>&ndash; Note: if you edit quantities or remove items from the order you will need to manually change the item\'s stock levels.</small>', 'jigoshop'), 'jigoshop_order_items_meta_box', 'shop_order', 'normal', 'high');
	
	add_meta_box( 'jigoshop-order-actions', __('Order Actions', 'jigoshop'), 'jigoshop_order_actions_meta_box', 'shop_order', 'side', 'default');
	
	remove_meta_box( 'commentstatusdiv', 'shop_order' , 'normal' );
	remove_meta_box( 'slugdiv', 'shop_order' , 'normal' );
}

/**
 * Save meta boxes
 * 
 * Runs when a post is saved and does an action which the write panel save scripts can hook into.
 *
 * @since 		1.0
 */
add_action( 'save_post', 'jigoshop_meta_boxes_save', 1, 2 );

function jigoshop_meta_boxes_save( $post_id, $post ) {
	global $wpdb;
	
	if ( !$_POST ) return $post_id;
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
	if ( !isset($_POST['jigoshop_meta_nonce']) || (isset($_POST['jigoshop_meta_nonce']) && !wp_verify_nonce( $_POST['jigoshop_meta_nonce'], 'jigoshop_save_data' ))) return $post_id;
	if ( !current_user_can( 'edit_post', $post_id )) return $post_id;
	if ( $post->post_type != 'product' && $post->post_type != 'shop_order' ) return $post_id;
	
	do_action( 'jigoshop_process_'.$post->post_type.'_meta', $post_id, $post );
}

/**
 * Product data
 * 
 * Forces certain product data based on the product's type, e.g. grouped products cannot have a parent.
 *
 * @since 		1.0
 */
add_filter('wp_insert_post_data', 'jigoshop_product_data');

function jigoshop_product_data( $data ) {
	global $post;
	if ($data['post_type']=='product' && isset($_POST['product-type'])) {
		$product_type = stripslashes( $_POST['product-type'] );
		switch($product_type) :
			case "grouped" :
			case "configurable" :
			case "downloadable" :
				$data['post_parent'] = 0;
			break;
		endswitch;
	}
	return $data;
}

/**
 * Order data
 * 
 * Forces the order posts to have a title in a certain format (containing the date)
 *
 * @since 		1.0
 */
add_filter('wp_insert_post_data', 'jigoshop_order_data');

function jigoshop_order_data( $data ) {
	global $post;
	if ($data['post_type']=='shop_order' && isset($data['post_date'])) {
		
		$order_title = 'Order';
		if ($data['post_date']) $order_title.= ' &ndash; '.date('F j, Y @ h:i A', strtotime($data['post_date']));
		
		$data['post_title'] = $order_title;
	}
	return $data;
}


/**
 * Save errors
 * 
 * Stores error messages in a variable so they can be displayed on the edit post screen after saving.
 *
 * @since 		1.0
 */
add_action( 'admin_notices', 'jigoshop_meta_boxes_save_errors' );

function jigoshop_meta_boxes_save_errors() {
	$jigoshop_errors = maybe_unserialize(get_option('jigoshop_errors'));
    if ($jigoshop_errors && sizeof($jigoshop_errors)>0) :
    	echo '<div id="jigoshop_errors" class="error fade">';
    	foreach ($jigoshop_errors as $error) :
    		echo '<p>'.$error.'</p>';
    	endforeach;
    	echo '</div>';
    	update_option('jigoshop_errors', '');
    endif; 
}

/**
 * Meta scripts
 * 
 * Outputs JavaScript used by the meta panels.
 *
 * @since 		1.0
 */
function jigoshop_meta_scripts() {
	?>
	<script type="text/javascript" src="<?php echo jigoshop::plugin_url(); ?>/assets/js/date.js"></script>
	<script type="text/javascript" src="<?php echo jigoshop::plugin_url(); ?>/assets/js/datepicker.js"></script>
	<script type="text/javascript" src="<?php echo jigoshop::plugin_url(); ?>/admin/write-panels/write-panels.js.php"></script>
	<script type="text/javascript">
		jQuery(function(){
			<?php do_action('product_write_panel_js'); ?>
		});
	</script>
	<?php
}
