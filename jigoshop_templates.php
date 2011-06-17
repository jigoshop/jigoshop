<?php
### Templates ##################################################################
/*
 * Templates are in the 'templates' folder. jigoshop looks for theme 
 * overides in /theme/jigoshop/ by default  but this can be overwritten with JIGOSHOP_TEMPLATE_URL
*/
################################################################################

function jigoshop_template_loader( $template ) {
	if ( is_single() && get_post_type() == 'product' ) :
		
		$template = JIGOSHOP_TEMPLATE_URL . 'single-product.php';
		
		if (!locate_template(array( $template ), false, false)) :
			$template = jigoshop::plugin_path() . '/templates/single-product.php';
		else :
			$template = locate_template(array( $template ), false, false);
		endif;

	elseif ( is_tax('product_cat') ) :
	
		$template = JIGOSHOP_TEMPLATE_URL . 'taxonomy-product_cat.php';
		
		if (!locate_template(array( $template ), false, false)) :
			$template = jigoshop::plugin_path() . '/templates/taxonomy-product_cat.php';
		else :
			$template = locate_template(array( $template ), false, false);
		endif;

	elseif ( is_tax('product_tag') ) :
	
		$template = JIGOSHOP_TEMPLATE_URL . 'taxonomy-product_tag.php';
		
		if (!locate_template(array( $template ), false, false)) :
			$template = jigoshop::plugin_path() . '/templates/taxonomy-product_tag.php';
		else :
			$template = locate_template(array( $template ), false, false);
		endif;

	elseif ( is_post_type_archive('product') ) :
		
		$template = JIGOSHOP_TEMPLATE_URL . 'archive-product.php';
		
		if (!locate_template(array( $template ), false, false)) :
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
		if (!locate_template(array( JIGOSHOP_TEMPLATE_URL . 'loop-shop.php' ))) :
			require(jigoshop::plugin_path() . '/templates/loop-shop.php');
			return;
		endif;
	endif;
	get_template_part( JIGOSHOP_TEMPLATE_URL . $slug, $name );
}

################################################################################
// Get the reviews template (comments)
################################################################################

function jigoshop_comments_template($template) {
		
	if(get_post_type() !== 'product') return $template;
	
	if (file_exists( STYLESHEETPATH . '/' . JIGOSHOP_TEMPLATE_URL . 'product/reviews.php' ))
		return STYLESHEETPATH . '/' . JIGOSHOP_TEMPLATE_URL . 'product/reviews.php'; 
	else
		return jigoshop::plugin_path() . '/templates/product/reviews.php';
}

add_filter('comments_template', 'jigoshop_comments_template' );


################################################################################
// Get other templates (e.g. product attributes)
################################################################################

function jigoshop_get_template($template_name) {
	if (file_exists( STYLESHEETPATH . '/' . JIGOSHOP_TEMPLATE_URL . $template_name )) include( STYLESHEETPATH . '/' . JIGOSHOP_TEMPLATE_URL . $template_name ); 
	else include( jigoshop::plugin_path() . '/templates/' . $template_name );
}

################################################################################
// Get other templates (e.g. product attributes) - path
################################################################################

function jigoshop_get_template_file_url($template_name, $ssl = false) {
	if (file_exists( STYLESHEETPATH . '/' . JIGOSHOP_TEMPLATE_URL . $template_name )) 
		$return = get_bloginfo('template_url') . '/' . JIGOSHOP_TEMPLATE_URL . $template_name; 
	else 
		$return = jigoshop::plugin_url() . '/templates/' . $template_name;
	
	if (get_option('jigoshop_force_ssl_checkout')=='yes' || is_ssl()) :
		if ($ssl) $return = str_replace('http:', 'https:', $return);
	endif;
	
	return $return;
}
