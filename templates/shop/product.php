<?php
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $product \Jigoshop\Entity\Product The product.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 */
?>

<?php do_action('jigoshop\product\before', $product); ?>
<article id="post-<?php echo $product->getId(); ?>" <?php post_class(); ?>>
	<h1><?php echo $product->getName(); ?></h1>
	<?php Render::output('shop/messages', array('messages' => $messages)); ?>
	<?php do_action('jigoshop\product\before_summary', $product); ?>
	<div class="summary">
		<p class="price"><?php echo Product::getPrice($product); ?></p>
		<?php echo $product->getDescription(); ?>
		<dl class="dl-horizontal">
			<?php if($product->getSku()): ?>
			<dt><?php echo __('SKU', 'jigoshop'); ?></dt><dd><?php echo $product->getSku(); ?></dd>
			<?php endif; ?>
<!--			<dt>--><?php //echo __('Categories', 'jigoshop'); ?><!--</dt><dd>--><?php //echo join(', ', $product->getCategories()); ?><!--</dd>-->
<!--			<dt>--><?php //echo __('Tagged as', 'jigoshop'); ?><!--</dt><dd>--><?php //echo join(', ', $product->getTags()); ?><!--</dd>-->
			<?php do_action('jigoshop\product\data', $product); ?>
		</dl>
		<?php do_action('jigoshop\product\summary', $product); ?>
	</div>
	<?php do_action('jigoshop\product\after_summary', $product); ?>
</article>
<?php do_action('jigoshop\product\after', $product); ?>
