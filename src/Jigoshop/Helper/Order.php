<?php

namespace Jigoshop\Helper;

use Jigoshop\Entity\Order\Status;

class Order
{
	public static function getStatus(\Jigoshop\Entity\Order $order)
	{
		$statuses = $order->getStatuses();
		$status = $order->getStatus();
		if (!isset($statuses[$status])) {
			$status = Status::CREATED;
		}
		$text = $statuses[$status];
		return sprintf('<mark class="%s">%s</mark>', $status, $text);
	}
}
