<?php
use Jigoshop\Helper\Render;

/**
 * @var $product \Jigoshop\Entity\Product The product.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 */
?>

<?php //do_action('jigoshop_before_single_product', $post, $product); ?>
<article id="post-<?php echo $product->getId(); ?>" <?php post_class(); ?>>
	<h1><?php echo $product->getName(); ?></h1>
	<?php Render::output('shop/messages', array('messages' => $messages)); ?>
	<?php //do_action('jigoshop_before_single_product_summary', $post, $product); ?>
	<div class="summary">
		<?php //do_action('jigoshop_template_single_summary', $post, $product); ?>
	</div>
	<?php //do_action('jigoshop_after_single_product_summary', $post, $product); ?>
</article>
<?php //do_action('jigoshop_after_single_product', $post, $product); ?>
