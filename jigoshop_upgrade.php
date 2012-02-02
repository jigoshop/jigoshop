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
 * @package    Jigoshop
 * @category   Core
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
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
                                    'country' => $country,
                                    'label' => '', // no label created as of yet
                                    'state' => $st,
                                    'rate' => $rate,
                                    'shipping' => $shipping,
                                    'class' => $class,
                                    'compound' => 'no', //no such thing as compound taxes, so value is no
                                    'is_all_states' => true //determines if admin panel should show 'all_states'
                                );
                endforeach;
                
            else : // do normal tax_rates array with the additional parameters
                    $tax_rates[] = array(
                                    'country' => $country,
                                    'label' => '', // no label created as of yet
                                    'state' => $state,
                                    'rate' => $rate,
                                    'shipping' => $shipping,
                                    'class' => $class,
                                    'compound' => 'no', //no such thing as compound taxes, so value is no
                                    'is_all_states' => false //determines if admin panel should show 'all_states'
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

		$taxes = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id = {$post->ID} AND meta_key LIKE 'tax_%' ");

		// Update catch all prices
		$parent_id = $post->post_parent;
		$parent_reg_price = get_post_meta( $parent_id, 'regular_price', true );
		$parent_sale_price = get_post_meta( $parent_id, 'sale_price', true );

		if ( ! get_post_meta( $post->ID, 'regular_price', true) && $parent_reg_price )
			update_post_meta( $post->ID, 'regular_price', $parent_reg_price );

		if( ! get_post_meta( $post->ID, 'sale_price', true) && $parent_sale_price )
			update_post_meta( $post->ID, 'sale_price', $parent_sale_price );

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