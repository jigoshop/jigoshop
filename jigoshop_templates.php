<?php
/**
 * Templates are in the 'templates' folder. jigoshop looks for theme
 *
 * Overides in /theme/jigoshop/ by default, but can be overwritten with JIGOSHOP_TEMPLATE_URL
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

function jigoshop_template_loader( $template ) {

	if ( is_single() && get_post_type() == 'product' ) {

		jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-product' ) );

		$template = locate_template( array( 'single-product.php', JIGOSHOP_TEMPLATE_URL . 'single-product.php' ) );

		if ( ! $template ) $template = jigoshop::plugin_path() . '/templates/single-product.php';

	}
	elseif ( is_tax('product_cat') ) {

		jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-products', 'jigoshop-product_cat' ) );

		global $posts;
		$templates = array();
		if ( count( $posts ) ) {
			$category = get_the_terms( $posts[0]->ID, 'product_cat' );
			$slug = $category[key($category)]->slug;
 			$templates[] = 'taxonomy-product_cat-' . $slug . '.php';
 			$templates[] = JIGOSHOP_TEMPLATE_URL . 'taxonomy-product_cat-' . $slug . '.php';
		}
 		$templates[] = 'taxonomy-product_cat.php';
 		$templates[] = JIGOSHOP_TEMPLATE_URL . 'taxonomy-product_cat.php';

		$template = locate_template( $templates );

		if ( ! $template ) $template = jigoshop::plugin_path() . '/templates/taxonomy-product_cat.php';
	}
	elseif ( is_tax('product_tag') ) {

		jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-products', 'jigoshop-product_tag' ) );

		global $posts;
		$templates = array();
		if ( count( $posts ) ) {
			$tag = get_the_terms( $posts[0]->ID, 'product_tag' );
			$slug = $tag[key($tag)]->slug;
 			$templates[] = 'taxonomy-product_tag-' . $slug . '.php';
 			$templates[] = JIGOSHOP_TEMPLATE_URL . 'taxonomy-product_tag-' . $slug . '.php';
		}
 		$templates[] = 'taxonomy-product_tag.php';
 		$templates[] = JIGOSHOP_TEMPLATE_URL . 'taxonomy-product_tag.php';

		$template = locate_template( $templates );

		if ( ! $template ) $template = jigoshop::plugin_path() . '/templates/taxonomy-product_tag.php';
	}
	elseif ( is_post_type_archive('product') ||  is_page( jigoshop_get_page_id('shop') )) {

		jigoshop_add_body_class( array( 'jigoshop', 'jigoshop-shop', 'jigoshop-products' ) );

		$template = locate_template( array( 'archive-product.php', JIGOSHOP_TEMPLATE_URL . 'archive-product.php' ) );

		if ( ! $template ) $template = jigoshop::plugin_path() . '/templates/archive-product.php';

	}

	return $template;

}
add_filter( 'template_include', 'jigoshop_template_loader' );

//################################################################################
// Get template part (for templates like loop)
//################################################################################

function jigoshop_get_template_part( $slug, $name = '' ) {
	if ($name=='shop') :
		if (!locate_template(array( 'loop-shop.php', JIGOSHOP_TEMPLATE_URL . 'loop-shop.php' ))) :
			load_template( jigoshop::plugin_path() . '/templates/loop-shop.php',false );
			return;
		endif;
	endif;
	if ($name=='on_sale') :
		if (!locate_template(array( 'loop-on_sale.php', JIGOSHOP_TEMPLATE_URL . 'loop-on_sale.php' ))) :
			load_template( jigoshop::plugin_path() . '/templates/loop-on_sale.php',false );
			return;
		endif;
	endif;
	get_template_part( JIGOSHOP_TEMPLATE_URL . $slug, $name );
}

//################################################################################
// Returns the template to be used ( child-theme or theme or plugin )
//################################################################################

function jigoshop_return_template( $template_name ) {
	$template = locate_template( array( $template_name, JIGOSHOP_TEMPLATE_URL . $template_name ), false );
	if ( !$template)
		$template = jigoshop::plugin_path() . '/templates/' . $template_name ;
	return $template;
}

//################################################################################
// Get the reviews template (comments)
//################################################################################

function jigoshop_comments_template( $template ) {
	if ( get_post_type() !== 'product' ) return $template;
	return jigoshop_return_template( 'single-product-reviews.php' ) ;
}
add_filter( 'comments_template', 'jigoshop_comments_template' );


//################################################################################
// Get other templates (e.g. product attributes)
//################################################################################

function jigoshop_get_template( $template_name, $require_once = true ) {
	load_template( jigoshop_return_template( $template_name ), $require_once );
}

