<?php global $_product; $availability = $_product->get_availability(); ?>

<p class="stock <?php echo $availability['class'] ?>"><?php echo $availability['availability']; ?></p>
						
<form action="<?php echo $_product->add_to_cart_url(); ?>" class="cart" method="post"><div class="quantity"><input name="quantity" value="1" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div> <button type="submit" class="button-alt"><?php _e('Add to cart', 'jigoshop'); ?></button></form>