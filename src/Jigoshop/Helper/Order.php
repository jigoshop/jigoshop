<?php

namespace Jigoshop\Helper;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Customer\Guest;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Frontend\Pages;

class Order
{
	/** @var Options */
	private static $options;

	/**
	 * @param Options $options Options object.
	 */
	public static function setOptions($options)
	{
		self::$options = $options;
	}

	public static function getStatus(\Jigoshop\Entity\Order $order)
	{
		$statuses = Status::getStatuses();
		$status = $order->getStatus();
		if (!isset($statuses[$status])) {
			$status = Status::PENDING;
		}
		$text = $statuses[$status];
		return sprintf('<mark class="%s" title="%s">%s</mark>', $status, $text, $text);
	}

	public static function getUserLink($customer)
	{
		if ($customer instanceof Guest) {
			return $customer->getName();
		}

		return sprintf('<a href="%s">%s</a>', get_edit_user_link($customer->getId()), $customer->getName());
	}

	/**
	 * @param $order \Jigoshop\Entity\Order
	 * @return string Cancel order link.
	 */
	public static function getCancelLink($order)
	{
		$args = array(
			'action' => 'cancel_order',
			'nonce' => wp_create_nonce('cancel_order'),
			'id' => $order->getId(),
			'key' => $order->getKey(),
		);
		$url = add_query_arg($args, get_permalink(self::$options->getPageId(Pages::CART)));

		return apply_filters('jigoshop_get_cancel_order', $url);
	}

	/**
	 * @param $key string Item key.
	 * @return string Link to remove item.
	 */
	public static function getRemoveLink($key)
	{
		return add_query_arg(array('action' => 'remove-item', 'item' => $key));
	}

	/**
	 * @param $order \Jigoshop\Entity\Order Order to generate link for.
	 * @return string Payment link.
	 */
	public static function getPayLink($order)
	{
		return add_query_arg(array('key' => $order->getKey()), Api::getEndpointUrl('pay', $order->getId(), get_permalink(self::$options->getPageId(Pages::CHECKOUT))));
	}
}
