<?php

namespace Jigoshop\Core;

use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class Cron
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var OrderServiceInterface */
	private $service;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $service)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->service = $service;
		$this->_scheduleEvents();

		$wp->addAction('jigoshop\\cron\\pending_orders', array($this, 'updatePendingOrders'));
		$wp->addAction('jigoshop\\cron\\processing_orders', array($this, 'completeProcessingOrders'));
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
		if (!$this->wp->nextScheduled('jigoshop\\cron\\pending_orders')) {
			$this->wp->scheduleEvent($time, 'daily', 'jigoshop\\cron\\pending_orders');
		}

		if (!$this->wp->nextScheduled('jigoshop\\cron\\processing_orders')) {
			$this->wp->scheduleEvent($time, 'daily', 'jigoshop\\cron\\processing_orders');
		}
	}

	/**
	 * Moves old orders to "On Hold" status.
	 *
	 * @internal
	 */
	public function updatePendingOrders()
	{
		if ($this->options->get('reset_pending_orders') == 'yes') {
			$orders = $this->service->findOldPending();

			// TODO: Disable notification of the user
//			$this->wp->removeAction('jigoshop\\order\\status\\pending_to_on-hold', 'jigoshop_processing_order_customer_notification');

			foreach ($orders as $order) {
				/** @var $order \Jigoshop\Entity\Order */
				$order->updateStatus('on-hold', \__('Archived due to order being in pending state for a month or longer.', 'jigoshop'));
			}

			// TODO: Enable notification of the user
//			$this->wp->addAction('jigoshop\\order\\status\\pending_to_on-hold', 'jigoshop_processing_order_customer_notification');
		}
	}

	/**
	 * Marks old, but still in "Processing" status orders as completed.
	 *
	 * @internal
	 */
	public function completeProcessingOrders()
	{
		if ($this->options->get('complete_processing_orders') == 'yes') {
			$orders = $this->service->findOldProcessing();

			// TODO: Disable notification of the user
//			$this->wp->removeAction('jigoshop\\order\\status\\completed', 'jigoshop_processing_order_customer_notification');

			foreach ($orders as $order) {
				/** @var $order \Jigoshop\Entity\Order */
				$order->updateStatus('completed', \__('Completed due to order being in processing state for a month or longer.', 'jigoshop'));
			}

			// TODO: Enable notification of the user
//			$this->wp->addAction('jigoshop\\order\\status\\completed', 'jigoshop_processing_order_customer_notification');
		}
	}
}