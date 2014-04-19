<?php

namespace Jigoshop\Core;

use Jigoshop\Service\OrderServiceInterface;

class Cron
{
	/** @var \Jigoshop\Core\Wordpress */
	private $wordpress;
	/** @var Options */
	private $options;
	/** @var OrderServiceInterface */
	private $service;

	public function __construct(Wordpress $wordpress, Options $options, OrderServiceInterface $service)
	{
		$this->wordpress = $wordpress;
		$this->options = $options;
		$this->service = $service;
		$this->_scheduleEvents();

		$wordpress->addAction('jigoshop\\cron\\pending_orders', array($this, '_updatePendingOrders'));
		$wordpress->addAction('jigoshop\\cron\\processing_orders', array($this, '_completeProcessingOrders'));
	}

	public static function clear()
	{
		$wordpress = new Wordpress();
		$wordpress->clearScheduledHook('jigoshop\\cron\\pending_orders');
		$wordpress->clearScheduledHook('jigoshop\\cron\\processing_orders');
	}

	/** Schedules order processing events if not scheduled already. */
	private function _scheduleEvents()
	{
		$time = time();
		if(!$this->wordpress->nextScheduled('jigoshop\\cron\\pending_orders'))
		{
			$this->wordpress->scheduleEvent($time, 'daily', 'jigoshop\\cron\\pending_orders');
		}

		if(!$this->wordpress->nextScheduled('jigoshop\\cron\\processing_orders'))
		{
			$this->wordpress->scheduleEvent($time, 'daily', 'jigoshop\\cron\\processing_orders');
		}
	}

	/**
	 * Moves old orders to "On Hold" status.
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _updatePendingOrders()
	{
		if($this->options->get('reset_pending_orders') == 'yes')
		{
			$this->wordpress->addFilter('posts_where', array($this, '_ordersFilter'));
			$query = new \WP_Query(array(
				'post_status' => 'publish',
				'post_type' => 'shop_order',
				'shop_order_status' => 'pending',
				'suppress_filters' => false,
				'fields' => 'ids',
			));
			$orders = $this->service->findByQuery($query);

			$this->wordpress->removeFilter('posts_where', array($this, '_ordersFilter'));
			// TODO: Update function to call
			$this->wordpress->removeAction('order_status_pending_to_on-hold', 'jigoshop_processing_order_customer_notification');

			// TODO: Proper status handling
			foreach($orders as $order)
			{
				/** @var $order \Jigoshop\Entity\Order */
				$order->updateStatus('on-hold', __('Archived due to order being in pending state for a month or longer.', 'jigoshop'));
			}

			// TODO: Proper action naming
			// TODO: Update function to call
			$this->wordpress->addAction('order_status_pending_to_on-hold', 'jigoshop_processing_order_customer_notification');
		}
	}

	/**
	 * Marks old, but still in "Processing" status orders as completed.
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _completeProcessingOrders()
	{
		if($this->options->get('complete_processing_orders') == 'yes')
		{
			$this->wordpress->addFilter('posts_where', array($this, '_ordersFilter'));
			$query = new \WP_Query(array(
				'post_status' => 'publish',
				'post_type' => 'shop_order',
				'shop_order_status' => 'processing',
				'suppress_filters' => false,
				'fields' => 'ids',
			));
			$orders = $this->service->findByQuery($query);

			$this->wordpress->removeFilter('posts_where', array($this, '_ordersFilter'));
			// TODO: Update function to call
			$this->wordpress->removeAction('order_status_completed', 'jigoshop_processing_order_customer_notification');

			// TODO: Proper status handling
			foreach($orders as $order)
			{
				/** @var $order \Jigoshop\Entity\Order */
				$order->updateStatus('completed', __('Completed due to order being in processing state for a month or longer.', 'jigoshop'));
			}

			// TODO: Proper action naming
			// TODO: Update function to call
			$this->wordpress->addAction('order_status_completed', 'jigoshop_processing_order_customer_notification');
		}
	}

	/**
	 * @param string $when Base query.
	 * @return string Query for orders older than 30 days.
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _ordersFilter($when = '')
	{
		return $when.$this->wordpress->getWPDB()->prepare(' AND post_date < %s', date('Y-m-d', time()-30*24*3600));
	}
}