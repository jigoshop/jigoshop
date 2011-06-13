<?php global $_product; ?>

<form action="<?php echo $_product->add_to_cart_url(); ?>" class="cart" method="post">
	<table cellspacing="0">
		<tbody>
			<?php foreach ($_product->children as $child) : $child_product = &new jigoshop_product( $child->ID ); $cavailability = $child_product->get_availability(); ?>
				<tr>
					<td><div class="quantity"><input name="quantity[<?php echo $child->ID; ?>]" value="0" size="4" title="Qty" class="input-text qty text" maxlength="12" /></div></td>
					<td><label for="product-<?php echo $child_product->id; ?>"><?php 
						if ($child_product->is_visible()) echo '<a href="'.get_permalink($child->ID).'">';
						echo $child_product->get_title(); 
						if ($child_product->is_visible()) echo '</a>';
					?></label></td>
					<td class="price"><?php echo $child_product->get_price_html(); ?><small class="stock <?php echo $cavailability['class'] ?>"><?php echo $cavailability['availability']; ?></small></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<button type="submit" class="button-alt"><?php _e('Add to cart', 'jigoshop'); ?></button>
</form>