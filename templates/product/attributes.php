<?php global $_product; ?>

<?php if ($_product->has_attributes()) : ?>
	<h2><?php _e('Additional Information', 'jigoshop'); ?></h2>
	<?php $_product->list_attributes(); ?>
<?php endif; ?>