<?php
?>
Advanced
<?php return; ?>
<fieldset id="tax_fieldset">
	<?php

	// Tax Status
	$args = array(
		'id' => 'tax_status',
		'label' => __('Tax Status', 'jigoshop'),
		'options' => array(
			'taxable' => __('Taxable', 'jigoshop'),
			'shipping' => __('Shipping', 'jigoshop'),
			'none' => __('None', 'jigoshop')
		)
	);
	echo Jigoshop_Forms::select($args);

	?>

	<p class="form_field tax_classes_field">
		<label for="tax_classes"><?php _e('Tax Classes', 'jigoshop'); ?></label>
	            	<span class="multiselect short">
	            <?php
	            $_tax = new jigoshop_tax();
	            $tax_classes = $_tax->get_tax_classes();
	            $selections = (array)get_post_meta($post->ID, 'tax_classes', true);

	            $checked = checked(in_array('*', $selections), true, false);

	            printf('<label %s><input type="checkbox" name="tax_classes[]" value="%s" %s/> %s</label>'
		            , !empty($checked) || $selections[0] == '' ? 'class="selected"' : ''
		            , '*'
		            , $checked
		            , __('Standard', 'jigoshop'));

	            if ($tax_classes) {

		            foreach ($tax_classes as $tax_class) {
			            $checked = checked(in_array(sanitize_title($tax_class), $selections), true, false);
			            printf('<label %s><input type="checkbox" name="tax_classes[]" value="%s" %s/> %s</label>'
				            , !empty($checked) ? 'class="selected"' : ''
				            , sanitize_title($tax_class)
				            , $checked
				            , __($tax_class, 'jigoshop'));
		            }
	            }
	            ?>
	            	</span>
	            	<span class="multiselect-controls">
						<a class="check-all" href="#"><?php _e('Check All', 'jigoshop'); ?></a>&nbsp;|
						<a class="uncheck-all" href="#"><?php _e('Uncheck All', 'jigoshop'); ?></a>
					</span>
	</p>
</fieldset>

<?php if (Jigoshop_Base::get_options()->get_option('jigoshop_enable_weight') !== 'no' || Jigoshop_Base::get_options()
		->get_option('jigoshop_enable_dimensions', true) !== 'no'
): ?>
	<fieldset id="form_fieldset">
		<?php
		// Weight
		if (Jigoshop_Base::get_options()->get_option('jigoshop_enable_weight') !== 'no') {
			$args = array(
				'id' => 'weight',
				'label' => __('Weight', 'jigoshop'),
				'after_label' => ' ('.Jigoshop_Base::get_options()->get_option('jigoshop_weight_unit').')',
				'type' => 'number',
				'step' => 'any',
				'placeholder' => '0.00',
			);
			echo Jigoshop_Forms::input($args);
		}

		// Dimensions
		if (Jigoshop_Base::get_options()->get_option('jigoshop_enable_dimensions', true) !== 'no') {
			echo '
					<p class="form-field dimensions_field">
						<label for"product_length">'.__('Dimensions', 'jigoshop').' ('.Jigoshop_Base::get_options()->get_option('jigoshop_dimension_unit').')'.'</label>
						<input type="number" step="any" name="length" class="short" value="'.get_post_meta($thepostid, 'length', true).'" placeholder="'.__('Length', 'jigoshop').'" />
						<input type="number" step="any" name="width" class="short" value="'.get_post_meta($thepostid, 'width', true).'" placeholder="'.__('Width', 'jigoshop').'" />
						<input type="number" step="any" name="height" class="short" value="'.get_post_meta($thepostid, 'height', true).'" placeholder="'.__('Height', 'jigoshop').'" />
					</p>
					';
		}
		?>
	</fieldset>
<?php endif; ?>

<fieldset>
	<?php
	// Customizable
	$args = array(
		'id' => 'product_customize',
		'label' => __('Can be personalized', 'jigoshop'),
		'options' => array(
			'no' => __('No', 'jigoshop'),
			'yes' => __('Yes', 'jigoshop'),
		),
		'selected' => get_post_meta($post->ID, 'customizable', true),
	);
	echo Jigoshop_Forms::select($args);

	// Customizable length
	$args = array(
		'id' => 'customized_length',
		'label' => __('Personalized Characters', 'jigoshop'),
		'type' => 'number',
		'value' => get_post_meta($post->ID, 'customized_length', true),
		'placeholder' => __('Leave blank for unlimited', 'jigoshop'),
	);
	echo Jigoshop_Forms::input($args);
	?>
</fieldset>