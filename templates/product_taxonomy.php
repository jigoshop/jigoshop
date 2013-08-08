<?php
/**
 * Product taxonomy template
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Catalog
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
 ?>

<?php get_header('shop'); ?>

<?php do_action('jigoshop_before_main_content'); // <div id="container"><div id="content" role="main"> ?>

	<?php $term = get_term_by( 'slug', get_query_var($wp_query->query_vars['taxonomy']), $wp_query->query_vars['taxonomy']); ?>

    <?php echo apply_filters( 'jigoshop_product_taxonomy_header', '<h1 class="page-title">' . wptexturize( $term->name ) . '</h1>' ); ?>

	<?php echo apply_filters( 'jigoshop_product_taxonomy_description', wpautop(wptexturize($term->description)) ); ?>

	<?php jigoshop_get_template_part( 'loop', 'shop' ); ?>

	<?php do_action('jigoshop_pagination'); ?>

<?php do_action('jigoshop_after_main_content'); // </div></div> ?>

<?php do_action('jigoshop_sidebar'); ?>

<?php get_footer('shop'); ?>