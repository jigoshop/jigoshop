<?php
/**
 * @var $product \Jigoshop\Entity\Product\Variable Product to add.
 */
?>
<form action="<?php echo $product->getLink(); ?>" method="get" class="form-inline cart" role="form">
	<button class="btn btn-primary btn-block" type="submit"><?php _e('Select', 'jigoshop'); ?></button>
</form>
