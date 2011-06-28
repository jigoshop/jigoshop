<?php
/**
 * ACTIONS USED IN TEMPLATE FILES
 *
 **/

/**
 * Content Wrappers
 **/
add_action( 'jigoshop_before_main_content', 'jigoshop_output_content_wrapper', 10);
add_action( 'jigoshop_after_main_content', 'jigoshop_output_content_wrapper_end', 10);

function jigoshop_output_content_wrapper() {	echo '<div id="container"><div id="content" role="main">';	}
function jigoshop_output_content_wrapper_end() {	echo '</div></div>';	}


/**
 * Shop Messages
 **/
add_action( 'jigoshop_before_single_product', 'jigoshop::show_messages', 10);
add_action( 'jigoshop_before_shop_loop', 'jigoshop::show_messages', 10);


/**
 * Sale flashes
 **/
add_action( 'jigoshop_before_shop_loop_item_title', 'jigoshop_show_product_sale_flash', 10, 2);
add_action( 'jigoshop_before_single_product_summary', 'jigoshop_show_product_sale_flash', 10, 2);

function jigoshop_show_product_sale_flash( $post, $_product ) {
	if ($_product->is_on_sale()) echo '<span class="onsale">'.__('Sale!', 'jigoshop').'</span>';
}

/**
 * Breadcrumbs
 **/
add_action( 'jigoshop_before_main_content', 'jigoshop_output_breadcrumb', 20);

function jigoshop_output_breadcrumb() {		jigoshop_breadcrumb();	}


/**
 * Sidebar
 **/
add_action( 'jigoshop_sidebar', 'jigoshop_get_sidebar', 10);

function jigoshop_get_sidebar() {		get_sidebar('shop');	}

/**
 * Products Loop
 **/
add_action( 'jigoshop_after_shop_loop_item', 'jigoshop_template_loop_add_to_cart', 10, 2);
add_action( 'jigoshop_before_shop_loop_item_title', 'jigoshop_template_loop_product_thumbnail', 10, 2);
add_action( 'jigoshop_after_shop_loop_item_title', 'jigoshop_template_loop_price', 10, 2);

function jigoshop_template_loop_add_to_cart( $post, $_product ) {		
	?><a href="<?php echo $_product->add_to_cart_url(); ?>" class="button"><?php _e('Add to cart', 'jigoshop'); ?></a><?php
}
function jigoshop_template_loop_product_thumbnail( $post, $_product ) {
	echo jigoshop_get_product_thumbnail();
}
function jigoshop_template_loop_price( $post, $_product ) {
	?><span class="price"><?php echo $_product->get_price_html(); ?></span><?php
}


/**
 * Before Single Products
 **/
add_action( 'jigoshop_before_single_product', 'jigoshop_check_product_visibility', 10, 2);

function jigoshop_check_product_visibility( $post, $_product ) {
	if (!$_product->is_visible() && $post->post_parent > 0) : wp_safe_redirect(get_permalink($post->post_parent)); exit; endif;
	if (!$_product->is_visible()) : wp_safe_redirect(home_url()); exit; endif;
}


/**
 * Before Single Products Summary Div
 **/
add_action( 'jigoshop_before_single_product_summary', 'jigoshop_show_product_images', 20);

function jigoshop_show_product_images() {
	jigoshop_get_template( 'product/images.php' );
}


/**
 * After Single Products Summary Div
 **/
add_action( 'jigoshop_after_single_product_summary', 'jigoshop_output_product_data_tabs', 10);
add_action( 'jigoshop_after_single_product_summary', 'jigoshop_output_related_products', 20);

function jigoshop_output_product_data_tabs() {
	jigoshop_get_template( 'product/tabs.php' );
}
function jigoshop_output_related_products() {
	jigoshop_get_template( 'product/related.php' );
}


/**
 * Product Summary Box
 **/
add_action( 'jigoshop_template_single_summary', 'jigoshop_template_single_price', 10, 2);
add_action( 'jigoshop_template_single_summary', 'jigoshop_template_single_excerpt', 20, 2);
add_action( 'jigoshop_template_single_summary', 'jigoshop_template_single_add_to_cart', 30, 2);
add_action( 'jigoshop_template_single_summary', 'jigoshop_template_single_meta', 40, 2);
add_action( 'jigoshop_template_single_summary', 'jigoshop_template_single_sharing', 50, 2);

function jigoshop_template_single_price( $post, $_product ) {
	?><p class="price"><?php echo $_product->get_price_html(); ?></p><?php
}

function jigoshop_template_single_excerpt( $post, $_product ) {
	if ($post->post_excerpt) echo wpautop(wptexturize($post->post_excerpt));
}

function jigoshop_template_single_add_to_cart( $post, $_product ) {
	
	if ( $_product->is_type('simple') ) jigoshop_get_template( 'product/simple/add-to-cart.php' );
	elseif ( $_product->is_type('downloadable') ) jigoshop_get_template( 'product/downloadable/add-to-cart.php' );
	elseif ( $_product->is_type('grouped') ) jigoshop_get_template( 'product/grouped/add-to-cart.php' );
	elseif ( $_product->is_type('virtual') ) jigoshop_get_template( 'product/virtual/add-to-cart.php' );

}

function jigoshop_template_single_meta( $post, $_product ) {
	
	?>
	<div class="product_meta"><?php if ($_product->is_type('simple')) : ?><span class="sku">SKU: <?php echo $_product->sku; ?>.</span> <?php endif; ?><?php echo $_product->get_categories( ', ', 'Posted in ', '.'); ?> <?php echo $_product->get_tags( ', ', 'Tagged as ', '.'); ?></div>
	<?php
	
}

function jigoshop_template_single_sharing( $post, $_product ) {
	
	if (get_option('jigoshop_sharethis')) :
		echo '<div class="social">
			<iframe src="https://www.facebook.com/plugins/like.php?href='.urlencode(get_permalink($post->ID)).'&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;" allowTransparency="true"></iframe>
			<span class="st_email"></span><span class="st_twitter"></span><span class="st_sharethis"></span>
		</div>';
	endif;
	
}


/**
 * Pagination in loop-shop
 **/
add_action( 'jigoshop_after_shop_loop', 'jigoshop_loop_display_pagination', 10);

function jigoshop_loop_display_pagination() {
	
	global $wp_query;
	
	if (  $wp_query->max_num_pages > 1 ) : 
		?>
		<div class="navigation">
			<div class="nav-next"><?php next_posts_link( __( 'Next <span class="meta-nav">&rarr;</span>', 'jigoshop' ) ); ?></div>
			<div class="nav-previous"><?php previous_posts_link( __( '<span class="meta-nav">&larr;</span> Previous', 'jigoshop' ) ); ?></div>
		</div>
		<?php 
	endif;
	
}

