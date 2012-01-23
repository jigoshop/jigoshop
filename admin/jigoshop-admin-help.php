<?php
/**
 * Functions used for adding help tabs to all jigoshop settings
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package    Jigoshop
 * @category   Admin
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */

add_action( 'load-product_page_attributes', 'jigoshop_product_attributes_help' );
function jigoshop_product_attributes_help() {
	$screen = get_current_screen();

	$sidebar_content = '
        <p><strong>'. __('For more information') . ':</strong></p>
        <p><a href="http://forum.jigoshop.com/kb/" target="_blank">Documentation on<br/>Product Attributes *TODO: ADD RELEVANT ARTICLE*</a></p>
        <p><a href="http://jigoshop.com/support" target="_blank">Support Forum</a></p>
    ';
    $screen->set_help_sidebar( $sidebar_content );

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-attribute-help-overview',
        'title'   => __( 'Overview' ),
        'content' => '<p>'.__('Attributes let you define extra product data, such as size or colour. You can use these attributes in the shop sidebar using the "layered nav" widgets. Please note: you cannot rename an attribute later on.','jigoshop').'.</p>',
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-attribute-help-types',
        'title'   => __( 'Attribute Types' ),
        'content' => '',
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-attribute-help-values',
        'title'   => __( 'Adding Options' ),
        'content' => '',
    ));
}

add_action( 'load-edit-tags.php', 'jigoshop_product_category_help' );
function jigoshop_product_category_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'edit-product_cat' )
        return false;

	$sidebar_content = '
        <p><strong>'. __('For more information') . ':</strong></p>
        <p><a href="http://forum.jigoshop.com/kb/" target="_blank">Documentation on<br/>Product Categories *TODO: ADD RELEVANT ARTICLE*</a></p>
        <p><a href="http://jigoshop.com/support" target="_blank">Support Forum</a></p>
    ';
    $screen->set_help_sidebar( $sidebar_content );

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-overview',
        'title'   => __( 'Overview' ),
        'content' => '<p>This screen provides access to all of your products. You can customize the display of this screen to suit your workflow.</p>',
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-categories',
        'title'   => __( 'Adding Product Categories' ),
        'content' => '',
    ));
}

add_action( 'load-edit-tags.php', 'jigoshop_product_tag_help' );
function jigoshop_product_tag_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'edit-product_tag' )
        return false;

	$sidebar_content = '
        <p><strong>'. __('For more information') . ':</strong></p>
        <p><a href="http://forum.jigoshop.com/kb/" target="_blank">Documentation on<br/>Product Tags *TODO: ADD RELEVANT ARTICLE*</a></p>
        <p><a href="http://jigoshop.com/support" target="_blank">Support Forum</a></p>
    ';
    $screen->set_help_sidebar( $sidebar_content );

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-overview',
        'title'   => __( 'Overview' ),
        'content' => '<p>This screen provides access to all of your products. You can customize the display of this screen to suit your workflow.</p>',
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-tags',
        'title'   => __( 'Adding Product Tags' ),
        'content' => '',
    ));
}

/**
 * Product Listing
 */
add_action( 'load-edit.php' , 'jigoshop_product_list_help' );
function jigoshop_product_list_help() {
    $screen = get_current_screen();

    if ( $screen->id != 'edit-product' )
        return false;

    $sidebar_content = '
        <p><strong>'. __('For more information') . ':</strong></p>
        <p><a href="http://forum.jigoshop.com/kb/" target="_blank">Documentation on<br/>Managing Products *TODO: ADD RELEVANT ARTICLE*</a></p>
        <p><a href="http://jigoshop.com/support" target="_blank">Support Forum</a></p>
    ';
    $screen->set_help_sidebar( $sidebar_content );

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-overview',
        'title'   => __( 'Overview' ),
        'content' => '<p>This screen provides access to all of your products. You can customize the display of this screen to suit your workflow.</p>',
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-content',
        'title'   => __( 'Screen Content' ),
        'content' => '',
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-search',
        'title'   => __( 'Searching for Products' ),
        'content' => '',
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-actions',
        'title'   => __( 'Bulk Actions' ),
        'content' => '',
    ));
}

// Add contextual help
add_action( 'add_meta_boxes' , 'jigoshop_product_data_help' , 10 , 2 );
function jigoshop_product_data_help ( $post_type , $post ) {
	if ( 'product' != $post_type )
		return false;
	
	$general = '
		<p>Hi! It looks like your in need of some help, this help section has been categorized by tabs & runs through quickly what each one does. If you need an extra hand please check out the links to the right</p>
		<p><strong>Product Type</strong> - Products are categorized into types which determine what kind of shopping experience your customers will have. Simple products are the most common type & offer the standard view. For more info on product types please consult the documentation</p>
		<p><strong>Regular Price</strong> - This is the baseline price for your product & is what Jigoshop will always default to</p>
		<p><strong>Sale Price</strong> - Entering a price here will place your product on sale unless it is scheduled by clicking the schedule link</p>
		<p><strong>Featured</strong> - Featuring a product enables its display on the featured products shortcode & widget</p>
	';

	$advanced = '
		<p><strong>Tax Status</strong> - Switches where taxation rules are applied to the product. Selecting Shipping will only apply tax to the shipping cost of the product</p>
		<p><strong>Tax Classes</strong> - Choose what defined tax classes apply to this product. By default Standard rate taxation is selected.</p>
		<p><strong>Visibilty</strong> - Determines where the product is visible. Catalog only hides the product from search results, converesely Search only hides the product from the shops catalog. Hidden hides the product completely whereas Catalog & Search enables the product in all areas</p>
	';

	$inventory = '
		<p><strong>Manage Stock</strong> - Enabling this will allow Jigoshop to automatically decrease stock & warn you when supplies are low on the dashboard page.
		<p><strong>Stock Status</strong> - Manually switch the stock status of the product between In Stock & Out of Stock</p>
		<p><strong>Stock Quantity</strong> - Set the initial stock quantity for Jigoshop stock management. This can be adjusted when new shipments arrive & stock levels increase.</p>
		<p><strong>Allow Backorders</strong> - Sometimes you may want to sell past your stock levels, allowing backorders enables this. Notification to the customer can also be set which displays a message on the catalog screen when stocks are low</p>
	';

	$attributes = '
		<p>Attributes define various characteristics of your product, these attributes can then be used to filter & describe your product. They are first configured in the Attributes screen, then added to products in the attributes tab of the product data panel. Attributes can be added by first selecting the attribute to be added and then clicking the Add Attribute button. Attributes can be ordered by dragging & dropping the attributes.</p>
		<p><strong>Display on product page</strong> - You may only want to use attributes for filtering or variations. Enabling this will display the attribute & its values in the Additional Information tab of the product view.</p>
		<p><strong>Is for variations</strong> - Marks the attribute for variation. You must first mark your attributes for variation before adding any variations.</p>
	';

	$group = '
		<p><strong>Product Group</strong> - Specify the Grouped product to attach this product to. Before you can attach a product you must first create the grouped product</p>
		<p><strong>Sort Order</strong> - Specify the order in which these products appear in the grouping. Similar to post order for WordPress Posts</p>
		<p><strong>File URL</strong> - Specify the location of your downloadable asset. The file can be either stored locally & accessed using the Media Uploader or externally</p>
		<p><strong>Download Limit</strong> - Restricts the number of redownloads a customer can use on that product. Once the limit is up they must re purchase the file.</p>
	';

	$variations = '
		<p>Variations are a very powerful aspect of Jigoshop, they allow customers to pick a specific variant of the product. For example a Shirt could come in sizes Small, Medium & Large each with varying stocks & pricing.</p>
		<p>Variations currently come in 3 different types, Simple, Downloadable & Virtual. These types behave much the same as their main product counter parts which enables you to create powerful combinations. For example when selling a book what format it arrives in (Printed or e-Book)</p>
		<p>To create variations you must first add & save your attributes for variation. Once this has been done you can then add & configure as many variations as there are combinations.</p>
		<p><strong>For more information</strong> <a href="http://forum.jigoshop.com/kb/creating-products/variable-products">click here to learn more about variable products</a></p>
	';

	$sidebar_content = '
		<p><strong>'. __('For more information') . ':</strong></p>
		<p><a href="http://forum.jigoshop.com/kb/creating-products/" target="_blank">Documentation on<br/>Creating Products</a></p>
		<p><a href="http://jigoshop.com/support" target="_blank">Support Forum</a></p>
	';

	$screen = get_current_screen();

	$screen->set_help_sidebar( $sidebar_content );

	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-general',
		'title'   => __( 'General Settings' ),
		'content' => $general,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-advanced',
		'title'   => __( 'Advanced Settings' ),
		'content' => $advanced,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-inventory',
		'title'   => __( 'Inventory Management' ),
		'content' => $inventory,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-attributes',
		'title'   => __( 'Attributes' ),
		'content' => $attributes,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-group',
		'title'   => __( 'Group & File' ),
		'content' => $group,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-variations',
		'title'   => __( 'Variations' ),
		'content' => $variations,
	));
}