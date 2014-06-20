<?php

namespace Jigoshop\Admin;

use Jigoshop\Core\Options;
use Jigoshop\Core\PostTypes;
use Jigoshop\Entity\Order;
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
	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Service\OrderServiceInterface */
	private $orderService;
	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $productService;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->productService = $productService;
	}

	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Dashboard', 'jigoshop');
	}

	/**
	 * @return string Required capability to view the page.
	 */
	public function getCapability()
	{
		return 'manage_jigoshop';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return 'jigoshop';
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		$this->wp->wpEnqueueScript('common');
		$this->wp->wpEnqueueScript('wp-lists');
		$this->wp->wpEnqueueScript('postbox');

		$this->wp->addMetaBox('jigoshop_dashboard_right_now', __('Right Now', 'jigoshop'), array($this, 'rightNow'), 'jigoshop', 'side', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_recent_orders', __('Recent Orders', 'jigoshop'), array($this, 'recentOrders'), 'jigoshop', 'side', 'core');
		if ($this->options->get('manage_stock') == 'yes') {
			$this->wp->addMetaBox('jigoshop_dashboard_stock_report', __('Stock Report', 'jigoshop'), array($this, 'stockReport'), 'jigoshop', 'side', 'core');
		}
		$this->wp->addMetaBox('jigoshop_dashboard_monthly_report', __('Monthly Report', 'jigoshop'), array($this, 'monthlyReport'), 'jigoshop', 'normal', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_recent_reviews', __('Recent Reviews', 'jigoshop'), array($this, 'recentReviews'), 'jigoshop', 'normal', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_latest_news', __('Latest News', 'jigoshop'), array($this, 'latestNews'), 'jigoshop', 'normal', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_useful_links', __('Useful Links', 'jigoshop'), array($this, 'usefulLinks'), 'jigoshop', 'normal', 'core');

		/** @noinspection PhpUnusedLocalVariableInspection */
		$submenu = $this->wp->getSubmenu();
		include(JIGOSHOP_DIR.'/templates/admin/dashboard.php');
	}

	/**
	 * Displays "Right Now" meta box.
	 */
	public function rightNow()
	{
		$num_posts = $this->wp->wpCountPosts(PostTypes::PRODUCT);
		/** @noinspection PhpUnusedLocalVariableInspection */
		$productCount = $this->wp->numberFormatI18n($num_posts->publish);
		/** @noinspection PhpUnusedLocalVariableInspection */
		$categoryCount = 0;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$tagCount = 0;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$attributesCount = 0;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$pendingCount = 0;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$onHoldCount = 0;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$processingCount = 0;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$completedCount = 0;

		include(JIGOSHOP_DIR.'/templates/admin/dashboard/rightNow.php');
	}

	/**
	 * Displays "Recent Orders" meta box.
	 */
	public function recentOrders()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		// TODO: Replace \WP_Query in order to make Jigoshop testable
		$orders = $this->orderService->findByQuery(new \WP_Query(array(
			'numberposts' => 10,
			'orderby' => 'post_date',
			'order' => 'DESC',
			'post_type' => 'shop_order',
			'post_status' => 'publish'
		)));
		// TODO: Implement after finishing implementing Orders
//		include(JIGOSHOP_DIR.'/templates/admin/dashboard/recentOrders.php');
	}

	/**
	 * Displays "Stock Report" meta box.
	 */
	public function stockReport()
	{
		$lowStockAmount = $this->options->get('notify_low_stock_amount', 1);
		$notifyOufOfStock = $this->options->get('notify_out_of_stock', true);

		if ($notifyOufOfStock) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			$outOfStock = $this->productService->findOutOfStock();
		}

		/** @noinspection PhpUnusedLocalVariableInspection */
		$lowStock = $this->productService->findLowStock($lowStockAmount);

		include(JIGOSHOP_DIR.'/templates/admin/dashboard/stockReport.php');
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

		/** @noinspection PhpUnusedLocalVariableInspection */
		$nextYear = ($selectedMonth == 12) ? $selectedYear + 1 : $selectedYear;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$nextMonth = ($selectedMonth == 12) ? 1 : $selectedMonth + 1;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$previousYear = ($selectedMonth == 1) ? $selectedYear - 1 : $selectedYear;
		/** @noinspection PhpUnusedLocalVariableInspection */
		$previousMonth = ($selectedMonth == 1) ? 12 : $selectedMonth - 1;

		$orders = $this->orderService->findFromMonth($selectedMonth);
		$days = range(strtotime($selectedYear.'-'.$selectedMonth.'-01'), time(), 24 * 3600);
		$orderAmountsData = $orderCountsData = array_fill_keys($days, 0);
		$orderAmounts = $orderCounts = array();

		foreach ($orders as $order) {
			/** @var $order Order */
			if (!in_array($order->getStatus(), array(Order\Status::REFUNDED, Order\Status::CANCELLED))) {
				$day = strtotime(date('Y-m-d', $order->getCreatedAt()->getTimestamp()));
				$orderCountsData[$day] += 1;
				$orderAmountsData[$day] += $order->getSubtotal() + $order->getShipping();
			}
		}

		foreach ($orderCountsData as $day => $value) {
			$orderCounts[] = array($day, $value);
		}
		foreach ($orderAmountsData as $day => $value) {
			$orderAmounts[] = array($day, $value);
		}

		unset($orderAmountsData, $orderCountsData);
		include(JIGOSHOP_DIR.'/templates/admin/dashboard/monthlyReport.php');
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
		include(JIGOSHOP_DIR.'/templates/admin/dashboard/recentReviews.php');
	}

	/**
	 * Displays "Latest News" meta box.
	 */
	public function latestNews()
	{
		if (file_exists(ABSPATH.WPINC.'/class-simplepie.php')) {
			include_once(ABSPATH.WPINC.'/class-simplepie.php');

			$rss = $this->wp->fetchFeed('http://www.jigoshop.com/feed');
			/** @noinspection PhpUnusedLocalVariableInspection */
			$items = array();

			if (!$this->wp->isWpError($rss)) {
				$maxItems = $rss->get_item_quantity(5);
				$rssItems = $rss->get_items(0, $maxItems);

				if ($maxItems > 0) {
					/** @noinspection PhpUnusedLocalVariableInspection */
					$that = $this;
					$items = array_map(function ($item) use ($that) {
						/** @var $item \SimplePie_Item */
						$date = $item->get_date('U');

						return array(
							'title' => $that->wp->wptexturize($item->get_title()),
							'link' => $item->get_permalink(),
							'date' => (abs(time() - $date)) < 86400 ? sprintf(__('%s ago', 'jigoshop'), $that->wp->humanTimeDiff($date)) : date(__('F jS Y', 'jigoshop'), $date),
						);
					}, $rssItems);
				}
			}

			include(JIGOSHOP_DIR.'/templates/admin/dashboard/latestNews.php');
		}
	}

	/**
	 * Displays "Useful Links" meta box.
	 */
	public function usefulLinks()
	{
		include(JIGOSHOP_DIR.'/templates/admin/dashboard/usefulLinks.php');
	}
}