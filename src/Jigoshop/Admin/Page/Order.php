<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Helper\Country;
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
		$wp->addAction('wp_ajax_jigoshop.admin.order.change_country', array($this, 'changeCountry'), 10, 0);
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

			$result = $this->getAjaxResponse($order);
			$result['html']['row'] = $row;
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

			$result = $this->getAjaxResponse($order);
			$result['item_cost'] = $item->getCost();
			$result['html']['item_cost'] = ProductHelper::formatPrice($item->getCost());
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
			$result = $this->getAjaxResponse($order);
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
			$result = $this->getAjaxResponse($order);
		} catch(Exception $e) {
			$result = array(
				'success' => false,
				'error' => $e->getMessage(),
			);
		}

		echo json_encode($result);
		exit;
	}

	public function dataBox()
	{
		$post = $this->wp->getGlobalPost();
		$order = $this->orderService->findForPost($post);
		$billingFields = $this->wp->applyFilters('jigoshop\admin\order\billing_fields', array(
			'company' => array(
				'label' => __('Company', 'jigoshop'),
				'type' => 'text',
			),
			'euvatno' => array(
				'label' => __('EU VAT Number', 'jigoshop'),
				'type' => 'text',
			),
			'first_name' => array(
				'label' => __('First Name', 'jigoshop'),
				'type' => 'text',
			),
			'last_name' => array(
				'label' => __('Last Name', 'jigoshop'),
				'type' => 'text',
			),
			'address' => array(
				'label' => __('Address', 'jigoshop'),
				'type' => 'text',
			),
			'city' => array(
				'label' => __('City', 'jigoshop'),
				'type' => 'text',
			),
			'postcode' => array(
				'label' => __('Postcode', 'jigoshop'),
				'type' => 'text',
			),
			'country' => array(
				'label' => __('Country', 'jigoshop'),
				'type' => 'select',
				'options' => Country::getAll(),
			),
			'state' => array(
				'label' => __('State/Province', 'jigoshop'),
				'type' => Country::hasStates($order->getBillingAddress()->getCountry()) ? 'select' : 'text',
				'options' => Country::getStates($order->getBillingAddress()->getCountry()),
			),
			'phone' => array(
				'label' => __('Phone', 'jigoshop'),
				'type' => 'text',
			),
			'email' => array(
				'label' => __('Email Address', 'jigoshop'),
				'type' => 'text',
			),
		), $order);
		$shippingFields = $this->wp->applyFilters('jigoshop\admin\order\shipping_fields', array(
			'company' => array(
				'label' => __('Company', 'jigoshop'),
				'type' => 'text',
			),
			'first_name' => array(
				'label' => __('First Name', 'jigoshop'),
				'type' => 'text',
			),
			'last_name' => array(
				'label' => __('Last Name', 'jigoshop'),
				'type' => 'text',
			),
			'address' => array(
				'label' => __('Address', 'jigoshop'),
				'type' => 'text',
			),
			'city' => array(
				'label' => __('City', 'jigoshop'),
				'type' => 'text',
			),
			'postcode' => array(
				'label' => __('Postcode', 'jigoshop'),
				'type' => 'text',
			),
			'country' => array(
				'label' => __('Country', 'jigoshop'),
				'type' => 'select',
				'options' => Country::getAll(),
			),
			'state' => array(
				'label' => __('State/Province', 'jigoshop'),
				'type' => Country::hasStates($order->getShippingAddress()->getCountry()) ? 'select' : 'text',
				'options' => Country::getStates($order->getShippingAddress()->getCountry()),
			),
			'phone' => array(
				'label' => __('Phone', 'jigoshop'),
				'type' => 'text',
			),
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
		$customer = $this->customerService->fromOrder($order);

		$tax = array();
		foreach ($order->getTax() as $class => $value) {
			$tax[$class] = array(
				'label' => $this->taxService->getLabel($class, $customer),
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
		$customer = $this->customerService->fromOrder($order);

		$item = new Item();
		$item->setType($product->getType());
		$item->setName($product->getName());
		$item->setPrice($product->getPrice());
		$item->setTax($this->taxService->getAll($product, 1, $customer));
		$item->setQuantity(1);
		$item->setProduct($product);

		return $item;
	}

	private function getAjaxResponse($order)
	{
		$customer = $this->customerService->fromOrder($order);

		$tax = array();
		foreach ($order->getTax() as $class => $value) {
			$tax[$class] = array(
				'label' => $this->taxService->getLabel($class, $customer),
				'value' => ProductHelper::formatPrice($value),
			);
		}

		return array(
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
	}
}
