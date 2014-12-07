<?php
/**
 * @var $product \Jigoshop\Entity\Product\Downloadable Product to add.
 */
?>
<form action="" method="post" class="form-inline cart" role="form">
	<input type="hidden" name="action" value="add-to-cart" />
	<input type="hidden" name="item" value="<?php echo $product->getId(); ?>" />
	<button class="btn btn-primary btn-block" type="submit"><?php _e('Add to cart', 'jigoshop'); ?></button>
</form>
