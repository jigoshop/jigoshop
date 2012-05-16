<?php
/**
 * Functions for the settings page in admin.
 *
 * The settings page contains options for the Jigoshop plugin - this file contains functions to display
 * and save the list of options.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 * Defines a custom sort for the tax_rates array. The sort that is needed is that the array is sorted
 * by country, followed by state, followed by compound tax. The difference is that compound must be sorted based
 * on compound = no before compound = yes. Ultimately, the purpose of the sort is to make sure that country, state
 * are all consecutive in the array, and that within those groups, compound = 'yes' always appears last. This is
 * so that tax classes that are compounded will be executed last in comparison to those that aren't.
 * last.
 * <br>
 * <pre>
 * eg. country = 'CA', state = 'QC', compound = 'yes'<br>
 *     country = 'CA', state = 'QC', compound = 'no'<br>
 *
 * will be sorted to have <br>
 *     country = 'CA', state = 'QC', compound = 'no'<br>
 *     country = 'CA', state = 'QC', compound = 'yes' <br>
 * </pre>
 *
 * @param type $a the first object to compare with (our inner array)
 * @param type $b the second object to compare with (our inner array)
 * @return int the results of strcmp
 */
function csort_tax_rates($a, $b) {
    $str1 = '';
    $str2 = '';

    $str1 .= $a['country'] . $a['state'] . ($a['compound'] == 'no' ? 'a' : 'b');
    $str2 .= $b['country'] . $b['state'] . ($b['compound'] == 'no' ? 'a' : 'b');

    return strcmp($str1, $str2);
}

/**
 * Update options
 *
 * Updates the options on the jigoshop settings page.
 *
 * @since 		1.0
 * @usedby 		jigoshop_settings()
 *
 * @param 		array $jigoshop_options_settings List of options to go through and save
 */
function jigoshop_update_options() {
    global $jigoshop_options_settings;

	/* If the settings haven't been saved, don't continue at all ! */
    if ( empty($_POST['submitted']) ) return false;

	check_admin_referer( 'jigoshop-update-settings', '_jigoshop_csrf' );

	foreach ($jigoshop_options_settings as $value) :

		$valueID   = !empty($value['id'])   ? $value['id']   : '';
		$valueType = !empty($value['type']) ? $value['type'] : '';

		if ( $valueType == 'tax_rates' ) {
			jigoshop_update_taxes();
			continue;
		}

		if ( $valueType == 'coupons' ) {
			jigoshop_update_coupons();
			continue;
		}

		if ( $valueType == 'checkbox' ) {
			update_option($valueID, isset ( $_POST[$valueID] ) ? 'yes' : 'no');
			continue;
		}

		if ( $valueType == 'multi_select_countries' ) {
			update_option($valueID, isset( $_POST[$valueID] ) ? $_POST[$valueID] : array());
			continue;
		}

		/* default back to standard image sizes if no value is entered */
		if ( $valueType == 'image_size' ) {

			$sizes = array(
				'jigoshop_shop_tiny'      => 'jigoshop_use_wordpress_tiny_crop',
				'jigoshop_shop_thumbnail' => 'jigoshop_use_wordpress_thumbnail_crop',
				'jigoshop_shop_small'     => 'jigoshop_use_wordpress_catalog_crop',
				'jigoshop_shop_large'     => 'jigoshop_use_wordpress_featured_crop'
			);

			$altSize = $sizes[$valueID];

			$dimensions = array( '_w', '_h' );
			foreach ( $dimensions as $v )
				!empty( $_POST[$valueID.$v] )
				? update_option( $valueID.$v, jigowatt_clean($_POST[$valueID.$v]) )
				: update_option( $valueID.$v, $value['std'] );

			update_option($altSize, isset ( $_POST[$altSize] ) ? 'yes' : 'no');

			continue;

		}

		/* Price separators get a special treatment as they should allow a spaces (don't trim) */
		if ( $valueID == 'jigoshop_price_thousand_sep' || $valueID == 'jigoshop_price_decimal_sep' ) {
			isset($_POST[$valueID]) ? update_option($valueID, $_POST[$valueID]) : @delete_option($valueID);
			continue;
		}

		isset($valueID) && isset($_POST[$valueID])
			? update_option($valueID, jigowatt_clean($_POST[$valueID]))
			: @delete_option($valueID);

	endforeach;

	add_action( 'jigoshop_admin_settings_notices', 'jigoshop_settings_updated_notice' );
	do_action ( 'jigoshop_update_options' );

}

add_action('load-jigoshop_page_jigoshop_settings', 'jigoshop_update_options');

/* Remove duplicates from multi dimensional arrays */
function super_unique($array) {
	$result = array_map("unserialize", array_unique(array_map("serialize", $array)));
	foreach ($result as $key => $value)
		if ( is_array($value) ) $result[$key] = super_unique($value);

	return $result;
}

function jigoshop_update_taxes() {

	$taxFields = array(
		'tax_classes' => '',
		'tax_country' => '',
		'tax_rate'    => '',
		'tax_label'   => '',
		'tax_shipping'=> '',
		'tax_compound'=> ''
	);

	$tax_rates = array();

	/* Save each array key to a variable */
	foreach ($taxFields as $name => $val)
		if (isset($_POST[$name])) $taxFields[$name] = $_POST[$name];

	extract($taxFields);

	for ($i = 0; $i < sizeof($tax_classes); $i++) :

		if ( empty($tax_rate[$i]) )
			continue;

		$countries = $tax_country[$i];
		$label     = trim($tax_label[$i]);
		$rate      = number_format((float)jigowatt_clean($tax_rate[$i]), 4);
		$class     = jigowatt_clean($tax_classes[$i]);

		/* Checkboxes */
		$shipping = !empty($tax_shipping[$i]) ? 'yes' : 'no';
		$compound = !empty($tax_compound[$i]) ? 'yes' : 'no';

		/* Save the state & country separately from options eg US:OH */
		$states  = array();
		foreach ( $countries as $k => $countryCode ) :
			if (strstr($countryCode, ':')) :
				$cr = explode(':', $countryCode);
				$states[$cr[1]]  = $cr[0];
				unset($countries[$k]);
			endif;
		endforeach;

		/* Save individual state taxes, eg OH => US (State => Country) */
		foreach ( $states as $state => $country ) :

			$tax_rates[] = array(
				'country'      => $country,
				'label'        => $label,
				'state'        => $state,
				'rate'         => $rate,
				'shipping'     => $shipping,
				'class'        => $class,
				'compound'     => $compound,
				'is_all_states'=> false //determines if admin panel should show 'all_states'
			);

		endforeach;

		foreach ( $countries as $country ) :

			/* Countries with states */
			if ( jigoshop_countries::country_has_states($country)) {

				foreach (array_keys(jigoshop_countries::$states[$country]) as $st) :
					$tax_rates[] = array(
						'country'      => $country,
						'label'        => $label,
						'state'        => $st,
						'rate'         => $rate,
						'shipping'     => $shipping,
						'class'        => $class,
						'compound'     => $compound,
						'is_all_states'=> false //determines if admin panel should show 'all_states'
					);
				endforeach;

			/* This country has no states, eg AF */
			} else {

				 $tax_rates[] = array(
					'country'      => $country,
					'label'        => $label,
					'state'        => '*',
					'rate'         => $rate,
					'shipping'     => $shipping,
					'class'        => $class,
					'compound'     => $compound,
					'is_all_states'=> true //determines if admin panel should show 'all_states'
				);

			}

		endforeach;

	endfor;

	$tax_rates = super_unique($tax_rates);
	usort($tax_rates, "csort_tax_rates");
	update_option('jigoshop_tax_rates', $tax_rates);

}

function jigoshop_update_coupons() {

	$couponFields = array(
		'coupon_code'     => '',
		'coupon_type'     => '',
		'coupon_amount'   => '',
		'product_ids'     => '',
		'coupon_date_from'=> '',
		'coupon_date_to'  => '',
		'individual'      => ''
	);

	$coupons = array();

	/* Save each array key to a variable */
	foreach ($couponFields as $name => $val)
		if (isset($_POST[$name])) $couponFields[$name] = $_POST[$name];

	extract($couponFields);

	for ($i = 0; $i < sizeof($coupon_code); $i++) :

		if ( empty($coupon_code[$i]) || !is_numeric($coupon_amount[$i]) ) continue;

		$amount        = jigowatt_clean($coupon_amount[$i]);
		$code          = jigowatt_clean($coupon_code[$i]);
		$type          = jigowatt_clean($coupon_type[$i]);
		$from_date     = !empty($coupon_date_from[$i])? strtotime($coupon_date_from[$i])                    : 0;
		$individual_use= !empty($individual[$i])      ? 'yes'                                               : 'no';
		$products      = !empty($product_ids[$i])     ? array_map('trim', explode(',', $product_ids[$i]))   : array();
		$to_date       = !empty($coupon_date_to[$i])  ? strtotime($coupon_date_to[$i]) + (60 * 60 * 24 - 1) : 0;

		if ($code && $type && $amount)
			$coupons[$code] = array(
				'code'          => $code,
				'amount'        => $amount,
				'type'          => $type,
				'products'      => $products,
				'date_from'     => $from_date,
				'date_to'       => $to_date,
				'individual_use'=> $individual_use
			);

	endfor;

	update_option('jigoshop_coupons', $coupons);

}

/**
 * Admin fields
 *
 * Loops though the jigoshop options array and outputs each field.
 *
 * @since 		1.0
 * @usedby 		jigoshop_settings()
 *
 * @param 		array $options List of options to go through and save
 */
function jigoshop_admin_fields($options) {
    ?>
    <h2 class="nav-tab-wrapper" id="jigoshop-nav-tab-wrapper">
        <?php
        $counter = 1;
        foreach ($options as $value) {
            if ('tab' == $value['type']) :
                echo '<a href="#' . $value['type'] . $counter . '" class="nav-tab" style="font-size:14px; margin-right:0px">' . $value['tabname'] . '</a>' . "\n";
                $counter++;
            endif;
        }
        ?>
    </h2>
    <div >
        <!-- <p class="submit"><input name="save" type="submit" value="<?php _e('Save changes', 'jigoshop') ?>" /></p> -->
        <?php
        $counter = 1;
        foreach ($options as $value) :

			switch ($value['type']) :

			case 'string':
				?><tr>
					<th scope="row"><?php echo $value['name']; ?></td>
					<td><?php echo $value['desc']; ?></td>
				  </tr><?php
				break;

			case 'tab':
				?><div id="<?php echo $value['type'] . $counter; ?>" class="panel">
				  <table class="form-table"><?php
				break;

			case 'title':
				?><thead>
					<tr>
						<th scope="col" colspan="2">
							<h3 class="title"><?php echo $value['name'] ?></h3>
							<?php if ( !empty($value['desc']) ) : ?>
							<p><?php echo $value['desc']; ?></p>
							<?php endif; ?>
						</th>
					</tr>
				  </thead><?php
				break;

			case 'checkbox':
				?><tr>
                    <th scope="row"<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
						<?php if (!empty($value['tip'])) : ?>
						<a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99" ></a>
						<?php endif; ?>
						<?php if ( !empty( $value['name'] ) ) : ?>
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label>
						<?php endif; ?>
					</th>
					<td<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
						<input
						id="<?php echo esc_attr( $value['id'] ); ?>"
						type="checkbox"
						class="jigoshop-input jigoshop-checkbox"
						style="<?php if ( !empty($value['css']) ) echo esc_attr( $value['css'] ); ?>"
						name="<?php echo esc_attr( $value['id'] ); ?>"
						<?php if (get_option($value['id']) !== false && get_option($value['id']) !== null)
						echo checked(get_option($value['id']), 'yes', false);
						else if ( isset($value['std'])) echo checked( $value['std'], 'yes', false ); ?> />
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php if (!empty($value['desc'])) echo $value['desc']; ?></label>
					</td>
				  </tr><?php
				break;

            case 'text':
                ?><tr>
                    <th scope="row"<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
						<?php if (!empty($value['tip'])) : ?>
						<a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a>
						<?php endif; ?>
						<?php if ( !empty( $value['name'] ) ) : ?>
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label>
						<?php endif; ?>
					</th>

                    <td<?php if ( empty( $value['name'] ) ) : ?> style="padding-top:0px;"<?php endif; ?>>
                    	<input name="<?php echo esc_attr( $value['id'] ); ?>"
                    		id="<?php echo esc_attr( $value['id'] ); ?>"
                    		type="<?php echo $value['type'] ?>"
                            class="regular-text"
                    		style="<?php if ( !empty($value['css']) ) echo esc_attr( $value['css'] ); ?>"
                    		value="<?php if (get_option($value['id']) !== false && get_option($value['id']) !== null)
                    			echo esc_attr( get_option($value['id']) );
                    			else if ( isset($value['std'])) echo esc_attr( $value['std'] ); ?>" />
                    	<?php if ( !empty( $value['name'] ) && !empty( $value['atr'] ) && $value['atr'] != 'group' ) : ?>
							<br /><small><?php echo $value['desc'] ?></small>
						<?php else: ?>
							<?php echo $value['desc'] ?>
						<?php endif; ?>
                    </td>
                  </tr><?php
                break;

            case 'select':
				?><tr>
                    <th scope="row">
						<?php if (!empty($value['tip'])) : ?>
						<a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a>
						<?php endif; ?>
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label>
					</th>
					<td>
						<select name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" style="<?php if ( isset($value['css'])) echo esc_attr( $value['css'] ); ?>">

						<?php foreach ($value['options'] as $key => $val) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php if (get_option($value['id']) == $key) { ?> selected="selected" <?php } ?>><?php echo ucfirst($val) ?></option>
						<?php endforeach; ?>
						</select><br /><small><?php echo $value['desc'] ?></small>
					</td>
				  </tr><?php
                break;

            case 'radio':
				?><tr>
                    <th scope="row">
						<?php if (!empty($value['tip'])) : ?>
						<a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a>
						<?php endif; ?>
						<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label>
					</th>
					<td>
						<?php foreach ($value['options'] as $key => $val) : ?>
						<label class="radio">
						<input type="radio"
							   name="<?php echo esc_attr( $value['id'] ); ?>"
							   id="<?php echo esc_attr( $key ); ?>"
							   value="<?php echo esc_attr( $key ); ?>"
							   <?php if (get_option($value['id']) == $key) { ?> checked="checked" <?php } ?>>
						<?php echo esc_attr( ucfirst( $val ) ); ?>
						</label><br />
						<?php endforeach; ?>
					</td>
				  </tr><?php
                break;

			case 'image_size' :

				$sizes = array(
					'jigoshop_shop_tiny'      => 'jigoshop_use_wordpress_tiny_crop',
					'jigoshop_shop_thumbnail' => 'jigoshop_use_wordpress_thumbnail_crop',
					'jigoshop_shop_small'     => 'jigoshop_use_wordpress_catalog_crop',
					'jigoshop_shop_large'     => 'jigoshop_use_wordpress_featured_crop'
				);

				$altSize = $sizes[$value['id']];

				?><tr>
					<th scope="row"><?php echo $value['name'] ?></label></th>
					<td valign="top" style="line-height:25px;height:25px;">

						<label for="<?php echo esc_attr( $value['id'] ); ?>_w"><?php _e('Width', 'jigoshop'); ?></label>
                        <input name="<?php echo esc_attr( $value['id'] ); ?>_w" id="<?php echo esc_attr( $value['id'] ); ?>_w" type="text" size="3" value="<?php if ( $size = get_option( $value['id'].'_w') ) echo $size; else echo $value['std']; ?>" />

						<label for="<?php echo esc_attr( $value['id'] ); ?>_h"><?php _e('Height', 'jigoshop'); ?></label>
                        <input name="<?php echo esc_attr( $value['id'] ); ?>_h" id="<?php echo esc_attr( $value['id'] ); ?>_h" type="text" size="3" value="<?php if ( $size = get_option( $value['id'].'_h') ) echo $size; else echo $value['std']; ?>" />
						<input
						id="<?php echo esc_attr( $altSize ); ?>"
						type="checkbox"
						class="jigoshop-input jigoshop-checkbox"
						name="<?php echo esc_attr( $altSize ); ?>"
						<?php if (get_option($altSize) !== false && get_option($altSize) !== null)
						echo checked(get_option($altSize), 'yes', false); ?> />
						<label for="<?php echo esc_attr( $altSize ); ?>"> <?php echo __('Crop?', 'jigoshop'); ?></label>
						<a href="#" tip="<?php echo __('Leave unchecked to set the image size by resizing the image proportionally (that is, without distorting it).<br />Leave checked to set the image size by hard cropping the image (either from the sides, or from the top and bottom).', 'jigoshop'); ?>" class="tips" style="float:none;display:inline-block;margin: 4px -8px 0 10px;" tabindex="99"></a>
						<br /><small><?php echo $value['desc'] ?></small>
					</td>
				</tr><?php
				break;

            case 'textarea':
                ?><tr>
                        <th scope="row"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label></th>
                        <td>
                            <textarea <?php if (isset($value['args']))
                    echo $value['args'] . ' '; ?>
                    name="<?php echo esc_attr( $value['id'] ); ?>"
                    id="<?php echo esc_attr( $value['id'] ); ?>"
                    class="large-text" style="<?php echo esc_attr( $value['css'] ); ?>"><?php echo esc_textarea( ( get_option($value['id'])) ? stripslashes(get_option($value['id'])) : $value['std'] ); ?></textarea>
                            <br /><small><?php echo $value['desc'] ?></small>
                        </td>
                    </tr><?php
                break;

            case 'tabend':
                ?></table></div><?php
                $counter = $counter + 1;
                break;

            case 'single_select_page' :

                $args = array(
					'name'        => $value['id'],
					'id'          => $value['id'] . '" style="width: 200px;',
					'sort_column' => 'menu_order',
					'sort_order'  => 'ASC',
					'selected'    => (int) get_option($value['id'])
				);

                if ( !empty($value['args']) ) $args = wp_parse_args($value['args'], $args);
				?><tr class="single_select_page">
					<th scope="row"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label></th>
					<td>
						<?php wp_dropdown_pages($args); ?>
						<br /><small><?php echo $value['desc'] ?></small>
					</td>
				</tr><?php
				break;
                case 'single_select_country' :
                    $countries = jigoshop_countries::$countries;
                    $country_setting = (string) get_option($value['id']);
                    if (strstr($country_setting, ':')) :
                        $country = current(explode(':', $country_setting));
                        $state = end(explode(':', $country_setting));
					else :
                        $country = $country_setting;
                        $state = '*';
                    endif;
                    ?><tr class="multi_select_countries">
                        <th scope="row"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name'] ?></label></th>
                        <td>
							<select id="<?php echo esc_attr( $value['id'] ); ?>" name="<?php echo esc_attr( $value['id'] ); ?>" title="Country" style="width: 150px;">
							<?php
								$show_all = ($value['id'] != 'jigoshop_default_country');
								echo jigoshop_countries::country_dropdown_options($country, $state, false, $show_all);
							?>
                            </select>
                        </td>
                    </tr><?php
					if (!$show_all && jigoshop_countries::country_has_states($country) && $state == '*') jigoshop_countries::base_country_notice();
                break;
            case 'multi_select_countries' :
                $countries = jigoshop_countries::$countries;
                asort($countries);
                $selections = (array) get_option($value['id']);
                ?><tr class="multi_select_countries">
                        <th scope="row"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label><?php echo $value['name'] ?></label></th>
                        <td>
                            <div class="multi_select_countries">
								<ul><?php
							if ($countries)
								foreach ($countries as $key => $val) :
								?><li><label>
									<input type="checkbox"
										   name="<?php echo esc_attr( $value['id'] ) . '[]'; ?>"
										   value="<?php echo esc_attr( $key ); ?>"
										   <?php if (in_array($key, $selections)) : ?>
										   checked="checked"
										   <?php endif; ?>
									/>
									<?php echo $val; ?>
									</label></li><?php
								endforeach;
							  ?></ul>
							</div>
                        </td>
                    </tr><?php
                    break;
                case 'coupons' :
                    $coupons = new jigoshop_coupons();
                    $coupon_codes = $coupons->get_coupons();
                ?>
					<thead><tr><th scope="col" colspan="2"><h3 class="title"><?php _e('Coupon Settings', 'jigoshop'); ?></h3></th></tr></thead>
					<tr>
                        <td id="coupon_codes">
                            <table class="coupon_rows" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th></th>
										<th><?php _e('Code'  , 'jigoshop'); ?></th>
										<th><?php _e('Type'  , 'jigoshop'); ?></th>
										<th><?php _e('Amount', 'jigoshop'); ?></th>
										<th><?php _e("ID's"  , 'jigoshop'); ?></th>
										<th><?php _e('From'  , 'jigoshop'); ?></th>
										<th><?php _e('To'    , 'jigoshop'); ?></th>
										<th><?php _e('Alone' , 'jigoshop'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = -1;
                                    if ($coupon_codes && is_array($coupon_codes) && sizeof($coupon_codes) > 0)
                                        foreach ($coupon_codes as $coupon) : $i++;
										?><tr class="coupon_row">
                                              <td><a href="#" class="remove button" title="' . __('Delete this Coupon', 'jigoshop') . '">&times;</a></td>
                                              <td><input type="text" value="<?php echo esc_attr( $coupon['code'] ); ?>" name="coupon_code[<?php echo esc_attr( $i ); ?>]" title="<?php _e('Coupon Code', 'jigoshop'); ?>" placeholder="<?php _e('Coupon Code', 'jigoshop'); ?>" class="text" /></td>
											  <td>
												<select name="coupon_type[<?php echo esc_attr( $i ); ?>]" title="Coupon Type"><?php

                                            $discount_types = array(
												'fixed_cart'     => __('Cart Discount'     , 'jigoshop'),
												'percent'        => __('Cart % Discount'   , 'jigoshop'),
												'fixed_product'  => __('Product Discount'  , 'jigoshop'),
												'percent_product'=> __('Product % Discount', 'jigoshop')
                                            );

                                            foreach ($discount_types as $type => $label) :
                                                ?><option value="<?php echo esc_attr( $type ); ?>" <?php echo ($coupon['type'] == $type) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $label ); ?></option><?php
                                            endforeach;
											  ?></select>
											  </td>
                                            <td><input type="text" value="<?php echo esc_attr( $coupon['amount'] ); ?>" name="coupon_amount[<?php echo esc_attr( $i ); ?>]" title="<?php _e('Coupon Amount', 'jigoshop'); ?>" placeholder="<?php _e('Amount', 'jigoshop'); ?>" class="text" /></td>
			                    			<td><input type="text" value="<?php echo ( ( is_array( $coupon['products'] ) ) ? implode( ', ', $coupon['products'] ) : '' ); ?>" name="product_ids[<?php echo esc_attr( $i ); ?>]" placeholder="<?php _e('1, 2, 3,', 'jigoshop'); ?>" class="text" /></td>

                                            <?php $coupon_date_from = $coupon['date_from']; ?>
                                            <td><label for="coupon_date_from[<?php echo esc_attr( $i ); ?>]"></label>
												<input type="text"
													   class="text date-pick"
													   name="coupon_date_from[<?php echo esc_attr( $i ); ?>]"
													   id="coupon_date_from[<?php echo esc_attr( $i ); ?>]"
													   value="<?php if ($coupon_date_from) echo date('Y-m-d', $coupon_date_from); ?>"
													   placeholder="<?php _e('yyyy-mm-dd', 'jigoshop'); ?>"
												/>
											</td>

                                            <?php $coupon_date_to = $coupon['date_to']; ?>
                                            <td><label for="coupon_date_to[<?php echo esc_attr( $i ); ?>]"></label>
												<input type="text"
													   class="text date-pick"
													   name="coupon_date_to[<?php echo esc_attr( $i ); ?>]"
													   id="coupon_date_to[<?php echo esc_attr( $i ); ?>]"
													   value="<?php if ($coupon_date_to) echo date('Y-m-d', $coupon_date_to); ?>"
													   placeholder="<?php _e('yyyy-mm-dd', 'jigoshop'); ?>"
												/>
											</td>

                                            <td><input type="checkbox" name="individual[<?php echo esc_attr( $i ); ?>]"
                                            <?php if (isset($coupon['individual_use']) && $coupon['individual_use'] == 'yes') : ?>
                                                checked="checked"
											<?php endif; ?> />
											</td>
										</tr>
                                        <script type="text/javascript">
                                            /* <![CDATA[ */
                                            jQuery(function() {
                                                // DATE PICKER FIELDS
                                                //												Date.firstDayOfWeek = 1;
                                                //												Date.format = 'yyyy-mm-dd';
                                                //												jQuery('.date-pick').datePicker();
                                                jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );

                                                /*
                                                jQuery('#coupon_date_from[<?php echo $i; ?>]').bind(
                                                    'dpClosed',
                                                    function(e, selectedDates)
                                                    {
                                                        var d = selectedDates[0];
                                                        if (d) {
                                                            d = new Date(d);
                                                            jQuery('#coupon_date_to[<?php echo $i; ?>]').dpSetStartDate(d.addDays(1).asString());
                                                        }
                                                    }
                                                );
                                                jQuery('#coupon_date_to[<?php echo $i; ?>]').bind(
                                                    'dpClosed',
                                                    function(e, selectedDates)
                                                    {
                                                        var d = selectedDates[0];
                                                        if (d) {
                                                            d = new Date(d);
                                                            jQuery('#coupon_date_from[<?php echo $i; ?>]').dpSetEndDate(d.addDays(-1).asString());
                                                        }
                                                    }
                                                );

                                                 */
                                            });
                                            /* ]]> */
                                        </script>
									<?php endforeach; ?>
                                </tbody>
                            </table>
                            <p><a href="#" class="add button"><?php _e('+ Add Coupon', 'jigoshop'); ?></a></p>
                        </td>
                    </tr>
                    <script type="text/javascript">
                        /* <![CDATA[ */
                        jQuery(function() {
                            jQuery('#coupon_codes a.add').live('click', function(){
                                var size = jQuery('#coupon_codes table.coupon_rows tbody .coupon_row').size();
                                // Make sure tbody exists
                                var tbody_size = jQuery('#coupon_codes table.coupon_rows tbody').size();
                                if (tbody_size==0) jQuery('#coupon_codes table.coupon_rows').append('<tbody></tbody>');

                                // Add the row
                                jQuery('<tr class="coupon_row">\
                                    <td><a href="#" class="remove button" title="<?php __('Delete this Coupon', 'jigoshop'); ?>">&times;</a></td>\
                                    <td><input type="text" value="" name="coupon_code[' + size + ']" title="<?php _e('Coupon Code', 'jigoshop'); ?>" placeholder="<?php _e('Coupon Code', 'jigoshop'); ?>" class="text" /></td>\
                                    <td><select name="coupon_type[' + size + ']" title="Coupon Type">\
                                        <option value="fixed_cart"><?php _e('Cart Discount', 'jigoshop'); ?></option>\
                                        <option value="percent"><?php _e('Cart % Discount', 'jigoshop'); ?></option>\
                                        <option value="fixed_product"><?php _e('Product Discount', 'jigoshop'); ?></option>\
                                        <option value="percent_product"><?php _e('Product % Discount', 'jigoshop'); ?></option>\
                                    </select></td>\
                                    <td><input type="text" value="" name="coupon_amount[' + size + ']" title="<?php _e('Coupon Amount', 'jigoshop'); ?>" placeholder="<?php _e('Amount', 'jigoshop'); ?>" class="text" /></td>\
                                    <td><input type="text" value="" name="product_ids[' + size + ']" \
                                        placeholder="<?php _e('1, 2, 3,', 'jigoshop'); ?>" class="text" /></td>\
                                    <td><label for="coupon_date_from[' + size + ']"></label>\
                                        <input type="text" class="text date-pick" name="coupon_date_from[' + size + ']" \
                                        id="coupon_date_from[' + size + ']" value="" \
                                        placeholder="<?php _e('yyyy-mm-dd', 'jigoshop'); ?>" /></td>\
                                    <td><label for="coupon_date_to[' + size + ']"></label>\
                                        <input type="text" class="text date-pick" name="coupon_date_to[' + size + ']" \
                                        id="coupon_date_to[' + size + ']" value="" \
                                        placeholder="<?php _e('yyyy-mm-dd', 'jigoshop'); ?>" /></td>\
                                    <td><input type="checkbox" name="individual[' + size + ']" /></td>').appendTo('#coupon_codes table.coupon_rows tbody');

                                                jQuery(function() {
                                                    // DATE PICKER FIELDS
                                                    //										Date.firstDayOfWeek = 1;
                                                    //										Date.format = 'yyyy-mm-dd';
                                                    //										jQuery('.date-pick').datePicker();
                                                    jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );

                                                    /*
                                    jQuery('#coupon_date_from[' + size + ']').bind(
                                        'dpClosed',
                                        function(e, selectedDates)
                                        {
                                            var d = selectedDates[0];
                                            if (d) {
                                                d = new Date(d);
                                                jQuery('#coupon_date_to[' + size + ']').dpSetStartDate(d.addDays(1).asString());
                                            }
                                        }
                                    );
                                    jQuery('#coupon_date_to[' + size + ']').bind(
                                        'dpClosed',
                                        function(e, selectedDates)
                                        {
                                            var d = selectedDates[0];
                                            if (d) {
                                                d = new Date(d);
                                                jQuery('#coupon_date_from[' + size + ']').dpSetEndDate(d.addDays(-1).asString());
                                            }
                                        }
                                    );

                                                     */
                                                });

                                                return false;
                                            });
                                            jQuery('#coupon_codes a.remove').live('click', function(){
                                                var answer = confirm("<?php _e('Delete this coupon?', 'jigoshop'); ?>")
                                                if (answer) {
                                                    jQuery('input', jQuery(this).parent().parent()).val('');
                                                    jQuery(this).parent().parent().hide();
                                                }
                                                return false;
                                            });
                                        });
                                        /* ]]> */
                    </script>
                                <?php
                                break;
                            case 'tax_rates' :
                                $_tax = new jigoshop_tax();
                                $tax_classes = $_tax->get_tax_classes();
                                $tax_rates = get_option('jigoshop_tax_rates');
                                $applied_all_states = array();
                                ?><tr>
                        <th><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><label><?php echo $value['name'] ?></label></th>
                        <td id="tax_rates">
                            <div class="taxrows">
                <?php
                $i = -1;
                if ($tax_rates && is_array($tax_rates) && sizeof($tax_rates) > 0)
                    foreach ($tax_rates as $rate) :
                        if ($rate['is_all_states']) :
                            if (in_array(get_all_states_key($rate), $applied_all_states)) :
                                continue;
                            endif;
                        endif;

                        $i++;// increment counter after check for all states having been applied

                        echo '<p class="taxrow"><select name="tax_classes[' . esc_attr( $i ) . ']" title="Tax Classes"><option value="*">' . __('Standard Rate', 'jigoshop') . '</option>';

                        if ($tax_classes)
                            foreach ($tax_classes as $class) :
                                echo '<option value="' . sanitize_title($class) . '"';

                                if ($rate['class'] == sanitize_title($class))
                                    echo 'selected="selected"';

                                echo '>' . $class . '</option>';
                            endforeach;

                        echo '</select><input type="text" class="text" value="' . esc_attr( $rate['label']  ) . '" name="tax_label[' . esc_attr( $i ) . ']" title="' . __('Online Label', 'jigoshop') . '" placeholder="' . __('Online Label', 'jigoshop') . '" maxlength="15" />';

                        echo '</select><select name="tax_country[' . esc_attr( $i ) . '][]" title="Country" multiple="multiple">';

                        if ($rate['is_all_states']) :
                            if (is_array($applied_all_states) && !in_array(get_all_states_key($rate), $applied_all_states)) :
                                $applied_all_states[] = get_all_states_key($rate);
                                jigoshop_countries::country_dropdown_options($rate['country'], '*'); //all-states
                            else :
                                continue;
                            endif;
                        else :
                            jigoshop_countries::country_dropdown_options($rate['country'], $rate['state']);
                        endif;

                        echo '</select><input type="text" class="text" value="' . esc_attr( $rate['rate']  ) . '" name="tax_rate[' . esc_attr( $i ) . ']" title="' . __('Rate', 'jigoshop') . '" placeholder="' . __('Rate', 'jigoshop') . '" maxlength="8" />% <label><input type="checkbox" name="tax_shipping[' . esc_attr( $i ) . ']" ';

                        if (isset($rate['shipping']) && $rate['shipping'] == 'yes')
                            echo 'checked="checked"';

                        echo ' /> ' . __('Apply to shipping', 'jigoshop') . '</label><label><input type="checkbox" name="tax_compound[' . esc_attr( $i ) . ']" ';

                        if (isset($rate['compound']) && $rate['compound'] == 'yes')
                            echo 'checked="checked"';

                        echo ' /> ' . __('Compound', 'jigoshop') . '</label><a href="#" class="remove button">&times;</a></p>';
                    endforeach;
                ?>
                            </div>
                            <p><a href="#" class="add button"><?php _e('+ Add Tax Rule', 'jigoshop'); ?></a></p>
                        </td>
                    </tr>
                    <script type="text/javascript">
                        /* <![CDATA[ */
                        jQuery(function() {
                            jQuery('#tax_rates a.add').live('click', function(){
                                var size = jQuery('.taxrows .taxrow').size();

                                // Add the row
                                jQuery('<p class="taxrow"> \
                                    <select name="tax_classes[' + size + ']" title="Tax Classes"> \
                                        <option value="*"><?php _e('Standard Rate', 'jigoshop'); ?></option><?php
                $tax_classes = $_tax->get_tax_classes();
                if ($tax_classes)
                    foreach ($tax_classes as $class) :
                        echo '<option value="' . sanitize_title($class) . '">' . $class . '</option>';
                    endforeach;
                ?></select><input type="text" class="text" name="tax_label[' + size + ']" title="<?php _e('Online Label', 'jigoshop'); ?>" placeholder="<?php _e('Online Label', 'jigoshop'); ?>" maxlength="15" />\
                                        </select><select name="tax_country[' + size + '][]" title="Country" multiple="multiple"><?php
                jigoshop_countries::country_dropdown_options('', '', true);
                ?></select><input type="text" class="text" name="tax_rate[' + size + ']" title="<?php _e('Rate', 'jigoshop'); ?>" placeholder="<?php _e('Rate', 'jigoshop'); ?>" maxlength="8" />%\
                                        <label><input type="checkbox" name="tax_shipping[' + size + ']" /> <?php _e('Apply to shipping', 'jigoshop'); ?></label>\
                                        <label><input type="checkbox" name="tax_compound[' + size + ']" /> <?php _e('Compound', 'jigoshop'); ?></label><a href="#" class="remove button">&times;</a>\
                                </p>').appendTo('#tax_rates div.taxrows');
                                                    return false;
                                                });
                                                jQuery('#tax_rates a.remove').live('click', function(){
                                                    var answer = confirm("<?php _e('Delete this rule?', 'jigoshop'); ?>");
                                                    if (answer) {
                                                        jQuery('input', jQuery(this).parent()).val('');
                                                        jQuery(this).parent().hide();
                                                    }
                                                    return false;
                                                });
                                            });
                                            /* ]]> */
                    </script>
                <?php
                break;
            case "shipping_options" :

                foreach (jigoshop_shipping::get_all_methods() as $method) :

                    $method->admin_options();

                endforeach;

                break;
            case "gateway_options" :

                foreach (jigoshop_payment_gateways::payment_gateways() as $gateway) :

                    $gateway->admin_options();

                endforeach;

                break;
        endswitch;
    endforeach;
    ?>
        <p class="submit"><input name="save" class="button-primary" type="submit" value="<?php _e('Save changes', 'jigoshop') ?>" /></p>
    </div>
    <script type="text/javascript">
        jQuery(function($) {
            // Tabs
            jQuery('#jigoshop-nav-tab-wrapper').show();
            jQuery('#jigoshop-nav-tab-wrapper a:first').addClass('nav-tab-active');
            jQuery('div.panel:not(div.panel:first)').hide();
            jQuery('#jigoshop-nav-tab-wrapper a').click(function(){
                jQuery('#jigoshop-nav-tab-wrapper a').removeClass('nav-tab-active');
                jQuery(this).addClass('nav-tab-active');
                jQuery('div.panel').hide();
                jQuery( jQuery(this).attr('href') ).show();

                jQuery.cookie('jigoshop_settings_tab_index', jQuery(this).index('#jigoshop-nav-tab-wrapper a'))

                return false;
            });

    <?php if (isset($_COOKIE['jigoshop_settings_tab_index']) && $_COOKIE['jigoshop_settings_tab_index'] > 0) : ?>

                    jQuery('ul.tabs li:eq(<?php echo $_COOKIE['jigoshop_settings_tab_index']; ?>) a').click();

    <?php endif; ?>

                // Countries
                jQuery('select#jigoshop_allowed_countries').change(function(){
                    if (jQuery(this).val()=="specific") {
                        jQuery(this).parent().parent().next('tr.multi_select_countries').show();
                    } else {
                        jQuery(this).parent().parent().next('tr.multi_select_countries').hide();
                    }
                }).change();

                // permalink double save hack
                $.get('<?php echo admin_url('options-permalink.php') ?>');

            });
    </script>
    <?php
    flush_rewrite_rules();
}

/**
 * When all states are selected, filter based on country and tax class. This method
 * creates the array key for such a filter.
 *
 * @param array $tax_rate the tax rates array
 * @return string country code and tax class concatenated
 */
function get_all_states_key($tax_rate) {
    return $tax_rate['country'] . $tax_rate['class'];
}

/**
 * Prints an updated notice
 */
function jigoshop_settings_updated_notice() {
    echo '<div id="message" class="updated"><p><strong>' . __('Your settings have been saved.', 'jigoshop') . '</strong></p></div>';
}

/**
 * Settings page
 *
 * Handles the display of the settings page in admin.
 *
 * @since 		1.0
 * @usedby 		jigoshop_admin_menu2()
 */
function jigoshop_settings() {
    global $jigoshop_options_settings;
    ?>
    <script type="text/javascript" src="<?php echo jigoshop::assets_url(); ?>/assets/js/bootstrap-tooltip.min.js"></script>
    <div class="wrap jigoshop">
        <div class="icon32 icon32-jigoshop-settings" id="icon-jigoshop"><br/></div>
        <h2><?php _e('General Settings', 'jigoshop'); ?></h2>
        <?php do_action( 'jigoshop_admin_settings_notices' ); ?>
        <form method="post" id="mainform" action="">
        <?php wp_nonce_field( 'jigoshop-update-settings', '_jigoshop_csrf' ); ?>
    <?php jigoshop_admin_fields($jigoshop_options_settings); ?>
            <input name="submitted" type="hidden" value="yes" />
        </form>
    </div>
    <?php
}
