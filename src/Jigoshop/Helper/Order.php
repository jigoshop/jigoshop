<?php

namespace Jigoshop\Helper;

use Jigoshop\Entity\Order\Status;

class Order
{
	public static function getStatus(\Jigoshop\Entity\Order $order)
	{
		$statuses = $order->getStatuses();
		$status = isset($statuses[$order->getStatus()]) ? $statuses[$order->getStatus()] : $statuses[Status::CREATED];
		return sprintf('<mark class="status-%s">%s</mark>', sanitize_title($order->getStatus()), $status);
	}
}
