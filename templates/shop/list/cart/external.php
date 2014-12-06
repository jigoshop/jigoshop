<?php
/**
 * @var $product \Jigoshop\Entity\Product\External Product to add.
 */
?>
<form action="<?php echo $product->getUrl(); ?>" method="get" class="form-inline cart" role="form" target="_blank">
	<button class="btn btn-primary btn-block" type="submit"><?php _e('Buy product', 'jigoshop'); ?></button>
</form>
