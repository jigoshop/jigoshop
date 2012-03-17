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
 * @package		Jigoshop
 * @category	Core
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
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

	if ( $jigoshop_db_version < 1202290 ) {
		jigoshop_upgrade_120();
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
		case '1.0':
			update_site_option( 'jigoshop_db_version', 1202090 );
			break;
		case '1.1':
			update_site_option( 'jigoshop_db_version', 1202130 );
			break;
		case '1.1.1':
			update_site_option( 'jigoshop_db_version', 1202280 );
			break;
		case '1.2':
			update_site_option( 'jigoshop_db_version', 1202290 );
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
		$parent_width = get_post_meta( $parent_id, 'width', true );

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

/**
 * Execute changes made in Jigoshop 1.2
 *
 * @since 1.2
 */
function jigoshop_upgrade_120() {
	
	global $wpdb;
	
	// convert all options to new Jigoshop_Options class
	require_once ( 'admin/jigoshop-admin-settings-options.php' );
	global $options_settings;
	foreach ( $options_settings as $setting ) {
		if ( isset( $setting['id'] )) {
			switch ( $setting['id'] ) {
			case 'jigoshop_shop_tiny':
			case 'jigoshop_shop_thumbnail':
			case 'jigoshop_shop_small':
			case 'jigoshop_shop_large':
				$current = get_option( $setting['id'].'_w' );
				if ( ! (false === $current) ) {
					Jigoshop_Options::set_option( $setting['id'].'_w', $current );
//					delete_option( $setting['id'].'_w' );
				}
				$current = get_option( $setting['id'].'_h' );
				if ( ! (false === $current) ) {
					Jigoshop_Options::set_option( $setting['id'].'_h', $current );
//					delete_option( $setting['id'].'_h' );
				}
				break;
			case 'jigoshop_display_totals_tax':
				$current = get_option( $setting['id'] );
				if ( ! (false === $current) ) {
					if ( $current == 'including' )
						Jigoshop_Options::set_option( $setting['id'], 'yes' );
					else
						Jigoshop_Options::set_option( $setting['id'], 'no' );
//					delete_option( $setting['id'] );
				}
				break;
			default:
				$current = get_option( $setting['id'] );
				if ( ! (false === $current) ) {
					Jigoshop_Options::set_option( $setting['id'], $current );
//					delete_option( $setting['id'] );
				}
				break;
			}
		}
	}
	
	// get all other current 'jigoshop_' namespace options from the 'options' table
	$options_in_use = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE 'jigoshop_%%';" ));
	
	foreach ( $options_in_use as $index => $setting ) {
		if ( $setting->option_name == 'jigoshop_options' ) continue;
		if ( ! Jigoshop_Options::exists_option( $setting->option_name ) ) {
			Jigoshop_Options::set_option( $setting->option_name, maybe_unserialize( $setting->option_value ));
//			delete_option( $setting->option_name );
		}
	}

	flush_rewrite_rules( true );

}