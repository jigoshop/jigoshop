<?php
/**
 * @var $product \Jigoshop\Entity\Product\Simple Product to add.
 */
?>
<form action="" method="post" class="form-inline" role="form">
	<input type="hidden" name="action" value="add-to-cart" />
	<div class="form-group">
		<label class="sr-only" for="product-quantity"><?php _e('Quantity', 'jigoshop'); ?></label>
		<input type="number" class="form-control" name="quantity" id="product-quantity" value="1" />
	</div>
	<button class="btn btn-primary" type="submit"><?php _e('Add to cart', 'jigoshop'); ?></button>
</form>
