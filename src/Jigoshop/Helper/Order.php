<?php

namespace Jigoshop\Helper;

use Jigoshop\Entity\Customer\Guest;
use Jigoshop\Entity\Order\Status;

class Order
{
	public static function getStatus(\Jigoshop\Entity\Order $order)
	{
		$statuses = Status::getStatuses();
		$status = $order->getStatus();
		if (!isset($statuses[$status])) {
			$status = Status::CREATED;
		}
		$text = $statuses[$status];
		return sprintf('<mark class="%s">%s</mark>', $status, $text);
	}

	public static function getUserLink($customer)
	{
		if ($customer instanceof Guest) {
			return $customer->getName();
		}

		return sprintf('<a href="%s">%s</a>', get_edit_user_link($customer->getId()), $customer->getName());
	}
}
