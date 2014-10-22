<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Order
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var OrderServiceInterface */
	private $orderService;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var CustomerServiceInterface */
	private $customerService;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, ProductServiceInterface $productService,
		CustomerServiceInterface $customerService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->productService = $productService;
		$this->customerService = $customerService;

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

		$wp->addAction('wp_ajax_jigoshop.admin.order.add_product', array($this, 'addProduct'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.remove_product', array($this, 'removeProduct'), 10, 0);

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

	public function addProduct()
	{
		// TODO: Add invalid data protection
		try {
			$order = $this->orderService->find($_POST['order']);
			/** @var Product|Product\Purchasable $product */
			$product = $this->productService->find($_POST['product']);
			$item = $this->formatItem($product);
			$order->addItem($item);
			$this->orderService->save($order);

			$row = Render::get('admin/order/item', array(
				'item' => $item,
			));

			$result = array(
				'success' => true,
				'product_subtotal' => $order->getProductSubtotal(),
				'subtotal' => $order->getSubtotal(),
				'total' => $order->getTotal(),
				'html' => array(
					'row' => $row,
					'product_subtotal' => ProductHelper::formatPrice($order->getProductSubtotal()),
					'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
					'total' => ProductHelper::formatPrice($order->getTotal()),
				),
			);
		} catch(Exception $e) {
			$result = array(
				'success' => false,
				'error' => $e->getMessage(),
			);
		}

		echo json_encode($result);
		exit;
	}

	public function removeProduct()
	{
		// TODO: Add invalid data protection
		try {
			$order = $this->orderService->find($_POST['order']);
			$order->removeItem($_POST['product']);
			$this->orderService->save($order);

			$result = array(
				'success' => true,
				'product_subtotal' => $order->getProductSubtotal(),
				'subtotal' => $order->getSubtotal(),
				'total' => $order->getTotal(),
				'html' => array(
					'product_subtotal' => ProductHelper::formatPrice($order->getProductSubtotal()),
					'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
					'total' => ProductHelper::formatPrice($order->getTotal()),
				),
			);
		} catch(Exception $e) {
			$result = array(
				'success' => false,
				'error' => $e->getMessage(),
			);
		}

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

		Render::output('admin/order/dataBox', array(
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

		Render::output('admin/order/itemsBox', array(
			'order' => $order,
		));
	}

	public function totalsBox()
	{
		$post = $this->wp->getGlobalPost();
		$order = $this->orderService->findForPost($post);

		Render::output('admin/order/totalsBox', array(
			'order' => $order,
			'shippingMethods' => array(),//$this->shippingService->getAvailable(),
		));
	}

	public function actionsBox()
	{
		//
	}

	private function formatItem($product)
	{
		$item = new Item();
		$item->setType($product->getType());
		$item->setName($product->getName());
		$item->setPrice($product->getPrice());
		$item->setQuantity(1);
		$item->setProduct($product);

		return $item;
	}
}
