<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\OrderInterface;
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
use Jigoshop\Shipping\Method;
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

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $options, $styles, $scripts){
			if ($wp->getPostType() == Types::ORDER) {
				$styles->add('jigoshop.admin.order', JIGOSHOP_URL.'/assets/css/admin/order.css');
				$styles->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
				$scripts->add('jigoshop.admin.order', JIGOSHOP_URL.'/assets/js/admin/order.js');
				$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
				$scripts->localize('jigoshop.admin.order', 'jigoshop_admin_order', array(
					'ajax' => $wp->getAjaxUrl(),
					'tax_shipping' => $options->get('tax.shipping'),
					'ship_to_billing' => $options->get('shipping.only_to_billing'),
				));
			}
		});

		$wp->addAction('wp_ajax_jigoshop.admin.order.add_product', array($this, 'ajaxAddProduct'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.update_product', array($this, 'ajaxUpdateProduct'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.remove_product', array($this, 'ajaxRemoveProduct'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.change_country', array($this, 'ajaxChangeCountry'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.change_shipping_method', array($this, 'ajaxChangeShippingMethod'), 10, 0);

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

	public function ajaxAddProduct()
	{
		try {
			$order = $this->orderService->find((int)$_POST['order']);

			if ($order->getId() === null) {
				throw new Exception(__('Order not found.', 'jigoshop'));
			}

			/** @var Product|Product\Purchasable $product */
			$product = $this->productService->find((int)$_POST['product']);

			if ($product->getId() === null) {
				throw new Exception(__('Product not found.', 'jigoshop'));
			}

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

	public function ajaxUpdateProduct()
	{
		try {
			if (!is_numeric($_POST['quantity']) || $_POST['quantity'] < 0) {
				throw new Exception(__('Invalid quantity value.', 'jigoshop'));
			}
			if (!is_numeric($_POST['price']) || $_POST['price'] < 0) {
				throw new Exception(__('Invalid product price.', 'jigoshop'));
			}

			$order = $this->orderService->find((int)$_POST['order']);

			if ($order->getId() === null) {
				throw new Exception(__('Order not found.', 'jigoshop'));
			}

			$item = $order->removeItem($_POST['product']);
			$item->setQuantity((int)$_POST['quantity']);
			$item->setPrice((float)$_POST['price']);

			if ($item->getQuantity() > 0) {
				$item->setTax($this->taxService->getAll($item, 1, $order->getCustomer()));
				$order->addItem($item);
			}

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

	public function ajaxRemoveProduct()
	{
		try {
			$order = $this->orderService->find((int)$_POST['order']);

			if ($order->getId() === null) {
				throw new Exception(__('Order not found.', 'jigoshop'));
			}

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

	public function ajaxChangeShippingMethod()
	{
		try {
			$order = $this->orderService->find((int)$_POST['order']);

			if ($order->getId() === null) {
				throw new Exception(__('Order not found.', 'jigoshop'));
			}

			$shippingMethod = $this->shippingService->get($_POST['method']);
			$order->setShippingMethod($shippingMethod, $this->taxService);
			$order = $this->rebuildOrder($order);
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

	public function ajaxChangeCountry()
	{
		try {
			if (!in_array($_POST['value'], array_keys(Country::getAllowed()))) {
				throw new Exception(__('Invalid country.', 'jigoshop'));
			}

			// TODO: Some kind of workaround for setting global post
			global $post;
			$post = $this->wp->getPost((int)$_POST['order']);
			$order = $this->orderService->findForPost($post);

			if ($order->getId() === null) {
				throw new Exception(__('Order not found.', 'jigoshop'));
			}

			switch ($_POST['type']) {
				case 'shipping':
					$address = $order->getCustomer()->getShippingAddress();
					break;
				case 'billing':
				default:
					$address = $order->getCustomer()->getBillingAddress();
			}

			$address->setCountry($_POST['value']);
			$order = $this->rebuildOrder($order);
			$this->orderService->save($order);

			$result = $this->getAjaxResponse($order);
			$result['has_states'] = Country::hasStates($address->getCountry());
			$result['states'] = Country::getStates($address->getCountry());
		} catch(Exception $e) {
			$result = array(
				'success' => false,
				'error' => $e->getMessage(),
			);
		}

		echo json_encode($result);
		exit;
	}

	// TODO: Change actions for state and postcode

	public function dataBox()
	{
		$post = $this->wp->getGlobalPost();
		$order = $this->orderService->findForPost($post);
		$billingOnly = $this->options->get('shipping.only_to_billing');
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
				'options' => Country::getAllowed(),
			),
			'state' => array(
				'label' => __('State/Province', 'jigoshop'),
				'type' => Country::hasStates($order->getCustomer()->getBillingAddress()->getCountry()) ? 'select' : 'text',
				'options' => Country::getStates($order->getCustomer()->getBillingAddress()->getCountry()),
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
				'options' => Country::getAllowed(),
			),
			'state' => array(
				'label' => __('State/Province', 'jigoshop'),
				'type' => Country::hasStates($order->getCustomer()->getShippingAddress()->getCountry()) ? 'select' : 'text',
				'options' => Country::getStates($order->getCustomer()->getShippingAddress()->getCountry()),
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
			'customers' => $customers,
			'billingOnly' => $billingOnly,
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
			'shippingMethods' => $this->shippingService->getEnabled(),
			'tax' => $this->getTaxes($order),
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
		$item->setName($product->getName());
		// TODO: Item price should ALWAYS be without taxes.
		$item->setPrice($product->getPrice());
		$item->setTax($this->taxService->getAll($product, 1, $order->getCustomer()));
		$item->setQuantity(1);
		$item->setProduct($product);

		return $item;
	}

	/**
	 * @param $order OrderInterface Order to get values from.
	 * @return array Ajax response array.
	 */
	private function getAjaxResponse($order)
	{
		$tax = $order->getTax();
		$shippingTax = $order->getShippingTax();

		foreach ($order->getTax() as $class => $value) {
			$tax[$class] = $value + $shippingTax[$class];
		}

		$shipping = array();
		foreach ($this->shippingService->getAvailable() as $method) {
			/** @var $method Method */
			$shipping[$method->getId()] = $method->isEnabled() ? $method->calculate($order) : -1;
		}

		return array(
			'success' => true,
			'shipping' => $shipping,
			'product_subtotal' => $order->getProductSubtotal(),
			'subtotal' => $order->getSubtotal(),
			'total' => $order->getTotal(),
			'tax' => $tax,
			'html' => array(
				'shipping' => array_map(function($item) use ($order) {
					return array(
						'price' => ProductHelper::formatPrice($item->calculate($order)),
						'html' => Render::get('admin/order/totals/shipping_method', array('method' => $item, 'order' => $order)),
					);
				}, $this->shippingService->getEnabled()),
				'product_subtotal' => ProductHelper::formatPrice($order->getProductSubtotal()),
				'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
				'total' => ProductHelper::formatPrice($order->getTotal()),
				'tax' => $this->getTaxes($order),
			),
		);
	}

	/**
	 * @param $order OrderInterface Order to get taxes for.
	 * @return array Taxes with labels array.
	 */
	private function getTaxes($order)
	{
		$result = array();
		$shippingTax = $order->getShippingTax();
		foreach ($order->getTax() as $class => $value) {
			$result[$class] = array(
				'label' => $this->taxService->getLabel($class, $order->getCustomer()),
				'value' => ProductHelper::formatPrice($value + $shippingTax[$class]),
			);
		}

		return $result;
	}

	/**
	 * @param $order \Jigoshop\Entity\Order The order.
	 * @return \Jigoshop\Entity\Order Updated order.
	 */
	private function rebuildOrder($order)
	{
		// Recalculate values
		$items = $order->getItems();
		$method = $order->getShippingMethod();
		$order->removeItems();

		foreach ($items as $item) {
			/** @var $item Item */
			$item->setTax($this->taxService->getAll($item, 1, $order->getCustomer()));
			$order->addItem($item);
		}

		if ($method !== null) {
			$order->setShippingMethod($method, $this->taxService);
		}

		return $order;
	}
}
