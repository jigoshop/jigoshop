<?php
/**
 * Functions used for Product Stock and Price editing using WordPress Bulk and Quick Edit
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 *	Props to Rachel Carden (http://rachelcarden.com/)
 *	Adapted from: http://rachelcarden.com/2012/03/manage-wordpress-posts-using-bulk-edit-and-quick-edit/
 */

/**
 *	Product Bulk and Quick Edit scripts
 */
add_action( 'admin_print_scripts-edit.php', 'jigoshop_enqueue_product_quick_scripts' );

function jigoshop_enqueue_product_quick_scripts() {

	global $pagenow, $typenow;

	if ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
		$post = get_post( $_GET['post'] );
		$typenow = $post->post_type;
	}
	if ( $typenow == 'product' ) {
		wp_enqueue_script( 'jigoshop-admin-quickedit', jigoshop::assets_url().'/assets/js/product_quick_edit.js', array( 'jquery', 'inline-edit-post' ), '', true );

		$jigoshop_quick_edit_params = array(
			'assets_url' 				=> jigoshop::assets_url(),
			'ajax_url' 					=> ( ! is_ssl() ) ? str_replace( 'https', 'http', admin_url( 'admin-ajax.php' ) ) : admin_url( 'admin-ajax.php' ),
			'get_stock_price_nonce'		=> wp_create_nonce( "get-product-stock-price" ),
			'update_stock_price_nonce'	=> wp_create_nonce( "update-product-stock-price" ),
		);

		wp_localize_script( 'jigoshop-admin-quickedit', 'jigoshop_quick_edit_params', $jigoshop_quick_edit_params );
	}
}

/**
 *	AJAX callback to get current stock and price for a Product for Quick Edit
 */
add_action( 'wp_ajax_jigoshop_get_product_stock_price', 'jigoshop_ajax_get_product_stock_price' );

function jigoshop_ajax_get_product_stock_price() {

	check_ajax_referer( 'get-product-stock-price', 'security' );

	$values = array();

	$_product = new jigoshop_product( $_GET['post_id'] );

	if ( $_product->managing_stock() ) {
		$values['stock'] = get_post_meta( $_GET['post_id'], 'stock', true );
	} else {
		$values['stock'] = __('Not managed','jigoshop');
	}
	if ( $_product->is_type( array( 'grouped' ) ) ) {
		$values['price'] = __('Grouped parent has no price','jigoshop');
	} else {
		if (get_post_meta( $_GET['post_id'], 'regular_price', true ) == null){
			$values['price']='';
		}
		else {
		$values['price'] = sprintf( "%.2F", get_post_meta( $_GET['post_id'], 'regular_price', true ) );
		}
	}

	die( json_encode( $values ) );
}

/**
 *	Output a Stock and Price input display field for Bulk and Quick edit on the Product List
 */
add_action( 'bulk_edit_custom_box', 'jigoshop_add_to_bulk_quick_edit_custom_box', 10, 2 );
add_action( 'quick_edit_custom_box', 'jigoshop_add_to_bulk_quick_edit_custom_box', 10, 2 );

function jigoshop_add_to_bulk_quick_edit_custom_box( $column_name, $post_type ) {

	switch ( $post_type ) {
	case 'product':
		switch ( $column_name ) {
		case 'stock':
			?>
			<fieldset class="inline-edit-col-right">
				<div class="inline-edit-col">
					<label><span class="title"><?php _e( 'Stock', 'jigoshop' ); ?></span>
					<input type="text" name="stock" value="" />
					</label>
				</div>
			</fieldset>
			<?php
			break;
		case 'price':
			?>
			<fieldset class="inline-edit-col-right">
				<div class="inline-edit-col">
					<label><span class="title"><?php _e( 'Price', 'jigoshop' ); ?></span>
					<input type="text" name="price" value="" />
					</label>
				</div>
			</fieldset>
			<?php
			break;
		}
		break;
	}
}

/**
 *	Quick Edit save routine
 */
add_action( 'save_post','jigoshop_save_quick_edit', 10, 2 );

function jigoshop_save_quick_edit( $post_id, $post ) {

	// don't save for autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

	// don't save for revisions
	if ( isset( $post->post_type ) && $post->post_type == 'revision' ) return $post_id;

	switch ( $post->post_type ) {
	case 'product':
		$_product = new jigoshop_product( $post_id );
		if ( array_key_exists( 'stock', $_POST ) && $_product->managing_stock() ) {
			$stock = empty( $_POST['stock'] ) ? 0 : jigoshop_sanitize_num( $_POST[ 'stock' ] );
			// TODO: do we need to check to hide products at low stock threshold? (-JAP-)
			update_post_meta( $post_id, 'stock', $stock );
		}
		if ( array_key_exists( 'price', $_POST ) && ! empty( $_POST['price'] ) ) {
			if ( ! $_product->is_type( array( 'grouped' ) ) ) {
				if ($_POST[ 'price' ] == null){
				update_post_meta( $post_id, 'regular_price', '' );
				}
				else{
				update_post_meta( $post_id, 'regular_price', jigoshop_sanitize_num( $_POST[ 'price' ] ) );
				}
			}
		}
		break;
   }

}

/**
 *	AJAX callback for Bulk Edit save routine
 */
add_action( 'wp_ajax_jigoshop_save_bulk_edit', 'jigoshop_save_bulk_edit' );

function jigoshop_save_bulk_edit() {

	check_ajax_referer( 'update-product-stock-price', 'security' );

	$post_ids = ( isset( $_POST[ 'post_ids' ] ) && ! empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : array();
	$stock = ( isset( $_POST[ 'stock' ] ) && ! empty( $_POST[ 'stock' ] ) ) ? $_POST[ 'stock' ] : NULL;
	$price = ( isset( $_POST[ 'price' ] ) && ! empty( $_POST[ 'price' ] ) ) ? $_POST[ 'price' ] : NULL;

	if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
		foreach ( $post_ids as $post_id ) {
			$_product = new jigoshop_product( $post_id );
			if ( $_product->managing_stock() ) {
				$stock = empty( $stock ) ? 0 : jigoshop_sanitize_num( $stock );
				// TODO: do we need to check to hide products at low stock threshold? (-JAP-)
				update_post_meta( $post_id, 'stock', $stock );
			}
			if ( ! empty( $price ) && ! $_product->is_type( array( 'grouped' ) ) ) {
				update_post_meta( $post_id, 'regular_price', jigoshop_sanitize_num( $price ) );
			}
		}
	}

	die();

}

