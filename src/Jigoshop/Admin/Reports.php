<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

/**
 * Jigoshop reports admin page.
 *
 * @package Jigoshop\Admin
 */
class Reports implements PageInterface
{
	const NAME = 'jigoshop_reports';

	/** @var Wordpress */
	private $wp;
	/** @var Messages */
	private $messages;
	/** @var OrderServiceInterface */
	private $orderService;
	/** @var array List of currently visible orders */
	private $orders;
	/** @var int Start date of the report */
	private $startDate;
	/** @var int End date of the report */
	private $endDate;

	public function __construct(Wordpress $wp, Messages $messages, OrderServiceInterface $orderService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->messages = $messages;
		$this->orderService = $orderService;

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles, $scripts) {
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php', 'options.php'))) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if ($screen->base != 'jigoshop_page_'.Reports::NAME) {
				return;
			}

			$scripts->add('jigoshop.flot', JIGOSHOP_URL.'/assets/js/flot/jquery.flot.min.js', array('jquery'));
			$scripts->add('jigoshop.flot.time', JIGOSHOP_URL.'/assets/js/flot/jquery.flot.time.min.js', array('jquery', 'jigoshop.flot'));
		});
	}

	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Reports', 'jigoshop');
	}

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return Admin::MENU;
	}

	/**
	 * @return string Required capability to view the page.
	 */
	public function getCapability()
	{
		return 'view_jigoshop_reports';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		$this->wp->wpEnqueueScript('common');
		$this->wp->wpEnqueueScript('wp-lists');
		$this->wp->wpEnqueueScript('postbox');

		$startDate = $this->startDate = !empty($_POST['start_date'])
			? strtotime($_POST['start_date'])
			: strtotime(date('Ymd', strtotime(date('Ym', time()).'01')));

		$endDate = $this->endDate = !empty($_POST['end_date'])
			? strtotime($_POST['end_date'])
			: strtotime(date('Ymd', time()));

		$restriction = function( $where = '' ) use ($startDate, $endDate) {
			$after = date('Y-m-d H:i:s', $startDate);
			$before = date('Y-m-d H:i:s', $endDate);

			$where .= " AND post_date >= '$after'";
			$where .= " AND post_date <= '$before'";

			return $where;
		};

		$this->wp->addFilter('posts_where', $restriction);
		$query = new \WP_Query(array(
			'post_status' => array(Status::COMPLETED),
			'post_type' => Types::ORDER,
			'order' => 'ASC',
			'orderby' => 'post_date',
			'posts_per_page' => -1
		));
		$this->orders = $this->orderService->findByQuery($query);
		$this->wp->removeFilter('posts_where', $restriction);

		$this->wp->addMetaBox('jigoshop_reports_sales', __('Sales', 'jigoshop'), array($this, 'sales'), 'jigoshop-reports', 'normal', 'core');
//		$this->wp->addMetaBox('jigoshop_reports_', __('', 'jigoshop'), array($this, ''), 'jigoshop-reports', 'normal', 'core');

		Render::output('admin/reports', array(
			'messages' => $this->messages,
			'orders' => $this->orders,
			'start_date' => $startDate,
			'end_date' => $endDate,
		));
	}

	public function sales()
	{
		if ($this->endDate > $this->startDate + 24*3600) {
			$days = range($this->startDate, $this->endDate, 24 * 3600);
		} else {
			$days = array($this->startDate);
		}

		$orderAmountsData = $orderCountsData = array_fill_keys($days, 0);
		$orderAmounts = $orderCounts = array();

		foreach ($this->orders as $order) {
			/** @var $order Order */
			$day = strtotime($order->getCreatedAt()->format('Y-m-d'));
			$orderCountsData[$day] += 1;
			$orderAmountsData[$day] += $order->getSubtotal() + $order->getShippingPrice();
		}

		foreach ($orderCountsData as $day => $value) {
			$orderCounts[] = array($day, $value);
		}
		foreach ($orderAmountsData as $day => $value) {
			$orderAmounts[] = array($day, $value);
		}

		unset($orderAmountsData, $orderCountsData);
		Render::output('admin/reports/sales', array(
			'orders' => $this->orders,
			'orderCounts' => $orderCounts,
			'orderAmounts' => $orderAmounts,
		));
	}
}
