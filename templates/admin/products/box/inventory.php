<?php
?>
Inventory
<?php return; ?>
<fieldset>
	<?php
	// manage stock
	$args = array(
		'id' => 'manage_stock',
		'label' => __('Manage Stock?', 'jigoshop'),
		'desc' => __('Handle stock for me', 'jigoshop'),
		'value' => false
	);
	echo Jigoshop_Forms::checkbox($args);

	?>
</fieldset>
<fieldset>
	<?php
	// Stock Status
	// TODO: These values should be true/false
	$args = array(
		'id' => 'stock_status',
		'label' => __('Stock Status', 'jigoshop'),
		'options' => array(
			'instock' => __('In Stock', 'jigoshop'),
			'outofstock' => __('Out of Stock', 'jigoshop')
		)
	);
	echo Jigoshop_Forms::select($args);

	echo '<div class="stock_fields">';

	// Stock
	// TODO: Missing default value of 0
	$args = array(
		'id' => 'stock',
		'label' => __('Stock Quantity', 'jigoshop'),
		'type' => 'number',
	);
	echo Jigoshop_Forms::input($args);

	// Backorders
	$args = array(
		'id' => 'backorders',
		'label' => __('Allow Backorders?', 'jigoshop'),
		'options' => array(
			'no' => __('Do not allow', 'jigoshop'),
			'notify' => __('Allow, but notify customer', 'jigoshop'),
			'yes' => __('Allow', 'jigoshop')
		)
	);
	echo Jigoshop_Forms::select($args);

	echo '</div>';
	?>
</fieldset>