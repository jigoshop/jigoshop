<?php
/**
 * Archive template
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
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
?>

<?php get_header('shop'); ?>

<?php do_action('jigoshop_before_main_content'); ?>

	<?php if (is_search()) : ?>
		<h1 class="page-title"><?php _e('Search Results:', 'jigoshop'); ?> &ldquo;<?php the_search_query(); ?>&rdquo; <?php if (get_query_var('paged')) echo ' &mdash; Page '.get_query_var('paged'); ?></h1>
	<?php else : ?>
		<?php echo apply_filters( 'jigoshop_products_list_title', '<h1 class="page-title">' . __( 'All Products', 'jigoshop' ) . '</h1>' ); ?>
	<?php endif; ?>

	<?php
		$shop_page_id = jigoshop_get_page_id('shop');
		$shop_page = get_post($shop_page_id);
		if(post_password_required($shop_page)):
			echo get_the_password_form($shop_page);
		else:
			echo apply_filters('the_content', $shop_page->post_content);
	?>

	<?php
	ob_start();
	jigoshop_get_template_part( 'loop', 'shop' );
	$products_list_html = ob_get_clean();
	echo apply_filters( 'jigoshop_products_list', $products_list_html );
	?>

	<?php do_action('jigoshop_pagination'); ?>
<?php endif; ?>

<?php do_action('jigoshop_after_main_content'); ?>

<?php do_action('jigoshop_sidebar'); ?>
<?php do_action('jigoshop_after_sidebar'); ?>

<?php get_footer('shop'); ?>
