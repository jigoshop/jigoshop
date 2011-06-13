<?php 

get_header();

do_action('before_shop');

?>
	  
<div id="container"><div id="content" role="main">

	<?php if (function_exists('jigoshop_breadcrumb')) jigoshop_breadcrumb(); ?>
	
	<?php if (is_search()) : ?>		
		<h1 class="page-title"><?php _e('Search Results:', 'jigoshop'); ?> &ldquo;<?php the_search_query(); ?>&rdquo; <?php if (get_query_var('paged')) echo ' &mdash; Page '.get_query_var('paged'); ?></h1>
	<?php else : ?>
		<h1 class="page-title"><?php _e('All Products', 'jigoshop'); ?></h1>
	<?php endif; ?>

	<?php jigoshop_get_template_part( 'loop', 'shop' ); ?>

	<?php if (  $wp_query->max_num_pages > 1 ) : ?>
	<div class="navigation">
		<div class="nav-next"><?php next_posts_link( __( 'Next <span class="meta-nav">&rarr;</span>', 'jigoshop' ) ); ?></div>
		<div class="nav-previous"><?php previous_posts_link( __( '<span class="meta-nav">&larr;</span> Previous', 'jigoshop' ) ); ?></div>
	</div><!-- #nav-below -->
	<?php endif; ?>

</div></div>

<?php

get_sidebar('shop');

get_footer();