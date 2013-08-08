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
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

add_action( 'load-product_page_attributes', 'jigoshop_product_attributes_help' );
function jigoshop_product_attributes_help() {
	$screen = get_current_screen();

	$types = '
		<p>'.__('Attributes can have many types which affect how they are displayed in both the admin &amp; frontend, these include', 'jigoshop').':</p>
		<ul>
			<li><strong>'.__('Select', 'jigoshop').'</strong> '.__('is used for attributes which can only have <strong>one</strong> value. Note: this type can not be used for variations', 'jigoshop').'.</li>
			<li><strong>'.__('Multiple Select', 'jigoshop').'</strong> '.__('is used in situations where a product can belong to many but not all possible attributes, for example available colours. This attribute type can be used for variations', 'jigoshop').'.</li>
			<li><strong>'.__('Text', 'jigoshop').'</strong> '.__('is used when you do not know what options there are until it comes to creating a product', 'jigoshop').'.</li>
		</ul>
		<p>'.__('In addition to all these types there are also custom attributes which are created in the product creation screen, these attributes are mainly only used for one-off attributes', 'jigoshop').'.</p>
	';

	$adding_options = '
		<p>'.__('Once youve set up your attribute with a name &amp; a type the final task is to create some options', 'jigoshop').'.</p>
		<p>'.__('To create an option click on the attribute name to the right of the screen, this should take you to a new screen. Once there simply add options the same as you would a category/tag', 'jigoshop').'.</p>
		<p>'.__('Thats really all there is to it, enjoy', 'jigoshop').'!</p>
	';

	// TODO: ADD RELEVANT ARTICLE
	$sidebar_content = '
        <p><strong>'.__('For more information', 'jigoshop').':</strong></p>
        <p><a href="http://forum.jigoshop.com/kb/" target="_blank">'.__('Documentation on', 'jigoshop').'<br/>'.__('Product Attributes', 'jigoshop').'</a></p>
        <p><a href="http://jigoshop.com/support" target="_blank">'.__('Support Forum', 'jigoshop').'</a></p>
    ';
    $screen->set_help_sidebar( $sidebar_content );

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-attribute-help-overview',
        'title'   => __('Overview', 'jigoshop'),
        'content' => '<p>'.__('Attributes let you define extra product data, such as size or colour. You can use these attributes in the shop sidebar using the "layered nav" widgets. Please note: you cannot rename an attribute later on', 'jigoshop').'.</p>',
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-attribute-help-types',
        'title'   => __('Attribute Types', 'jigoshop'),
        'content' => $types,
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-attribute-help-values',
        'title'   => __('Adding Options', 'jigoshop'),
        'content' => $adding_options,
    ));
}

add_action( 'load-edit-tags.php', 'jigoshop_product_category_help' );
function jigoshop_product_category_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'edit-product_cat' )
        return false;

    $overview = '
    	<p>'.__('You can use categories to define sections of your site and group related products', 'jigoshop').'.</p>

		<p>'.__('What’s the difference between categories and tags? Normally, tags are ad-hoc keywords that identify important information in your post (names, subjects, etc) that may or may not recur in other products, while categories are pre-determined sections. If you think of your site like a book, the categories are like the Table of Contents and the tags are like the terms in the index', 'jigoshop').'.</p>
    ';

    $product_categories = '
		<p>'.__('When adding a new category on this screen, you’ll fill in the following fields', 'jigoshop').':</p>
		<ul>
			<li><strong>'.__('Name', 'jigoshop').'</strong> - '.__('The name is how it appears on your site', 'jigoshop').'.</li>
			<li><strong>'.__('Slug', 'jigoshop').'</strong> - '.__('The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens', 'jigoshop').'.</li>
			<li><strong>'.__('Parent', 'jigoshop').'</strong> - '.__('Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional. To create a subcategory, just choose another category from the Parent dropdown', 'jigoshop').'.</li>
			<li><strong>'.__('Description', 'jigoshop').'</strong> - '.__('The description is not prominent by default; however, some themes may display it', 'jigoshop').'.</li>
		</ul>
		<p>'.__('You can change the display of this screen using the Screen Options tab to set how many items are displayed per screen and to display/hide columns in the table', 'jigoshop').'.</p>
    ';

	// *TODO: ADD RELEVANT ARTICLE*
	$sidebar_content = '
        <p><strong>'.__('For more information', 'jigoshop').':</strong></p>
        <p><a href="http://forum.jigoshop.com/kb/" target="_blank">'.__('Documentation on', 'jigoshop').'<br/>'.__('Product Categories', 'jigoshop').'</a></p>
        <p><a href="http://jigoshop.com/support" target="_blank">'.__('Support Forum', 'jigoshop').'</a></p>
    ';
    $screen->set_help_sidebar( $sidebar_content );

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-overview',
        'title'   => __('Overview', 'jigoshop'),
        'content' => $overview,
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-categories',
        'title'   => __('Adding Product Categories', 'jigoshop'),
        'content' => $product_categories,
    ));
}

add_action( 'load-edit-tags.php', 'jigoshop_product_tag_help' );
function jigoshop_product_tag_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'edit-product_tag' )
        return false;

    $overview = '
		<p>'.__('You can assign keywords to your products using <strong>tags</strong>. Unlike categories, tags have no hierarchy, meaning there’s no relationship from one tag to another', 'jigoshop').'.</p>
		<p>'.__('What’s the difference between categories and tags? Normally, tags are ad-hoc keywords that identify important information in your post (names, subjects, etc) that may or may not recur in other products, while categories are pre-determined sections. If you think of your site like a book, the categories are like the Table of Contents and the tags are like the terms in the index', 'jigoshop').'.</p>
    ';

    $tags = '
		<p>'.__('When adding a new tag on this screen, you’ll fill in the following fields', 'jigoshop').':</p>
		<ul>
			<li><strong>'.__('Name', 'jigoshop').'</strong> - '.__('The name is how it appears on your site', 'jigoshop').'.</li>
			<li><strong>'.__('Slug', 'jigoshop').'</strong> - '.__('The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens', 'jigoshop').'.</li>
			<li><strong>'.__('Description', 'jigoshop').'</strong> - '.__('The description is not prominent by default; however, some themes may display it', 'jigoshop').'.</li>
		</ul>
		<p>'.__('You can change the display of this screen using the Screen Options tab to set how many items are displayed per screen and to display/hide columns in the table', 'jigoshop').'.</p>
    ';

	// *TODO: ADD RELEVANT ARTICLE*
	$sidebar_content = '
        <p><strong>'.__('For more information', 'jigoshop').':</strong></p>
        <p><a href="http://forum.jigoshop.com/kb/" target="_blank">'.__('Documentation on', 'jigoshop').'<br/>'.__('Product Tags', 'jigoshop').'</a></p>
        <p><a href="http://jigoshop.com/support" target="_blank">'.__('Support Forum', 'jigoshop').'</a></p>
    ';
    $screen->set_help_sidebar( $sidebar_content );

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-overview',
        'title'   => __('Overview', 'jigoshop'),
        'content' => $overview,
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-tags',
        'title'   => __('Adding Product Tags', 'jigoshop'),
        'content' => $tags,
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

    $screen_content = '
		<p>'.__('You can customize the display of this screen’s contents in a number of ways', 'jigoshop').':</p>
		<ul>
			<li>'.__('You can hide/display columns based on your needs and decide how many products to list per screen using the Screen Options tab', 'jigoshop').'.</li>
			<li>'.__('You can filter the list of products by status using the text links in the upper left to show All, Published, Draft, or Trashed products. The default view is to show all products', 'jigoshop').'.</li>
			<li>'.__('You can refine the list to show only products in a specific category, from a specific month, or by a specific type by using the dropdown menus above the products list. Click the Filter button after making your selection', 'jigoshop').'.</li>
		</ul>
    ';

    $searching = '
		<p>'.__('You can search for products in a number of ways', 'jigoshop').':</p>
		<ul>
			<li><strong>'.__('ID', 'jigoshop').'</strong>: '.__('You can search for products by ID simply type ID: followed by the ID you want to search by into the search box', 'jigoshop').'.</li>
			<li><strong>'.__('SKU', 'jigoshop').'</strong>: '.__('You can search for products by SKU simply type SKU: followed by the SKU you want to search by into the search box', 'jigoshop').'.</li>
		</ul>
    ';

    $bulk = '
    	<p>'.__('You can also edit or move multiple products to the trash at once. Select the products you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply', 'jigoshop').'.</p>

		<p>'.__('When using Bulk Edit, you can change the metadata (categories, author, etc.) for all selected products at once. To remove a product from the grouping, just click the x next to its name in the Bulk Edit area that appears', 'jigoshop').'.</p>
    ';

	// *TODO: ADD RELEVANT ARTICLE*
    $sidebar_content = '
        <p><strong>'.__('For more information', 'jigoshop').':</strong></p>
        <p><a href="http://forum.jigoshop.com/kb/" target="_blank">'.__('Documentation on', 'jigoshop').'<br/>'.__('Managing Products', 'jigoshop').'</a></p>
        <p><a href="http://jigoshop.com/support" target="_blank">'.__('Support Forum', 'jigoshop').'</a></p>
    ';
    $screen->set_help_sidebar( $sidebar_content );

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-overview',
        'title'   => __('Overview', 'jigoshop'),
        'content' => '<p>'.__('This screen provides access to all of your products. You can customize the display of this screen to suit your workflow', 'jigoshop').'.</p>',
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-content',
        'title'   => __('Screen Content', 'jigoshop'),
        'content' => $screen_content,
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-search',
        'title'   => __('Searching for Products', 'jigoshop'),
        'content' => $searching,
    ));

    $screen->add_help_tab( array(
        'id'      => 'jigoshop-product-list-help-actions',
        'title'   => __('Bulk Actions', 'jigoshop'),
        'content' => $bulk,
    ));
}

// Add contextual help
add_action( 'add_meta_boxes' , 'jigoshop_product_data_help' , 10 , 2 );
function jigoshop_product_data_help ( $post_type , $post ) {
	if ( 'product' != $post_type )
		return false;

	$general = '
		<p>'.__('Hi! It looks like you\'re in need of some help, this help section has been categorized by tabs & runs through quickly what each one does. If you need an extra hand please check out the links to the right', 'jigoshop').'.</p>
		<p><strong>'.__('Product Type', 'jigoshop').'</strong> - '.__('Products are categorized into types which determine what kind of shopping experience your customers will have. Simple products are the most common type & offer the standard view. For more info on product types please consult the documentation', 'jigoshop').'.</p>
		<p><strong>'.__('Regular Price', 'jigoshop').'</strong> - '.__('This is the baseline price for your product & is what Jigoshop will always default to', 'jigoshop').'.</p>
		<p><strong>'.__('Sale Price', 'jigoshop').'</strong> - '.__('Entering a price or percentage here will place your product on sale unless it is scheduled by clicking the schedule link', 'jigoshop').'.</p>
		<p><strong>'.__('Featured', 'jigoshop').'</strong> - '.__('Featuring a product enables its display on the featured products shortcode & widget', 'jigoshop').'.</p>
	';

	$advanced = '
		<p><strong>'.__('Tax Status', 'jigoshop').'</strong> - '.__('Switches where taxation rules are applied to the product. Selecting Shipping will only apply tax to the shipping cost of the product', 'jigoshop').'.</p>
		<p><strong>'.__('Tax Classes', 'jigoshop').'</strong> - '.__('Choose what defined tax classes apply to this product. By default Standard rate taxation is selected', 'jigoshop').'.</p>
		<p><strong>'.__('Visibility', 'jigoshop').'</strong> - '.__('Determines where the product is visible. <strong>Catalog only</strong> hides the product from search results, on the other hand <strong>Search only</strong> hides the product from the shops catalog. <strong>Hidden</strong> hides the product completely whereas <strong>Catalog & Search</strong> enables the product in all areas', 'jigoshop').'.</p>
	';

	$inventory = '
		<p><strong>'.__('Manage Stock', 'jigoshop').'</strong> - '.__('Enabling this will allow Jigoshop to automatically decrease stock & warn you when supplies are low on the dashboard page', 'jigoshop').'.</p>
		<p><strong>'.__('Stock Status', 'jigoshop').'</strong> - '.__('Manually switch the stock status of the product between In Stock & Out of Stock', 'jigoshop').'.</p>
		<p><strong>'.__('Stock Quantity', 'jigoshop').'</strong> - '.__('Set the initial stock quantity for Jigoshop stock management. This can be adjusted when new shipments arrive & stock levels increase', 'jigoshop').'.</p>
		<p><strong>'.__('Allow Backorders', 'jigoshop').'</strong> - '.__('Sometimes you may want to sell past your stock levels, allowing backorders enables this. Notification to the customer can also be set which displays a message on the catalog screen when stocks are low', 'jigoshop').'.</p>
	';

	$attributes = '
		<p>'.__('Attributes define various characteristics of your product, these attributes can then be used to filter & describe your product. They are first configured in the Attributes screen, then added to products in the attributes tab of the product data panel. Attributes can be added by first selecting the attribute to be added and then clicking the Add Attribute button. Attributes can be ordered by dragging & dropping the attributes', 'jigoshop').'.</p>
		<p><strong>'.__('Display on product page', 'jigoshop').'</strong> - '.__('You may only want to use attributes for filtering or variations. Enabling this will display the attribute & its values in the Additional Information tab of the product view', 'jigoshop').'.</p>
		<p><strong>'.__('Is for variations', 'jigoshop').'</strong> - '.__('Marks the attribute for variation. You must first mark your attributes for variation before adding any variations', 'jigoshop').'.</p>
	';

	$group = '
		<p><strong>'.__('Product Group', 'jigoshop').'</strong> - '.__('Specify the Grouped product to attach this product to. Before you can attach a product you must first create the grouped product', 'jigoshop').'.</p>
		<p><strong>'.__('Sort Order', 'jigoshop').'</strong> - '.__('Specify the order in which these products appear in the grouping. Similar to post order for WordPress Posts', 'jigoshop').'.</p>
		<p><strong>'.__('File URL', 'jigoshop').'</strong> - '.__('Specify the location of your downloadable asset. The file can be either stored locally & accessed using the Media Uploader or externally', 'jigoshop').'.</p>
		<p><strong>'.__('Download Limit', 'jigoshop').'</strong> - '.__('Restricts the number of redownloads a customer can use on that product. Once the limit is up they must re purchase the file', 'jigoshop').'.</p>
	';

	$variations = '
		<p>'.__('Variations are a very powerful aspect of Jigoshop, they allow customers to pick a specific variant of the product. For example a Shirt could come in sizes Small, Medium & Large each with varying stocks & pricing', 'jigoshop').'.</p>
		<p>'.__('Variations currently come in 3 different types, Simple, Downloadable & Virtual. These types behave much the same as their main product counter parts which enables you to create powerful combinations. For example when selling a book what format it arrives in (Printed or e-Book)', 'jigoshop').'.</p>
		<p>'.__('To create variations you must first add & save your attributes for variation. Once this has been done you can then add & configure as many variations as there are combinations', 'jigoshop').'.</p>
		<p><strong>'.__('For more information', 'jigoshop').'</strong> <a href="http://forum.jigoshop.com/kb/creating-products/variable-products">'.__('click here to learn more about variable products', 'jigoshop').'</a>.</p>
	';

	$sidebar_content = '
		<p><strong>'.__('For more information', 'jigoshop').':</strong></p>
		<p><a href="http://forum.jigoshop.com/kb/creating-products/" target="_blank">'.__('Documentation on', 'jigoshop').'<br/>'.__('Creating Products', 'jigoshop').'</a></p>
		<p><a href="http://jigoshop.com/support" target="_blank">'.__('Support Forum', 'jigoshop').'</a></p>
	';

	$screen = get_current_screen();

	$screen->set_help_sidebar( $sidebar_content );

	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-general',
		'title'   => __('General Settings', 'jigoshop'),
		'content' => $general,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-advanced',
		'title'   => __('Advanced Settings', 'jigoshop'),
		'content' => $advanced,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-inventory',
		'title'   => __('Inventory Management', 'jigoshop'),
		'content' => $inventory,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-attributes',
		'title'   => __('Attributes', 'jigoshop'),
		'content' => $attributes,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-group',
		'title'   => __('Group & File', 'jigoshop'),
		'content' => $group,
	));
	$screen->add_help_tab( array(
		'id'      => 'jigoshop-product-data-help-variations',
		'title'   => __('Variations', 'jigoshop'),
		'content' => $variations,
	));
}