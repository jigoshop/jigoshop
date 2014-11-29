<?php
use Jigoshop\Helper\Country;

/**
 * @var $address \Jigoshop\Entity\Customer\Address
 */
?>
<dl class="dl-horizontal clearfix">
	<dt><?php echo __('Name', 'jigoshop'); ?></dt>
	<dd><?php echo $address->getName(); ?></dd>
	<dt><?php echo __('Address', 'jigoshop'); ?></dt>
	<dd><?php echo $address->getAddress(); ?></dd>
	<dt><?php echo __('City', 'jigoshop'); ?></dt>
	<dd><?php echo $address->getCity(); ?></dd>
	<dt><?php echo __('Postcode', 'jigoshop'); ?></dt>
	<dd><?php echo $address->getPostcode(); ?></dd>
	<dt><?php echo __('State/province', 'jigoshop'); ?></dt>
	<dd><?php echo Country::getStateName($address->getCountry(), $address->getState()); ?></dd>
	<dt><?php echo __('Country', 'jigoshop'); ?></dt>
	<dd><?php echo Country::getName($address->getCountry()); ?></dd>
</dl>
