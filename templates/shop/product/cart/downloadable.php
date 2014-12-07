<?php
/**
 * @var $product \Jigoshop\Entity\Product\Downloadable Product to add.
 */
?>
<form action="" method="post" class="form-inline" role="form">
	<input type="hidden" name="action" value="add-to-cart" />
	<input type="hidden" name="quantity" value="1" />
	<button class="btn btn-primary" type="submit"><?php _e('Add to cart', 'jigoshop'); ?></button>
</form>
