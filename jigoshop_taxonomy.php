<?php
/**
 * Custom Post Types
 **/
function jigoshop_post_type() {

	global $wpdb;
	
	$shop_page_id = get_option('jigoshop_shop_page_id');
	
	$base_slug = $shop_page_id && get_page_uri( get_option('jigoshop_shop_page_id') ) ? get_page_uri( get_option('jigoshop_shop_page_id') ) : 'shop';	
	
	register_taxonomy( 'product_cat',
        array('product'),
        array(
            'hierarchical' => true,
            'update_count_callback' => '_update_post_term_count',
            'labels' => array(
                    'name' => __( 'Product Categories', 'jigoshop'),
                    'singular_name' => __( 'Product Category', 'jigoshop'),
                    'search_items' =>  __( 'Search Product Categories', 'jigoshop'),
                    'all_items' => __( 'All Product Categories', 'jigoshop'),
                    'parent_item' => __( 'Parent Product Category', 'jigoshop'),
                    'parent_item_colon' => __( 'Parent Product Category:', 'jigoshop'),
                    'edit_item' => __( 'Edit Product Category', 'jigoshop'),
                    'update_item' => __( 'Update Product Category', 'jigoshop'),
                    'add_new_item' => __( 'Add New Product Category', 'jigoshop'),
                    'new_item_name' => __( 'New Product Category Name', 'jigoshop')
            ),
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => $base_slug . '/' . _x('category', 'slug', 'jigoshop'), 'with_front' => false ),
        )
    );
    
    register_taxonomy( 'product_tag',
        array('product'),
        array(
            'hierarchical' => false,
            'labels' => array(
                    'name' => __( 'Product Tags', 'jigoshop'),
                    'singular_name' => __( 'Product Tag', 'jigoshop'),
                    'search_items' =>  __( 'Search Product Tags', 'jigoshop'),
                    'all_items' => __( 'All Product Tags', 'jigoshop'),
                    'parent_item' => __( 'Parent Product Tag', 'jigoshop'),
                    'parent_item_colon' => __( 'Parent Product Tag:', 'jigoshop'),
                    'edit_item' => __( 'Edit Product Tag', 'jigoshop'),
                    'update_item' => __( 'Update Product Tag', 'jigoshop'),
                    'add_new_item' => __( 'Add New Product Tag', 'jigoshop'),
                    'new_item_name' => __( 'New Product Tag Name', 'jigoshop')
            ),
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => $base_slug . '/' . _x('tag', 'slug', 'jigoshop'), 'with_front' => false ),
        )
    );
    
    $attribute_taxonomies = jigoshop::$attribute_taxonomies;    
	if ( $attribute_taxonomies ) :
		foreach ($attribute_taxonomies as $tax) :
	    	
	    	$name = 'product_attribute_'.strtolower(sanitize_title($tax->attribute_name));
	    	$hierarchical = true;
	    	if ($name) :

	    		register_taxonomy( $name,
			        array('product'),
			        array(
			            'hierarchical' => $hierarchical,
			            'labels' => array(
			                    'name' => $tax->attribute_name,
			                    'singular_name' =>$tax->attribute_name,
			                    'search_items' =>  __( 'Search ', 'jigoshop') . $tax->attribute_name,
			                    'all_items' => __( 'All ', 'jigoshop') . $tax->attribute_name,
			                    'parent_item' => __( 'Parent ', 'jigoshop') . $tax->attribute_name,
			                    'parent_item_colon' => __( 'Parent ', 'jigoshop') . $tax->attribute_name . ':',
			                    'edit_item' => __( 'Edit ', 'jigoshop') . $tax->attribute_name,
			                    'update_item' => __( 'Update ', 'jigoshop') . $tax->attribute_name,
			                    'add_new_item' => __( 'Add New ', 'jigoshop') . $tax->attribute_name,
			                    'new_item_name' => __( 'New ', 'jigoshop') . $tax->attribute_name
			            ),
			            'show_ui' => false,
			            'query_var' => true,
			            'show_in_nav_menus' => false,
			            'rewrite' => array( 'slug' => $base_slug . '/' . strtolower(sanitize_title($tax->attribute_name)), 'with_front' => false, 'hierarchical' => $hierarchical ),
			        )
			    );
	    		
	    	endif;
	    endforeach;    	
    endif;
    
	register_post_type( "product",
		array(
			'labels' => array(
				'name' => __( 'Products', 'jigoshop' ),
				'singular_name' => __( 'Product', 'jigoshop' ),
				'add_new' => __( 'Add Product', 'jigoshop' ),
				'add_new_item' => __( 'Add New Product', 'jigoshop' ),
				'edit' => __( 'Edit', 'jigoshop' ),
				'edit_item' => __( 'Edit Product', 'jigoshop' ),
				'new_item' => __( 'New Product', 'jigoshop' ),
				'view' => __( 'View Product', 'jigoshop' ),
				'view_item' => __( 'View Product', 'jigoshop' ),
				'search_items' => __( 'Search Products', 'jigoshop' ),
				'not_found' => __( 'No Products found', 'jigoshop' ),
				'not_found_in_trash' => __( 'No Products found in trash', 'jigoshop' ),
				'parent' => __( 'Parent Product', 'jigoshop' )
			),
			'description' => __( 'This is where you can add new products to your store.', 'jigoshop' ),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'menu_position' => 57,
			'hierarchical' => true,
			'rewrite' => array( 'slug' => $base_slug, 'with_front' => false ),
			'query_var' => true,			
			'supports' => array( 'title', 'editor', 'thumbnail', 'comments'/*, 'page-attributes'*/ ),
			'has_archive' => $base_slug,
			'show_in_nav_menus' => false,
		)
	);
	
    register_taxonomy( 'product_type',
        array('product'),
        array(
            'hierarchical' => false,
            'show_ui' => false,
            'query_var' => true,
            'show_in_nav_menus' => false,
        )
    );
    
    register_post_type( "shop_order",
		array(
			'labels' => array(
				'name' => __( 'Orders', 'jigoshop' ),
				'singular_name' => __( 'Order', 'jigoshop' ),
				'add_new' => __( 'Add Order', 'jigoshop' ),
				'add_new_item' => __( 'Add New Order', 'jigoshop' ),
				'edit' => __( 'Edit', 'jigoshop' ),
				'edit_item' => __( 'Edit Order', 'jigoshop' ),
				'new_item' => __( 'New Order', 'jigoshop' ),
				'view' => __( 'View Order', 'jigoshop' ),
				'view_item' => __( 'View Order', 'jigoshop' ),
				'search_items' => __( 'Search Orders', 'jigoshop' ),
				'not_found' => __( 'No Orders found', 'jigoshop' ),
				'not_found_in_trash' => __( 'No Orders found in trash', 'jigoshop' ),
				'parent' => __( 'Parent Orders', 'jigoshop' )
			),
			'description' => __( 'This is where store orders are stored.', 'jigoshop' ),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'menu_position' => 58,
			'hierarchical' => false,
			'show_in_nav_menus' => false,
			'rewrite' => false,
			'query_var' => true,			
			'supports' => array( 'title', 'comments' ),
			'has_archive' => false
		)
	);
	
    register_taxonomy( 'shop_order_status',
        array('shop_order'),
        array(
            'hierarchical' => true,
            'update_count_callback' => '_update_post_term_count',
            'labels' => array(
                    'name' => __( 'Order statuses', 'jigoshop'),
                    'singular_name' => __( 'Order status', 'jigoshop'),
                    'search_items' =>  __( 'Search Order statuses', 'jigoshop'),
                    'all_items' => __( 'All  Order statuses', 'jigoshop'),
                    'parent_item' => __( 'Parent Order status', 'jigoshop'),
                    'parent_item_colon' => __( 'Parent Order status:', 'jigoshop'),
                    'edit_item' => __( 'Edit Order status', 'jigoshop'),
                    'update_item' => __( 'Update Order status', 'jigoshop'),
                    'add_new_item' => __( 'Add New Order status', 'jigoshop'),
                    'new_item_name' => __( 'New Order status Name', 'jigoshop')
            ),
            'show_ui' => false,
            'show_in_nav_menus' => false,
            'query_var' => true,
            'rewrite' => false,
        )
    );

    if (get_option('jigowatt_update_rewrite_rules')=='1') :
    	// Re-generate rewrite rules
    	global $wp_rewrite;
    	$wp_rewrite->flush_rules();
    	update_option('jigowatt_update_rewrite_rules', '0');
    endif;
    
} 
