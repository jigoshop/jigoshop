<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

/**
 * Jigoshop dashboard.
 *
 * @package Jigoshop\Admin
 * @author Amadeusz Starzykiewicz
 */
class Dashboard implements PageInterface
{
	const NAME = 'jigoshop';

	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Service\OrderServiceInterface */
	private $orderService;
	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $productService;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, ProductServiceInterface $productService, Styles $styles,
		Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->productService = $productService;

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles, $scripts) {
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php', 'options.php'))) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, array('toplevel_page_'.Dashboard::NAME, 'options'))) {
				return;
			}

			$styles->add('jigoshop.admin.dashboard', JIGOSHOP_URL.'/assets/css/admin/dashboard.css');
			$scripts->add('jigoshop.flot', JIGOSHOP_URL.'/assets/js/flot/jquery.flot.min.js', array('jquery'));
			$scripts->add('jigoshop.flot.time', JIGOSHOP_URL.'/assets/js/flot/jquery.flot.time.min.js', array('jquery', 'jigoshop.flot'));
		});
	}

	/** @return string Title of page. */
	public function getTitle()
	{
		return __('Dashboard', 'jigoshop');
	}

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return Admin::MENU;
	}

	/** @return string Required capability to view the page. */
	public function getCapability()
	{
		return 'manage_jigoshop';
	}

	/** @return string Menu slug. */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/** Displays the page. */
	public function display()
	{
		$this->wp->wpEnqueueScript('common');
		$this->wp->wpEnqueueScript('wp-lists');
		$this->wp->wpEnqueueScript('postbox');

		$this->wp->addMetaBox('jigoshop_dashboard_right_now', __('<span>Shop</span> Content', 'jigoshop'), array($this, 'rightNow'), 'jigoshop', 'side', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_recent_orders', __('<span>Recent</span> Orders', 'jigoshop'), array($this, 'recentOrders'), 'jigoshop', 'side', 'core');
		if ($this->options->get('products.manage_stock')) {
			$this->wp->addMetaBox('jigoshop_dashboard_stock_report', __('<span>Stock</span> Report', 'jigoshop'), array($this, 'stockReport'), 'jigoshop', 'side', 'core');
		}
		$this->wp->addMetaBox('jigoshop_dashboard_monthly_report', __('<span>Monthly</span> Report', 'jigoshop'), array($this, 'monthlyReport'), 'jigoshop', 'normal', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_recent_reviews', __('<span>Recent</span> Reviews', 'jigoshop'), array($this, 'recentReviews'), 'jigoshop', 'normal', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_latest_news', __('<span>Latest</span> News', 'jigoshop'), array($this, 'latestNews'), 'jigoshop', 'normal', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_useful_links', __('<span>Useful</span> Links', 'jigoshop'), array($this, 'usefulLinks'), 'jigoshop', 'normal', 'core');

		$submenu = $this->wp->getSubmenu();
		Render::output('admin/dashboard', array(
			'submenu' => $submenu,
		));
	}

	/**
	 * Displays "Right Now" meta box.
	 */
	public function rightNow()
	{
		$counts = $this->wp->wpCountPosts(Types::PRODUCT);
		$productCount = $counts->publish;
		$categoryCount = $this->wp->wpCountTerms(Types::PRODUCT_CATEGORY);
		$tagCount = $this->wp->wpCountTerms(Types::PRODUCT_TAG);
		$attributesCount = $this->productService->countAttributes();
		$counts = $this->wp->wpCountPosts(Types::ORDER);
		$pendingCount = $counts->{Order\Status::PENDING};
		$onHoldCount = $counts->{Order\Status::ON_HOLD};
		$processingCount = $counts->{Order\Status::PROCESSING};
		$completedCount = $counts->{Order\Status::COMPLETED};
		$cancelledCount = $counts->{Order\Status::CANCELLED};
		$refundedCount = $counts->{Order\Status::REFUNDED};

		Render::output('admin/dashboard/rightNow', array(
			'productCount' => $productCount,
			'categoryCount' => $categoryCount,
			'tagCount' => $tagCount,
			'attributesCount' => $attributesCount,
			'pendingCount' => $pendingCount,
			'onHoldCount' => $onHoldCount,
			'processingCount' => $processingCount,
			'completedCount' => $completedCount,
			'cancelledCount' => $cancelledCount,
			'refundedCount' => $refundedCount,
		));
	}

	/**
	 * Displays "Recent Orders" meta box.
	 */
	public function recentOrders()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		// TODO: Replace \WP_Query in order to make Jigoshop testable
		$statuses = Order\Status::getStatuses();
		unset($statuses[Order\Status::CANCELLED], $statuses[Order\Status::REFUNDED]);
		$orders = $this->orderService->findByQuery(new \WP_Query(array(
			'numberposts' => 10,
			'orderby' => 'post_date',
			'order' => 'DESC',
			'post_type' => Types::ORDER,
			'post_status' => array_keys($statuses),
		)));

		Render::output('admin/dashboard/recentOrders', array(
			'orders' => $orders,
		));
	}

	/**
	 * Displays "Stock Report" meta box.
	 */
	public function stockReport()
	{
		$lowStockThreshold = $this->options->get('advanced.low_stock_threshold', 2);
		$notifyOufOfStock = $this->options->get('advanced.notify_out_of_stock', true);
		$number = $this->options->get('advanced.dashboard_stock_number', 5);
		$outOfStock = array();

		if ($notifyOufOfStock) {
			$outOfStock = $this->productService->findOutOfStock($number);
		}

		$lowStock = $this->productService->findLowStock($lowStockThreshold, $number);

		Render::output('admin/dashboard/stockReport', array(
			'notifyOutOfStock' => $notifyOufOfStock,
			'outOfStock' => $outOfStock,
			'lowStock' => $lowStock,
		));
	}

	/**
	 * Displays "Monthly Report" meta box.
	 */
	public function monthlyReport()
	{
		$currentMonth = intval(date('m'));
		$currentYear = intval(date('Y'));
		$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : $currentMonth;
		$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;

		$nextYear = ($selectedMonth == 12) ? $selectedYear + 1 : $selectedYear;
		$nextMonth = ($selectedMonth == 12) ? 1 : $selectedMonth + 1;
		$previousYear = ($selectedMonth == 1) ? $selectedYear - 1 : $selectedYear;
		$previousMonth = ($selectedMonth == 1) ? 12 : $selectedMonth - 1;

		$orders = $this->orderService->findFromMonth($selectedMonth, $selectedYear);
		$days = range(strtotime($selectedYear.'-'.$selectedMonth.'-01'), time(), 24 * 3600);
		$orderAmountsData = $orderCountsData = array_fill_keys($days, 0);
		$orderAmounts = $orderCounts = array();

		foreach ($orders as $order) {
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
		Render::output('admin/dashboard/monthlyReport', array(
			'orders' => $orders,
			'selectedMonth' => $selectedMonth,
			'selectedYear' => $selectedYear,
			'currentMonth' => $currentMonth,
			'currentYear' => $currentYear,
			'nextMonth' => $nextMonth,
			'nextYear' => $nextYear,
			'previousMonth' => $previousMonth,
			'previousYear' => $previousYear,
			'orderCounts' => $orderCounts,
			'orderAmounts' => $orderAmounts,
		));
	}

	/**
	 * Displays "Recent Reviews" meta box.
	 */
	public function recentReviews()
	{
		$wpdb = $this->wp->getWPDB();
		/** @noinspection PhpUnusedLocalVariableInspection */
		$comments = $wpdb->get_results("SELECT *, SUBSTRING(comment_content,1,100) AS comment_excerpt
				FROM $wpdb->comments
				LEFT JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
				WHERE comment_approved = '1'
				AND comment_type = ''
				AND post_password = ''
				AND post_type = 'product'
				ORDER BY comment_date_gmt DESC
				LIMIT 5");

		Render::output('admin/dashboard/recentReviews', array(
			'comments' => $comments,
		));
	}

	/**
	 * Displays "Latest News" meta box.
	 */
	public function latestNews()
	{
		if (file_exists(ABSPATH.WPINC.'/class-simplepie.php')) {
			include_once(ABSPATH.WPINC.'/class-simplepie.php');

			$wp = $this->wp;
			$rss = $wp->fetchFeed('http://www.jigoshop.com/feed');
			$items = array();

			if (!$wp->isWpError($rss)) {
				$maxItems = $rss->get_item_quantity(5);
				$rssItems = $rss->get_items(0, $maxItems);

				if ($maxItems > 0) {
					$items = array_map(function ($item) use ($wp) {
						/** @var $item \SimplePie_Item */
						$date = $item->get_date('U');

						return array(
							'title' => $wp->getHelpers()->wptexturize($item->get_title()),
							'link' => $item->get_permalink(),
							'date' => (abs(time() - $date)) < 86400 ? sprintf(__('%s ago', 'jigoshop'), $wp->humanTimeDiff($date)) : date(__('F jS Y', 'jigoshop'), $date),
						);
					}, $rssItems);
				}
			}

			Render::output('admin/dashboard/latestNews', array(
				'items' => $items,
			));
		}
	}

	/**
	 * Displays "Useful Links" meta box.
	 */
	public function usefulLinks()
	{
		Render::output('admin/dashboard/usefulLinks', array());
	}
}
