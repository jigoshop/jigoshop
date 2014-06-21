<?php
?>
General
<?php return; ?>
<fieldset>
	<?php
	// Visibility
	$args = array(
		'id' => 'product_visibility',
		'label' => __('Visibility', 'jigoshop'),
		'options' => array(
			'visible' => __('Catalog & Search', 'jigoshop'),
			'catalog' => __('Catalog Only', 'jigoshop'),
			'search' => __('Search Only', 'jigoshop'),
			'hidden' => __('Hidden', 'jigoshop')
		),
		'selected' => get_post_meta($post->ID, 'visibility', true)
	);
	echo Jigoshop_Forms::select($args);

	// Featured
	$args = array(
		'id' => 'featured',
		'label' => __('Featured?', 'jigoshop'),
		'desc' => __('Enable this option to feature this product', 'jigoshop'),
		'value' => false
	);
	echo Jigoshop_Forms::checkbox($args);
	?>
</fieldset>
<fieldset>
	<?php
	// SKU
	if (Jigoshop_Base::get_options()->get_option('jigoshop_enable_sku') !== 'no') {
		$args = array(
			'id' => 'sku',
			'label' => __('SKU', 'jigoshop'),
			'placeholder' => $post->ID,
		);
		echo Jigoshop_Forms::input($args);
	}
	?>
</fieldset>

<fieldset id="price_fieldset">
	<?php
	// Regular Price
	$args = array(
		'id' => 'regular_price',
		'label' => __('Regular Price', 'jigoshop'),
		'after_label' => ' ('.get_jigoshop_currency_symbol().')',
		'type' => 'number',
		'step' => 'any',
		'placeholder' => __('Price Not Announced', 'jigoshop'),
	);
	echo Jigoshop_Forms::input($args);

	// Sale Price
	$args = array(
		'id' => 'sale_price',
		'label' => __('Sale Price', 'jigoshop'),
		'after_label' => ' ('.get_jigoshop_currency_symbol().__(' or %', 'jigoshop').')',
		'desc' => '<a href="#" class="sale_schedule">'.__('Schedule', 'jigoshop').'</a>',
		'placeholder' => __('15% or 19.99', 'jigoshop'),
	);
	echo Jigoshop_Forms::input($args);

	// Sale Price date range
	// TODO: Convert this to a helper somehow?
	$field = array('id' => 'sale_price_dates', 'label' => __('On Sale Between', 'jigoshop'));

	$sale_price_dates_from = get_post_meta($thepostid, 'sale_price_dates_from', true);
	$sale_price_dates_to = get_post_meta($thepostid, 'sale_price_dates_to', true);

	echo '	<p class="form-field sale_price_dates_fields">
							<label for="'.esc_attr($field['id']).'_from">'.$field['label'].'</label>
							<input type="text" class="short date-pick" name="'.esc_attr($field['id']).'_from" id="'.esc_attr($field['id']).'_from" value="';
	if ($sale_price_dates_from) {
		echo date('Y-m-d', $sale_price_dates_from);
	}
	echo '" placeholder="'.__('From', 'jigoshop').' ('.date('Y-m-d').')" maxlength="10" />
							<input type="text" class="short date-pick" name="'.esc_attr($field['id']).'_to" id="'.esc_attr($field['id']).'_to" value="';
	if ($sale_price_dates_to) {
		echo date('Y-m-d', $sale_price_dates_to);
	}
	echo '" placeholder="'.__('To', 'jigoshop').' ('.date('Y-m-d').')" maxlength="10" />
							<a href="#" class="cancel_sale_schedule">'.__('Cancel', 'jigoshop').'</a>
						</p>';
	?>
	<?php do_action('jigoshop_product_pricing_options'); /* allow extensions like sales flash pro to add pricing options */ ?>
</fieldset>

<fieldset>
	<?php
	// External products
	$args = array(
		'id' => 'external_url',
		'label' => __('Product URL', 'jigoshop'),
		'placeholder' => __('The URL of the external product (eg. http://www.google.com)', 'jigoshop'),
		'extras' => array()
	);
	echo Jigoshop_Forms::input($args);
	?>
</fieldset>