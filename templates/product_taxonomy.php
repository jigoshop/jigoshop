<?php

global $taxonomy;
	  
$term_slug = get_query_var($taxonomy);
$term = get_term_by( 'slug', $term_slug, $taxonomy);

?>
	  
<div id="container"><div id="content" role="main">

	<?php if (function_exists('jigoshop_breadcrumb')) jigoshop_breadcrumb(); ?>
		
	<h1 class="page-title"><?php echo wptexturize($term->name); ?></h1>
	
	<?php echo wpautop(wptexturize($term->description)); ?>
	
	<?php jigoshop_get_template_part( 'loop', 'shop' ); ?>
	
	<?php if (  $wp_query->max_num_pages > 1 ) : ?>
	<div class="navigation">
		<div class="nav-next"><?php next_posts_link( __( 'Next <span class="meta-nav">&rarr;</span>', 'jigoshop' ) ); ?></div>
		<div class="nav-previous"><?php previous_posts_link( __( '<span class="meta-nav">&larr;</span> Previous', 'jigoshop' ) ); ?></div>
	</div><!-- #nav-below -->
	<?php endif; ?>

</div></div>