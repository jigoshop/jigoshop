<?php
/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 * @var $view_cart_button_id string Number field ID.
 * @var $view_cart_button_name string Number field name.
 * @var $view_cart_button string Number of products in widget.
 * @var $checkout_button_id string Number field ID.
 * @var $checkout_button_name string Number field name.
 * @var $checkout_button string Number of products in widget.
 */
?>
<p>
	<label for="<?php echo $title_id; ?>"><?php _e('Title:', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $title_id; ?>"  name="<?php echo $title_name; ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
	<label for="<?php echo $view_cart_button_id; ?>"><?php _e('View cart button:', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $view_cart_button_id; ?>"  name="<?php echo $view_cart_button_name; ?>" type="text" value="<?php echo $view_cart_button; ?>" />
</p>
<p>
	<label for="<?php echo $checkout_button_id; ?>"><?php _e('Checkout button:', 'jigoshop'); ?></label>
	<input class="widefat" id="<?php echo $checkout_button_id; ?>"  name="<?php echo $checkout_button_name; ?>" type="text" value="<?php echo $checkout_button; ?>" />
</p>
