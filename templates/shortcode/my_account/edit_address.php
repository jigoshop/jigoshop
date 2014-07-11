<?php
/**
 * @var $load_address string Address being edited.
 * @var $address array List of address fields.
 */
?>
<form action="<?php echo esc_url( add_query_arg('address', $load_address, apply_filters('jigoshop_get_edit_address_page_id', get_permalink(jigoshop_get_page_id('edit_address')))) ); ?>" method="post">
	<h3>
		<?php if($load_address == 'billing'): ?>
			<?php _e('Billing Address', 'jigoshop'); ?>
		<?php else: ?>
			<?php _e('Shipping Address', 'jigoshop'); ?>
		<?php endif; ?>
	</h3>
	<?php foreach($address as $field): ?>
		<?php jigoshop_customer::address_form_field($field); ?>
	<?php endforeach; ?>
	<?php jigoshop::nonce_field('edit_address') ?>
	<input type="submit" class="button" name="save_address" value="<?php _e('Save Address', 'jigoshop'); ?>" />
</form>