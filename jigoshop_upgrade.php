<?php
/**
 * Jigoshop Upgrade API
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 * Run Jigoshop Upgrade functions.
 *
 * @return void
*/
function jigoshop_upgrade() {

	// Get the db version
	$jigoshop_db_version = get_site_option( 'jigoshop_db_version' );

	// 'Cause we aint got shiz to do
	if ( $jigoshop_db_version == JIGOSHOP_VERSION )
		return false;

	if ( ! is_numeric($jigoshop_db_version) ) {
		jigoshop_convert_db_version();
	}

	if ( $jigoshop_db_version < 1109200 ) {
		jigoshop_upgrade_99();
	}

	if ( $jigoshop_db_version < 1202010 ) {
		jigoshop_upgrade_100();
	}

	if ( $jigoshop_db_version < 1202130 ) {
		jigoshop_upgrade_110();
	}

	if ( $jigoshop_db_version < 1202280 ) {
		jigoshop_upgrade_111();
	}

    if ( $jigoshop_db_version < 1203310 ) {
        jigoshop_upgrade_120();
    }

	if ( $jigoshop_db_version < 1207160 ) {
		jigoshop_upgrade_130();
	}

	if ( $jigoshop_db_version < 1211190 ) {
		jigoshop_upgrade_145();
	}

	if ( $jigoshop_db_version < 1211270 ) {
		jigoshop_upgrade_146();
	}

 	if ( $jigoshop_db_version < 1301280 ) {
 		jigoshop_upgrade_150();
 	}

 	if ( $jigoshop_db_version < 1303050 ) {
 		jigoshop_upgrade_160();
 	}

 	if ( $jigoshop_db_version < 1303180 ) {
 		jigoshop_upgrade_161();
 	}

	// Update the db option
	update_site_option( 'jigoshop_db_version', JIGOSHOP_VERSION );

	return true;
}

/**
 * Updates jigoshop db version to a numeric value for better comparison
 */
function jigoshop_convert_db_version() {
	global $wpdb;

	$jigoshop_db_version = get_site_option('jigoshop_db_version');

	switch ( $jigoshop_db_version ) {
		case '0.9.6':
			update_site_option( 'jigoshop_db_version', 1105310 );
			break;
		case '0.9.7':
			update_site_option( 'jigoshop_db_version', 1105311 );
			break;
		case '0.9.7.1':
			update_site_option( 'jigoshop_db_version', 1105312 );
			break;
		case '0.9.7.2':
			update_site_option( 'jigoshop_db_version', 1105313 );
			break;
		case '0.9.7.3':
			update_site_option( 'jigoshop_db_version', 1106010 );
			break;
		case '0.9.7.4':
			update_site_option( 'jigoshop_db_version', 1106011 );
			break;
		case '0.9.7.5':
			update_site_option( 'jigoshop_db_version', 1106130 );
			break;
		case '0.9.7.6':
			update_site_option( 'jigoshop_db_version', 1106140 );
			break;
		case '0.9.7.7':
			update_site_option( 'jigoshop_db_version', 1106220 );
			break;
		case '0.9.7.8':
			update_site_option( 'jigoshop_db_version', 1106221 );
			break;
		case '0.9.8':
			update_site_option( 'jigoshop_db_version', 1107010 );
			break;
		case '0.9.8.1':
			update_site_option( 'jigoshop_db_version', 1109080 );
			break;
		case '0.9.9':
			update_site_option( 'jigoshop_db_version', 1109200 );
			break;
		case '0.9.9.1':
			update_site_option( 'jigoshop_db_version', 1111090 );
			break;
		case '0.9.9.2':
			update_site_option( 'jigoshop_db_version', 1111091 );
			break;
		case '0.9.9.3':
			update_site_option( 'jigoshop_db_version', 1111092 );
			break;
        // The verion of db was updated since 1.0 to the new standard. No point on continuing to
        // add entries here, since anyone that has post 1.0 will also have the
        // new db versions. Anyone before, will get converted from this function.
	}
}

/**
 * Execute changes made in Jigoshop 0.9.9
 *
 * @since 0.9.9
 */
function jigoshop_upgrade_99() {
	global $wpdb;

	$q = $wpdb->get_results("SELECT *
		FROM $wpdb->term_taxonomy
		WHERE taxonomy LIKE 'product_attribute_%'
	");

	foreach($q as $item) {
		$taxonomy = str_replace('product_attribute_', 'pa_', $item->taxonomy);

		$wpdb->update(
			$wpdb->term_taxonomy,
			array('taxonomy' => $taxonomy),
			array('term_taxonomy_id' => $item->term_taxonomy_id)
		);
	}
}

/**
 * Execute changes made in Jigoshop 1.0
 *
 * @since 1.0.0
 */
function jigoshop_upgrade_100() {
	global $wpdb;

	// Run upgrade

    // upgrade option jigoshop_tax_rates
    $jigoshop_tax_rates = get_site_option('jigoshop_tax_rates');
    $tax_rates = array();

    if ($jigoshop_tax_rates && is_array($jigoshop_tax_rates)) :

        foreach($jigoshop_tax_rates as $key) :
            $country = $key['country'];
            $state = $key['state'];

            // Change canadian province NF and PQ to NL and QC respectively
            if (isset($key['country']) && $key['country'] == 'CA') :
                if ($key['state'] == 'NF') :
                    $state = 'NL';
                elseif ($key['state'] == 'PQ') :
                    $state = 'QC';
                endif;
            endif;

            $rate = $key['rate'];
            $shipping = $key['shipping'];
            $class = $key['class'];

            // convert all-states
            if (jigoshop_countries::country_has_states($country) && $state == '*') :
                foreach (array_keys(jigoshop_countries::$states[$country]) as $st) :
                    $tax_rates[] = array(
									'country'      => $country,
									'label'        => '', // no label created as of yet
									'state'        => $st,
									'rate'         => $rate,
									'shipping'     => $shipping,
									'class'        => $class,
									'compound'     => 'no', //no such thing as compound taxes, so value is no
									'is_all_states'=> true //determines if admin panel should show 'all_states'
                                );
                endforeach;

            else : // do normal tax_rates array with the additional parameters
                    $tax_rates[] = array(
									'country'      => $country,
									'label'        => '', // no label created as of yet
									'state'        => $state,
									'rate'         => $rate,
									'shipping'     => $shipping,
									'class'        => $class,
									'compound'     => 'no', //no such thing as compound taxes, so value is no
									'is_all_states'=> false //determines if admin panel should show 'all_states'
                                );

            endif;
        endforeach;

        update_option('jigoshop_tax_rates', $tax_rates);

    endif;

    // convert products

	$args = array(
		'post_type'	  => 'product',
		'numberposts' => -1,
		'post_status' => 'any', // Fixes draft products not being upgraded
	);

	$posts = get_posts( $args );

	foreach( $posts as $post ) {

		// Convert SKU key to lowercase
		$wpdb->update( $wpdb->postmeta, array('meta_key' => 'sku'), array('post_id' => $post->ID, 'meta_key' => 'sku') );

		// Change redirect add to cart option name
		$checkoutValue = get_option( 'jigoshop_directly_to_checkout' );

		if ($checkoutValue == "no" )
			$checkoutValue = "same_page";

		else if ($checkoutValue == "cart")
			$checkoutValue = "to_cart";

		else if ($checkoutValue == "yes")
			$checkoutValue = "to_checkout";

		update_option( 'jigoshop_redirect_add_to_cart' , $checkoutValue );
		delete_option( 'jigoshop_directly_to_checkout' );

		// Convert featured to true/false
		$featured = get_post_meta( $post->ID, 'featured', true);

		if ( $featured == 'yes' )
			update_post_meta( $post->ID, 'featured', true );
		else {
			update_post_meta( $post->ID, 'featured', false);
		}

		// Convert the filepath to url
		$file_path = get_post_meta( $post->ID, 'file_path', true );
		update_post_meta( $post->ID, 'file_path', site_url().'/'.$file_path );

		// Unserialize all product_data keys to individual key => value pairs
		$product_data = get_post_meta( $post->ID, 'product_data', true );
		if ( is_array($product_data) ) {
			foreach( $product_data as $key => $value ) {

				// Convert all keys to lowercase
				// @todo: Needs testing especially with 3rd party plugins using product_data
				$key = strtolower($key);

				// We now call it tax_classes & its an array
				if ( $key == 'tax_class' ) {

					if ( $value )
						$value = (array) $value;
					else
						$value = array('*');

					$key = 'tax_classes';
				}

				// Convert manage stock to true/false
				if ( $key == 'manage_stock' ) {
					$value = ( $value == 'yes' ) ? true : false;
				}

				// Create the meta
				update_post_meta( $post->ID, $key, $value );

				// Remove the old meta
				delete_post_meta( $post->ID, 'product_data' );
			}
		}

		$product_attributes = get_post_meta( $post->ID, 'product_attributes', true );

		if ( is_array($product_attributes) ) {
			foreach( $product_attributes as $key => $attribute ) {

				// We use true/false for these now
				if ( isset( $attribute['visible'] ) )
					$attribute['visible']     = ( $attribute['visible'] == 'yes' ) ? true : false;

				if ( isset( $attribute['variation'] ) )
					$attribute['variation']   = ( $attribute['variation'] == 'yes' ) ? true : false;

				if ( isset( $attribute['is_taxonomy'] ) )
					$attribute['is_taxonomy'] = ( $attribute['is_taxonomy'] == 'yes' ) ? true : false;

				$product_attributes[$key] = $attribute;
			}

			update_post_meta( $post->ID, 'product_attributes', $product_attributes );
		}
	}

	// Variations
	$args = array(
		'post_type'	  => 'product_variation',
		'numberposts' => -1,
		'post_status' => 'any', // Fixes draft products not being upgraded
	);

	$posts = get_posts( $args );

	foreach( $posts as $post ) {

		// Convert SKU key to lowercase
		$wpdb->update( $wpdb->postmeta, array('meta_key' => 'sku'), array('post_id' => $post->ID, 'meta_key' => 'sku') );

		// Convert 'price' key to regular_price
		$wpdb->update( $wpdb->postmeta, array('meta_key' => 'regular_price'), array('post_id' => $post->ID, 'meta_key' => 'price') );

		$taxes = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s", $post->ID, 'tax_%' ) );

		// Update catch all prices, weights, and dimensions
		$parent_id = $post->post_parent;
		$parent_reg_price = get_post_meta( $parent_id, 'regular_price', true );
		$parent_sale_price = get_post_meta( $parent_id, 'sale_price', true );

        // weight and dimensions were in pre 1.0. Therefore, make sure all of this
        // data gets converted as well
		$parent_weight = get_post_meta( $parent_id, 'weight', true );
		$parent_length = get_post_meta( $parent_id, 'length', true );
		$parent_height = get_post_meta( $parent_id, 'height', true );
		$parent_width  = get_post_meta( $parent_id, 'width',  true );

		if ( ! get_post_meta( $post->ID, 'regular_price', true) && $parent_reg_price )
			update_post_meta( $post->ID, 'regular_price', $parent_reg_price );

		if( ! get_post_meta( $post->ID, 'sale_price', true) && $parent_sale_price )
			update_post_meta( $post->ID, 'sale_price', $parent_sale_price );

		if( ! get_post_meta( $post->ID, 'weight', true) && $parent_weight )
			update_post_meta( $post->ID, 'weight', $parent_weight );

		if( ! get_post_meta( $post->ID, 'length', true) && $parent_length )
			update_post_meta( $post->ID, 'length', $parent_length );

		if( ! get_post_meta( $post->ID, 'height', true) && $parent_height )
			update_post_meta( $post->ID, 'height', $parent_height );

		if( ! get_post_meta( $post->ID, 'width', true) && $parent_width )
			update_post_meta( $post->ID, 'width', $parent_width );

		$variation_data = array();
		foreach( $taxes as $tax ) {
			$variation_data[$tax->meta_key] = $tax->meta_value;
			delete_post_meta( $post->ID, $tax->meta_key );
		}

		update_post_meta( $post->ID, 'variation_data', $variation_data );
	}

	// Update shop order comments type
	$wpdb->update( $wpdb->comments, array(
		'comment_type' => 'jigoshop',
		'comment_author' => 'Jigoshop',
		'comment_author_email' => '',
		'comment_author_IP' => '',
	), array('user_id' => 0, 'comment_author' => 'JigoShop') );
}

/**
 * Execute changes made in Jigoshop 1.1
 *
 * @since 1.1
 */
function jigoshop_upgrade_110() {

	global $wpdb;

	// Add setting to show or hide stock
	update_option( 'jigoshop_show_stock' , 'yes' );

	// New settings for guest control
	update_option( 'jigoshop_enable_guest_login' , 'yes' );
	update_option( 'jigoshop_enable_signup_form' , 'yes' );

	// Add attribute label column to allow non-ascii characters
	$sql = 'ALTER TABLE '. $wpdb->prefix . 'jigoshop_attribute_taxonomies' . ' ADD COLUMN attribute_label longtext NULL';
	$wpdb->query($sql);

}

/**
 * Execute changes made in Jigoshop 1.1.1
 *
 * @since 1.1.1
 */
function jigoshop_upgrade_111() {

	// Add default setting for shop redirection page
	$shop_page = get_option('jigoshop_shop_page_id');
	update_option( 'jigoshop_shop_redirect_page_id' , $shop_page );
	update_option( 'jigoshop_enable_related_products' , 'yes' );

}

function get_old_taxes_as_array($taxes_as_string) {

    $tax_classes = array();

    if ($taxes_as_string) :

        $taxes = explode('|', $taxes_as_string);

        foreach ($taxes as $tax) :

            $tax_class = explode(':', $tax);
            if (isset($tax_class[1])) :
                $tax_info = explode(',', $tax_class[1]);

                if (isset($tax_class[0]) && isset($tax_info[0]) && isset($tax_info[1]) && isset($tax_info[2]) && isset($tax_info[3])) :
                    $tax_classes[$tax_class[0]] = array('amount' => $tax_info[0], 'rate' => $tax_info[1], 'compound' => ($tax_info[2] ? true : false), 'display' => $tax_info[3]);
                endif;

            endif;

        endforeach;

    endif;

    return $tax_classes;
}

/**
 * Execute changes made in Jigoshop 1.2.0
 *
 * @since 1.2
 */
function jigoshop_upgrade_120() {

    // update orders
	$args = array(
		'post_type'	  => 'shop_order',
		'numberposts' => -1,
		'post_status' => 'publish'
	);

	$posts = get_posts( $args );

	foreach( $posts as $post ) :
        $order_data = get_post_meta($post->ID, 'order_data', true);

        if (!empty($order_data['order_tax'])) :

            // means someone has posted a manual order. Need to update to new tax string
            if (strpos($order_data['order_tax'], ':') === false) :
                $order_data['order_tax_total'] = $order_data['order_tax'];
                $order_data['order_tax'] = jigoshop_tax::create_custom_tax($order_data['order_total'] - $order_data['order_tax_total'], $order_data['order_tax_total'], $order_data['order_shipping_tax'], $order_data['order_tax_divisor']);
            else :
                $tax_array = get_old_taxes_as_array($order_data['order_tax']);
                $order_data['order_tax'] = jigoshop_tax::array_implode($tax_array);
            endif;

            update_post_meta($post->ID, 'order_data', $order_data);

        endif;

    endforeach;
    
}

/**
 * Execute changes made in Jigoshop 1.3
 *
 * @since 1.3
 */
function jigoshop_upgrade_130() {
	
	global $wpdb;
	
	/* Update all product variation titles to something useful. */
	$args = array(
		'post_type' => 'product',
		'tax_query' => array(
			array(
				'taxonomy'=> 'product_type',
				'terms'   => 'variable',
				'field'   => 'slug',
				'operator'=> 'IN'
			)
		)
	);
	$posts_array = get_posts( $args );

	foreach ( $posts_array as $post ) {

		$product = new jigoshop_product ( $post->ID );
		$var = $product->get_children();

		foreach ( $var as $id ) {

			$variation = $product->get_child( $id )->variation_data;
			$taxes     = array();

			foreach ( $variation as $k => $v ) :

				if ( strstr ( $k, 'tax_' ) ) {
					$tax  = substr( $k, 4 );
					$taxes[] = sprintf('[%s: %s]', $tax, !empty($v) ? $v : 'Any ' . $tax );
				}

			endforeach;

			if ( !strstr (get_the_title($id), 'Child Variation' ) )
				continue;

			$title = sprintf('%s - %s', get_the_title($post->ID), implode( $taxes, ' ' ) );
			if ( !empty($title) )
				$wpdb->update( $wpdb->posts, array('post_title' => $title), array('ID' => $id) );

		}

	}
	
	// Convert coupon options to new 'shop_coupon' custom post type and create posts
	$args = array(
		'numberposts'	=> -1,
		'post_type'		=> 'shop_coupon',
		'post_status'	=> 'publish'
	);
	$new_coupons = (array) get_posts( $args );
	if ( empty( $new_coupons )) {   /* probably an upgrade from 1.2.3 or less, convert options based coupons */
		$coupons = get_option( 'jigoshop_coupons' );
		$coupon_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'shop_coupon',
			'post_author'    => 1,
			'post_name'      => '',
			'post_content'   => '',
			'comment_status' => 'closed'
		);
		if ( ! empty( $coupons )) foreach ( $coupons as $coupon ) {
			$coupon_data['post_name'] = $coupon['code'];
			$coupon_data['post_title'] = $coupon['code'];
			$post_id = wp_insert_post( $coupon_data );
			update_post_meta( $post_id, 'type', $coupon['type'] );
			update_post_meta( $post_id, 'amount', $coupon['amount'] );
			update_post_meta( $post_id, 'include_products', $coupon['products'] );
			update_post_meta( $post_id, 'date_from', ($coupon['date_from'] <> 0) ? $coupon['date_from'] : '' );
			update_post_meta( $post_id, 'date_to', ($coupon['date_to'] <> 0) ? $coupon['date_to'] : '' );
			update_post_meta( $post_id, 'individual_use', ($coupon['individual_use'] == 'yes') );
		}
	} else {                        /* if CPT based coupons from RC1, convert data for incorrect products meta */
		foreach ( $new_coupons as $id => $coupon ) {
			$product_ids = get_post_meta( $coupon->ID, 'products', true );
			if ( $product_ids <> '' ) update_post_meta( $coupon->ID, 'include_products', $product_ids );
			delete_post_meta( $coupon->ID, 'products', $product_ids );
		}
	}
	
	flush_rewrite_rules( true );

}

/**
 * Execute changes made in Jigoshop 1.4.5
 *
 * @since 1.4.5
 */
function jigoshop_upgrade_145() {
	
	Jigoshop_Base::get_options()->delete_option( 'jigoshop_paypal_send_shipping' );
	delete_option( 'jigoshop_paypal_send_shipping' );
	Jigoshop_Base::get_options()->delete_option( 'jigoshop_display_totals_tax' );
	delete_option( 'jigoshop_display_totals_tax' );
	
}

/**
 * Execute changes made in Jigoshop 1.4.6
 *
 * @since 1.4.6
 */
function jigoshop_upgrade_146() {
	
	Jigoshop_Base::get_options()->add_option( 'jigoshop_show_checkout_shipping_fields', 'yes' );
	
}

/**
 * Execute changes made in Jigoshop 1.5
 *
 * @since 1.5
 */
function jigoshop_upgrade_150() {
	
	Jigoshop_Base::get_options()->add_option( 'jigoshop_cart_shows_shop_button', 'no' );
	Jigoshop_Base::get_options()->add_option( 'jigoshop_enable_postcode_validating', 'no' );
	Jigoshop_Base::get_options()->add_option( 'jigoshop_product_thumbnail_columns', '3' );
	
}

/**
 * Execute changes made in Jigoshop 1.6
 *
 * @since 1.6
 */
function jigoshop_upgrade_160() {
	
	Jigoshop_Base::get_options()->add_option( 'jigoshop_skrill_icon', '' );
	Jigoshop_Base::get_options()->add_option( 'jigoshop_skrill_payment_methods_multicheck', 'ACC' );
	Jigoshop_Base::get_options()->add_option( 'jigoshop_verify_checkout_info_message', 'yes' );
	Jigoshop_Base::get_options()->add_option( 'jigoshop_eu_vat_reduction_message', 'yes' );

}

/**
 * Execute changes made in Jigoshop 1.6.1
 *
 * @since 1.6.1
 */
function jigoshop_upgrade_161() {
	
	Jigoshop_Base::get_options()->add_option( 'jigoshop_catalog_product_button', 'add' );

}
