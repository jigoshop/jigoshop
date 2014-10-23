<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer;
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
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
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
	/** @var ShippingServiceInterface */
	private $shippingService;
	/** @var TaxServiceInterface */
	private $taxService;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, ProductServiceInterface $productService,
		CustomerServiceInterface $customerService, ShippingServiceInterface $shippingService, TaxServiceInterface $taxService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->productService = $productService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;
		$this->taxService = $taxService;

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
		$wp->addAction('wp_ajax_jigoshop.admin.order.update_product', array($this, 'updateProduct'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.remove_product', array($this, 'removeProduct'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.change_shipping_method', array($this, 'changeShippingMethod'), 10, 0);

		$that = $this;
		$wp->addAction('add_meta_boxes_'.Types::ORDER, function() use ($wp, $orderService, $that){
			$post = $wp->getGlobalPost();
			$order = $orderService->findForPost($post);
			$wp->addMetaBox('jigoshop-order-data', $order->getTitle(), array($that, 'dataBox'), Types::ORDER, 'normal', 'high');
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
			$item = $this->formatItem($order, $product);
			$order->addItem($item);
			$this->orderService->save($order);

			$row = Render::get('admin/order/item', array(
				'item' => $item,
			));

			$tax = array();
			foreach ($order->getTax() as $class => $value) {
				$tax[$class] = array(
					'label' => $this->taxService->getLabel($class),
					'value' => ProductHelper::formatPrice($value),
				);
			}

			$result = array(
				'success' => true,
				'product_subtotal' => $order->getProductSubtotal(),
				'subtotal' => $order->getSubtotal(),
				'total' => $order->getTotal(),
				'tax' => $order->getTax(),
				'html' => array(
					'row' => $row,
					'product_subtotal' => ProductHelper::formatPrice($order->getProductSubtotal()),
					'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
					'total' => ProductHelper::formatPrice($order->getTotal()),
					'tax' => $tax,
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

	public function updateProduct()
	{
		// TODO: Add invalid data protection
		try {
			$order = $this->orderService->find($_POST['order']);
			$item = $order->removeItem($_POST['product']);
			$item->setQuantity((int)$_POST['quantity']);
			$item->setPrice((float)$_POST['price']);
			$order->addItem($item);
			$this->orderService->save($order);

			$tax = array();
			foreach ($order->getTax() as $class => $value) {
				$tax[$class] = array(
					'label' => $this->taxService->getLabel($class),
					'value' => ProductHelper::formatPrice($value),
				);
			}

			$result = array(
				'success' => true,
				'item_cost' => $item->getCost(),
				'product_subtotal' => $order->getProductSubtotal(),
				'subtotal' => $order->getSubtotal(),
				'total' => $order->getTotal(),
				'tax' => $order->getTax(),
				'html' => array(
					'item_cost' => ProductHelper::formatPrice($item->getCost()),
					'product_subtotal' => ProductHelper::formatPrice($order->getProductSubtotal()),
					'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
					'total' => ProductHelper::formatPrice($order->getTotal()),
					'tax' => $tax,
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

			$tax = array();
			foreach ($order->getTax() as $class => $value) {
				$tax[$class] = array(
					'label' => $this->taxService->getLabel($class),
					'value' => ProductHelper::formatPrice($value),
				);
			}

			$result = array(
				'success' => true,
				'product_subtotal' => $order->getProductSubtotal(),
				'subtotal' => $order->getSubtotal(),
				'total' => $order->getTotal(),
				'tax' => $order->getTax(),
				'html' => array(
					'product_subtotal' => ProductHelper::formatPrice($order->getProductSubtotal()),
					'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
					'total' => ProductHelper::formatPrice($order->getTotal()),
					'tax' => $tax,
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

	public function changeShippingMethod()
	{
		// TODO: Add invalid data protection
		try {
			$order = $this->orderService->find($_POST['order']);
			$shippingMethod = $this->shippingService->get($_POST['shipping_method']);
			$order->setShippingMethod($shippingMethod);
			$this->orderService->save($order);

			$tax = array();
			foreach ($order->getTax() as $class => $value) {
				$tax[$class] = array(
					'label' => $this->taxService->getLabel($class),
					'value' => ProductHelper::formatPrice($value),
				);
			}

			$result = array(
				'success' => true,
				'product_subtotal' => $order->getProductSubtotal(),
				'subtotal' => $order->getSubtotal(),
				'total' => $order->getTotal(),
				'tax' => $order->getTax(),
				'html' => array(
					'product_subtotal' => ProductHelper::formatPrice($order->getProductSubtotal()),
					'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
					'total' => ProductHelper::formatPrice($order->getTotal()),
					'tax' => $tax,
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
		// TODO: Properly get customer from order billing/shipping data
		$c = new Customer();
		$c->setCountry('PL');

		$tax = array();
		foreach ($order->getTax() as $class => $value) {
			$tax[$class] = array(
				'label' => $this->taxService->getLabel($class, $c),
				'value' => ProductHelper::formatPrice($value),
			);
		}

		Render::output('admin/order/totalsBox', array(
			'order' => $order,
			'shippingMethods' => array(),//$this->shippingService->getAvailable(),
			'tax' => $tax,
		));
	}

	public function actionsBox()
	{
		//
	}

	/**
	 * @param $order \Jigoshop\Entity\Order The order.
	 * @param $product Product|Product\Purchasable The product to format.
	 * @return Item Prepared item.
	 */
	private function formatItem($order, $product)
	{
		$item = new Item();
		$item->setType($product->getType());
		$item->setName($product->getName());
		$item->setPrice($product->getPrice());
		// TODO: Use billing or shipping address (based on option) as customer (for taxes)
		$c = new Customer();
		$c->setCountry('PL');
		$item->setTax($this->taxService->getAll($product, $c));//$order->getCustomer()));
		$item->setQuantity(1);
		$item->setProduct($product);

		return $item;
	}
}
