<?php
/**
 * @var $user WP_User User instance.
 * @var $customer jigoshop_user Customer data.
 */
?>
<h2><?php _e('Jigoshop profile', 'jigoshop'); ?></h2>
<table class="form-table" style="width: 50%; float: left;">
	<caption><?php _e('Billing address', 'jigoshop'); ?></caption>
	<tbody>
	<tr>
		<th scope="row"><?php _e('First name', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_first_name]" value="<?php echo $customer->getBillingFirstName(); ?>" class="regular-text" /></td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Last name', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_last_name]" value="<?php echo $customer->getBillingLastName(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Company', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_company]" value="<?php echo $customer->getBillingCompany(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scioe="row"><?php _e('EU VAT Number', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_euvatno]" value="<?php echo $customer->getBillingEuvatno(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Address 1', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_address_1]" value="<?php echo $customer->getBillingAddress1(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Address 2', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_address_2]" value="<?php echo $customer->getBillingAddress2(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('City', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_city]" value="<?php echo $customer->getBillingCity(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Postcode', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_postcode]" value="<?php echo $customer->getBillingPostcode(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Country', 'jigoshop'); ?></th>
		<td><?php jigoshop_render('admin/user-profile/country_dropdown', array(
			'country' => $customer->getBillingCountry(),
			'state' => $customer->getBillingState(),
			'name' => 'billing_country',
		)); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Email', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_email]" value="<?php echo $customer->getBillingEmail(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Phone', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[billing_phone]" value="<?php echo $customer->getBillingPhone(); ?>" class="regular-text" /> </td>
	</tr>
	</tbody>
</table>
<table class="form-table" style="width: 50%; float: left; clear: none;">
	<caption><?php _e('Shipping address', 'jigoshop'); ?></caption>
	<tbody>
	<tr>
		<th scope="row"><?php _e('First name', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[shipping_first_name]" value="<?php echo $customer->getShippingFirstName(); ?>" class="regular-text" /></td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Last name', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[shipping_last_name]" value="<?php echo $customer->getShippingLastName(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Company', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[shipping_company]" value="<?php echo $customer->getShippingCompany(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Address 1', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[shipping_address_1]" value="<?php echo $customer->getShippingAddress1(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Address 2', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[shipping_address_2]" value="<?php echo $customer->getShippingAddress2(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('City', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[shipping_city]" value="<?php echo $customer->getShippingCity(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Postcode', 'jigoshop'); ?></th>
		<td><input type="text" name="jigoshop[shipping_postcode]" value="<?php echo $customer->getShippingPostcode(); ?>" class="regular-text" /> </td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Country', 'jigoshop'); ?></th>
		<td><?php jigoshop_render('admin/user-profile/country_dropdown', array(
				'country' => $customer->getShippingCountry(),
				'state' => $customer->getShippingState(),
				'name' => 'shipping_country',
			)); ?></td>
	</tr>
	</tbody>
</table>
<span style="clear: both; display: block;"></span>
