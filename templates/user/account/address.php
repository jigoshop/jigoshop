<?php
use Jigoshop\Helper\Country;

/**
 * @var $address \Jigoshop\Entity\Customer\Address
 */
?>
<dl class="dl-horizontal clearfix address">
	<dt><?php echo __('Name', 'jigoshop'); ?></dt>
	<dd><?php echo $address->getName(); ?>&nbsp;</dd>
	<dt><?php echo __('Address', 'jigoshop'); ?></dt>
	<dd><?php echo $address->getAddress(); ?>&nbsp;</dd>
	<dt><?php echo __('City', 'jigoshop'); ?></dt>
	<dd><?php echo $address->getCity(); ?>&nbsp;</dd>
	<dt><?php echo __('Postcode', 'jigoshop'); ?></dt>
	<dd><?php echo $address->getPostcode(); ?>&nbsp;</dd>
	<dt><?php echo __('State/province', 'jigoshop'); ?></dt>
	<dd><?php echo Country::getStateName($address->getCountry(), $address->getState()); ?>&nbsp;</dd>
	<dt><?php echo __('Country', 'jigoshop'); ?></dt>
	<dd><?php echo Country::getName($address->getCountry()); ?>&nbsp;</dd>
	<?php if ($address->getPhone()): ?>
		<dt><?php echo __('Phone', 'jigoshop'); ?></dt>
		<dd><?php echo $address->getPhone(); ?>&nbsp;</dd>
	<?php endif; ?>
	<?php if ($address->getEmail()): ?>
		<dt><?php echo __('Email', 'jigoshop'); ?></dt>
		<dd><?php echo $address->getEmail(); ?>&nbsp;</dd>
	<?php endif; ?>
</dl>
