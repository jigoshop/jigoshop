<?php
/**
 * Product template
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

	<?php if ( have_posts() ) while ( have_posts() ) : the_post(); global $_product; $_product = new jigoshop_product( $post->ID ); ?>

		<?php do_action('jigoshop_before_single_product', $post, $_product); ?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<?php do_action('jigoshop_before_single_product_summary', $post, $_product); ?>

			<div class="summary">

				<?php do_action( 'jigoshop_template_single_summary', $post, $_product ); ?>

			</div>

			<?php do_action('jigoshop_after_single_product_summary', $post, $_product); ?>

		</div>

		<?php do_action('jigoshop_after_single_product', $post, $_product); ?>

	<?php endwhile; ?>

<?php do_action('jigoshop_after_main_content'); // </div></div> ?>

<?php do_action('jigoshop_sidebar'); ?>

<?php get_footer('shop'); ?>