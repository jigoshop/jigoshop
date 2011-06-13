<?php global $_product; ?>

<?php
	$related = $_product->get_related();
	if (sizeof($related)>0) :
		echo '<div class="related products"><h2>'.__('Related Products', 'jigoshop').'</h2>';
		$args = array(
			'post_type'	=> 'product',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page' => 4,
			'orderby' => 'rand',
			'post__in' => $related
		);
		query_posts($args);
		jigoshop_get_template_part( 'loop', 'shop' ); 
		echo '</div>';
	endif;
	wp_reset_query();
?>