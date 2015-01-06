<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order;
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
			$scripts->add('jigoshop.flot.pie', JIGOSHOP_URL.'/assets/js/flot/jquery.flot.pie.min.js', array('jquery', 'jigoshop.flot'));
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

		$reports = $this;
		$boxes = $this->wp->applyFilters('jigoshop\admin\reports\boxes', array(
			function() use ($reports) {
				$reports->sales();
			},
			function() use ($reports) {
				$reports->topEarners();
			},
			function() use ($reports) {
				$reports->mostSold();
			},
			function() use ($reports) {
				$reports->totalNewCustomers();
			},
			function() use ($reports) {
				$reports->totalOrders();
			},
			function() use ($reports) {
				$reports->totalSales();
			},
		));

		Render::output('admin/reports', array(
			'messages' => $this->messages,
			'orders' => $this->orders,
			'boxes' => $boxes,
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

	public function topEarners()
	{
		$sales = array();
		$chart = array();
		$totalSales = 0.0;
		$totalChart = 0;

		foreach ($this->orders as $order) {
			/** @var $order Order */
			foreach ($order->getItems() as $item) {
				/** @var $item Order\Item */
				if (!isset($sales[$item->getProduct()->getId()])) {
					$sales[$item->getName()] = array(
						'product' => $item->getProduct(),
						'value' => 0.0,
					);
					$chart[$item->getName()] = 0;
				}

				$sales[$item->getName()]['value'] += $item->getCost();
				$totalSales += $item->getCost();
				$chart[$item->getName()] += $item->getQuantity();
				$totalChart += $item->getQuantity();
			}
		}

		$data = array();
		foreach ($chart as $product => $quantity) {
			$data[] = array(
				'label' => $product,
				'data' => round($quantity/$totalChart, 3)*100,
			);
		}

		Render::output('admin/reports/top_earners', array(
			'orders' => $this->orders,
			'sales' => $sales,
			'chart' => $data,
			'total_sales' => $totalSales,
		));
	}

	public function mostSold()
	{
		$sales = array();
		$chart = array();
		$totalChart = 0;

		foreach ($this->orders as $order) {
			/** @var $order Order */
			foreach ($order->getItems() as $item) {
				/** @var $item Order\Item */
				if (!isset($sales[$item->getProduct()->getId()])) {
					$sales[$item->getName()] = array(
						'product' => $item->getProduct(),
						'value' => 0.0,
					);
					$chart[$item->getName()] = 0;
				}

				$sales[$item->getName()]['value'] += $item->getQuantity();
				$chart[$item->getName()] += $item->getQuantity();
				$totalChart += $item->getQuantity();
			}
		}

		$data = array();
		foreach ($chart as $product => $quantity) {
			$data[] = array(
				'label' => $product,
				'data' => round($quantity/$totalChart, 3)*100,
			);
		}

		Render::output('admin/reports/most_sold', array(
			'orders' => $this->orders,
			'sales' => $sales,
			'chart' => $data,
			'total_sales' => $totalChart,
		));
	}

	public function totalNewCustomers()
	{
		$after = date('Y-m-d', $this->startDate);
		$before = date('Y-m-d', strtotime('+1 day', $this->endDate));

		$restriction = function($query) use ($before, $after){
			/** @var $wpdb \wpdb */
			global $wpdb;
			/** @var $query \WP_User_Query */
			$query->query_where .= $wpdb->prepare(" AND {$wpdb->users}.user_registered BETWEEN %s AND %s", array($before, $after));
		};

		add_action('pre_user_query', $restriction);
		$query = new \WP_User_Query(array(
			'fields' => 'ids',
			'role' => 'customer',
		));
		$totalCustomers = $query->get_total();
		remove_action('pre_user_query', $restriction);

		Render::output('admin/reports/total_new_customers', array(
			'orders' => $this->orders,
			'total_customers' => $totalCustomers,
		));
	}

	public function totalOrders()
	{
		Render::output('admin/reports/total_orders', array(
			'orders' => $this->orders,
			'total_orders' => count($this->orders),
		));
	}

	public function totalSales()
	{
		Render::output('admin/reports/total_sales', array(
			'orders' => $this->orders,
			'total_sales' => array_sum(array_map(function($order){
				/** @var $order Order */
				return $order->getTotal();
			}, $this->orders)),
		));
	}
}
