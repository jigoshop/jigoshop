<?php global $_product; $availability = $_product->get_availability(); ?>

<?php if ($availability['availability']) : ?><p class="stock <?php echo $availability['class'] ?>"><?php echo $availability['availability']; ?></p><?php endif; ?>
						
<form action="<?php echo $_product->add_to_cart_url(); ?>" class="cart" method="post">
	<button type="submit" class="button-alt"><?php _e('Add to cart', 'jigoshop'); ?></button>
	<?php jigoshop::nonce_field('add_to_cart') ?>
</form>