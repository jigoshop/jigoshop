<?php

namespace Jigoshop\Core;

use Jigoshop\Service\Order as OrderService;

class Cron
{
	private $_options;
	private $_service;

	public function __construct(Options $options, OrderService $service)
	{
		$this->_options = $options;
		$this->_service = $service;
		$this->_scheduleEvents();

		add_action('jigoshop_cron_pending_orders', array($this, '_updatePendingOrders'));
		add_action('jigoshop_cron_processing_orders', array($this, '_completeProcessingOrders'));
	}

	public static function clear()
	{
		wp_clear_scheduled_hook('jigoshop_cron_pending_orders');
		wp_clear_scheduled_hook('jigoshop_cron_processing_orders');
	}

	/** Schedules order processing events if not scheduled already. */
	private function _scheduleEvents()
	{
		$time = time();
		if(!wp_next_scheduled('jigoshop_cron_pending_orders'))
		{
			wp_schedule_event($time, 'daily', 'jigoshop_cron_pending_orders');
		}

		if(!wp_next_scheduled('jigoshop_cron_processing_orders'))
		{
			wp_schedule_event($time, 'daily', 'jigoshop_cron_processing_orders');
		}
	}

	/**
	 * Moves old orders to "On Hold" status.
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _updatePendingOrders()
	{
		if($this->_options->get('reset_pending_orders') == 'yes')
		{
			add_filter('posts_where', array($this, '_ordersFilter'));
			$query = new \WP_Query(array(
				'post_status' => 'publish',
				'post_type' => 'shop_order',
				'shop_order_status' => 'pending',
				'suppress_filters' => false,
				'fields' => 'ids',
			));
			$orders = $this->_service->findByQuery($query);

			remove_filter('posts_where', array($this, '_ordersFilter'));
			remove_action('order_status_pending_to_on-hold', 'jigoshop_processing_order_customer_notification');

			foreach($orders as $order)
			{
				/** @var $order \Jigoshop\Order */
				$order->updateStatus('on-hold', __('Archived due to order being in pending state for a month or longer.', 'jigoshop'));
			}

			add_action('order_status_pending_to_on-hold', 'jigoshop_processing_order_customer_notification');
		}
	}

	/**
	 * Marks old, but still in "Processing" status orders as completed.
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _completeProcessingOrders()
	{
		if($this->_options->get('complete_processing_orders') == 'yes')
		{
			add_filter('posts_where', array($this, '_ordersFilter'));
			$query = new \WP_Query(array(
				'post_status' => 'publish',
				'post_type' => 'shop_order',
				'shop_order_status' => 'processing',
				'suppress_filters' => false,
				'fields' => 'ids',
			));
			$orders = $this->_service->findByQuery($query);

			remove_filter('posts_where', array($this, '_ordersFilter'));
			remove_action('order_status_completed', 'jigoshop_processing_order_customer_notification');

			foreach($orders as $order)
			{
				/** @var $order \Jigoshop\Order */
				$order->updateStatus('completed', __('Completed due to order being in processing state for a month or longer.', 'jigoshop'));
			}

			add_action('order_status_completed', 'jigoshop_processing_order_customer_notification');
		}
	}

	/**
	 * @param string $when Base query.
	 * @return string Query for orders older than 30 days.
	 */
	private function _ordersFilter($when = '')
	{
		/** @var $wpdb \WPDB */
		global $wpdb;
		return $when.$wpdb->prepare(' AND post_date < %s', date('Y-m-d', time()-30*24*3600));
	}
}