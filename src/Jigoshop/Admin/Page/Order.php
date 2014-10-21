<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
use WPAL\Wordpress;

class Order
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Service\OrderServiceInterface */
	private $orderService;
	/** @var TaxServiceInterface */
	private $taxService;
	/** @var CustomerServiceInterface */
	private $customerService;
	/** @var ShippingServiceInterface */
	private $shippingService;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, TaxServiceInterface $taxService, CustomerServiceInterface $customerService,
		ShippingServiceInterface $shippingService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->taxService = $taxService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles, $scripts){
			if ($wp->getPostType() == Types::ORDER) {
				$styles->add('jigoshop.admin.order', JIGOSHOP_URL.'/assets/css/admin/order.css');
				$styles->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
				$scripts->add('jigoshop.admin.order', JIGOSHOP_URL.'/assets/js/admin/order.js');
				$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
				$scripts->localize('jigoshop.admin.order', 'jigoshop_admin_order', array(
					'ajax' => admin_url('admin-ajax.php'),
				));
			}
		});

		$wp->addAction('wp_ajax_jigoshop.admin.find_product', array($this, 'findProduct'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.add_product', array($this, 'addProduct'), 10, 0);

		$that = $this;
		$wp->addAction('add_meta_boxes_'.Types::ORDER, function() use ($wp, $that){
			$wp->addMetaBox('jigoshop-order-data', __('Order Data', 'jigoshop'), array($that, 'dataBox'), Types::ORDER, 'normal', 'high');
			$wp->addMetaBox('jigoshop-order-items', __('Order Items', 'jigoshop'), array($that, 'itemsBox'), Types::ORDER, 'normal', 'high');
			$wp->addMetaBox('jigoshop-order-totals', __('Order Totals', 'jigoshop'), array($that, 'totalsBox'), Types::ORDER, 'normal', 'default');
//			add_meta_box('jigoshop-order-attributes', __('Order Variation Attributes / Addons', 'jigoshop'), array($that, 'itemsBox'), Types::ORDER, 'side', 'default');

			$wp->addMetaBox('jigoshop-order-actions', __('Order Actions', 'jigoshop'), array($that, 'actionsBox'), Types::ORDER, 'side', 'default');
			$wp->removeMetaBox('commentstatusdiv', null, 'normal');
		});
	}

	// TODO: Refactor the class, add page resolver with proper pages to get client related code from order type definition
	public function findProduct()
	{
		$result = array(
			'success' => true,
			'results' => array(
				array(
					'id' => 1,
					'text' => 'Test123',
				),
			),
		);

		echo json_encode($result);
		exit;
	}

	public function addProduct()
	{
		$result = array(
			'success' => true,
			'html' => array(
				'row' => '<tr><td>1</td><td></td><td>Test123</td><td><input type="text" value="1.00" /></td><td><input type="text" value="1" /></td><td>$1.00</td><td>x</td></tr>',
				'product_subtotal' => '$26.00',
				'subtotal' => '$26.00',
				'total' => '$26.00',
			),
		);

		echo json_encode($result);
		exit;
	}

	// TODO: Think on better place to keep order displaying functions
	public function dataBox()
	{
		$post = $this->wp->getGlobalPost();
		$order = $this->orderService->findForPost($post);
		$billingFields = $this->wp->applyFilters('jigoshop\admin\order\billing_fields', array(
			'company' => __('Company', 'jigoshop'),
			'euvatno' => __('EU VAT Number', 'jigoshop'),
			'first_name' => __('First Name', 'jigoshop'),
			'last_name' => __('Last Name', 'jigoshop'),
			'address' => __('Address', 'jigoshop'),
			'city' => __('City', 'jigoshop'),
			'postcode' => __('Postcode', 'jigoshop'),
			'country' => __('Country', 'jigoshop'),
			'state' => __('State/Province', 'jigoshop'),
			'phone' => __('Phone', 'jigoshop'),
			'email' => __('Email Address', 'jigoshop'),
		), $order);
		$shippingFields = $this->wp->applyFilters('jigoshop\admin\order\shipping_fields', array(
			'company' => __('Company', 'jigoshop'),
			'first_name' => __('First Name', 'jigoshop'),
			'last_name' => __('Last Name', 'jigoshop'),
			'address' => __('Address', 'jigoshop'),
			'city' => __('City', 'jigoshop'),
			'postcode' => __('Postcode', 'jigoshop'),
			'country' => __('Country', 'jigoshop'),
			'state' => __('State/Province', 'jigoshop'),
			'phone' => __('Phone', 'jigoshop'),
		), $order);
		$customers = $this->customerService->findAll();

		Render::output('admin/orders/dataBox', array(
			'order' => $order,
			'billingFields' => $billingFields,
			'shippingFields' => $shippingFields,
			'customers' => $customers
		));
	}

	public function itemsBox()
	{
		$post = $this->wp->getGlobalPost();
		$order = $this->orderService->findForPost($post);

		Render::output('admin/orders/itemsBox', array(
			'order' => $order,
		));
	}

	public function totalsBox()
	{
		$post = $this->wp->getGlobalPost();
		$order = $this->orderService->findForPost($post);

		Render::output('admin/orders/totalsBox', array(
			'order' => $order,
			'shippingMethods' => array(),//$this->shippingService->getAvailable(),
		));
	}

	public function actionsBox()
	{
		//
	}
}
