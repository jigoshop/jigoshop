<?php
### Templates ##################################################################
/*
 * Templates are in the 'templates' folder. jigoshop looks for theme 
 * overides in /theme/jigoshop/
*/
################################################################################

function jigoshop_template_loader( $template ) {
	if ( is_single() && get_post_type() == 'product' ) :
		
		$template = 'jigoshop/single-product.php';
		
		if (!locate_template(array( $template ), false, false)) :
			$template = jigoshop::plugin_path() . '/templates/single-product.php';
		else :
			$template = locate_template(array( $template ), false, false);
		endif;

	elseif ( is_tax('product_cat') ) :
	
		$template = 'jigoshop/taxonomy-product_cat.php';
		
		if (!locate_template(array('jigoshop/taxonomy-product_cat.php'), false, false)) :
			$template = jigoshop::plugin_path() . '/templates/taxonomy-product_cat.php';
		else :
			$template = locate_template(array( $template ), false, false);
		endif;

	elseif ( is_tax('product_tag') ) :
	
		$template = 'jigoshop/taxonomy-product_tag.php';
		
		if (!locate_template(array('jigoshop/taxonomy-product_tag.php'), false, false)) :
			$template = jigoshop::plugin_path() . '/templates/taxonomy-product_tag.php';
		else :
			$template = locate_template(array( $template ), false, false);
		endif;

	elseif ( is_post_type_archive('product') ) :
		
		$template = 'jigoshop/archive-product.php';
		
		if (!locate_template(array('jigoshop/archive-product.php'), false, false)) :
			$template = jigoshop::plugin_path() . '/templates/archive-product.php';
		else :
			$template = locate_template(array( $template ), false, false);
		endif;

	endif;
	return $template;
}
add_filter('template_include','jigoshop_template_loader');

################################################################################
// Get template part (for templates like loop)
################################################################################

function jigoshop_get_template_part( $slug, $name = '' ) {
	if ($name=='shop') :
		if (!locate_template(array('jigoshop/loop-shop.php'))) :
			require(jigoshop::plugin_path() . '/templates/loop-shop.php');
			return;
		endif;
	endif;
	get_template_part( 'jigoshop/' . $slug, $name );
}

################################################################################
// Get the reviews template (comments)
################################################################################

function jigoshop_comments_template() {
	if (file_exists( TEMPLATEPATH . '/jigoshop/product/reviews.php' ))
		comments_template( '/jigoshop/product/reviews.php' ); 
	else
		comments_template( '/../../plugins/jigoshop/templates/product/reviews.php' );
}

################################################################################
// Get other templates (e.g. product attributes)
################################################################################

function jigoshop_get_template($template_name) {
	if (file_exists( TEMPLATEPATH . '/jigoshop/' . $template_name )) include( TEMPLATEPATH . '/jigoshop/' . $template_name ); 
	else include( jigoshop::plugin_path() . '/templates/' . $template_name );
}

################################################################################
// Get other templates (e.g. product attributes) - path
################################################################################

function jigoshop_get_template_file_url($template_name, $ssl = false) {
	if (file_exists( TEMPLATEPATH . '/jigoshop/' . $template_name )) 
		$return = get_bloginfo('template_url') . '/jigoshop/' . $template_name; 
	else 
		$return = jigoshop::plugin_url() . '/templates/' . $template_name;
	
	if (get_option('jigoshop_force_ssl_checkout')=='yes' || is_ssl()) :
		if ($ssl) $return = str_replace('http:', 'https:', $return);
	endif;
	
	return $return;
}
