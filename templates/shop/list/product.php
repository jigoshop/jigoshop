<?php
/**
 * @var $product \Jigoshop\Entity\Product Product to display.
 */
?>
<li class="product">
	<?php do_action('jigoshop\shop\list\product\before', $product); ?>
	<a href="<?php echo $product->getLink(); ?>">
		<?php do_action('jigoshop\shop\list\product\before_title', $product); ?>
		<strong><?php echo $product->getName(); ?></strong>
		<?php do_action('jigoshop\shop\list\product\after_title', $product); ?>
	</a>
	<?php do_action('jigoshop\shop\list\product\after', $product); ?>
</li>