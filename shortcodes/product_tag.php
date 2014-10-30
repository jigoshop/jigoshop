<?php
function jigoshop_product_tag( $attributes ) {

	global $paged;
	$jigoshop_options = Jigoshop_Base::get_options();
	$attributes = shortcode_atts( array(
		'tag'		=> '',
		'per_page'	=> $jigoshop_options->get('jigoshop_catalog_per_page'),
		'columns'	=> $jigoshop_options->get('jigoshop_catalog_columns'),
		'orderby'	=> $jigoshop_options->get('jigoshop_catalog_sort_orderby'),
		'order'		=> $jigoshop_options->get('jigoshop_catalog_sort_direction'),
		'pagination'	=> false,
		'tax_operator'	=> 'IN'
	), $attributes);

	if(isset($_REQUEST['tag'])){
		$attributes['tag'] = $_REQUEST['tag'];
	}

	/** Operator validation. */
	if( !in_array( $attributes['tax_operator'], array( 'IN', 'NOT IN', 'AND' ) ) )
		$tax_operator = 'IN';

	/** Multiple category values. */
	if ( !empty($slug) ) {
		$slug = explode( ',', esc_attr( $slug ) );
		$slug = array_map('trim', $slug);
	}

	$args = array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => 1,
		'posts_per_page'         => $attributes['per_page'],
		'orderby'                => $attributes['orderby'],
		'order'                  => $attributes['order'],
		'paged'                  => $paged,
		'meta_query'             => array(
			array(
				'key'       => 'visibility',
				'value'     => array( 'catalog', 'visible' ),
				'compare'   => 'IN'
			)
		),
		'tax_query' => array(
			array(
				'taxonomy'    => 'product_tag',
				'field'       => 'slug',
				'terms'       => $attributes['tag'],
				'operator'    => $attributes['tax_operator']
			)
		)
	);

	query_posts( $args );
	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	if($attributes['pagination']) do_action('jigoshop_pagination');
	wp_reset_query();

	return ob_get_clean();
}
