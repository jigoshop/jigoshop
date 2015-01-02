<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Exception;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Tax;
use Jigoshop\Helper\Validation;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Shipping;
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

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, ProductServiceInterface $productService,
		CustomerServiceInterface $customerService, ShippingServiceInterface $shippingService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->productService = $productService;
		$this->customerService = $customerService;
		$this->shippingService = $shippingService;

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $options, $styles, $scripts){
			if ($wp->getPostType() == Types::ORDER) {
				$styles->add('jigoshop.admin.order', JIGOSHOP_URL.'/assets/css/admin/order.css');
				$scripts->add('jigoshop.admin.order', JIGOSHOP_URL.'/assets/js/admin/order.js', array('jquery', 'jigoshop.helpers'));
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
		$wp->addAction('wp_ajax_jigoshop.admin.order.change_state', array($this, 'ajaxChangeState'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.change_postcode', array($this, 'ajaxChangePostcode'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.order.change_shipping_method', array($this, 'ajaxChangeShippingMethod'), 10, 0);

		$that = $this;
		$wp->addAction('add_meta_boxes_'.Types::ORDER, function() use ($wp, $orderService, $that){
			$post = $wp->getGlobalPost();
			$order = $orderService->findForPost($post);
			$wp->addMetaBox('jigoshop-order-data', $order->getTitle(), array($that, 'dataBox'), Types::ORDER, 'normal', 'high');
			$wp->addMetaBox('jigoshop-order-items', __('Order Items', 'jigoshop'), array($that, 'itemsBox'), Types::ORDER, 'normal', 'high');
			$wp->addMetaBox('jigoshop-order-totals', __('Order Totals', 'jigoshop'), array($that, 'totalsBox'), Types::ORDER, 'normal', 'high');
//			add_meta_box('jigoshop-order-attributes', __('Order Variation Attributes / Addons', 'jigoshop'), array($that, 'itemsBox'), Types::ORDER, 'side', 'default');

//			$wp->addMetaBox('jigoshop-order-actions', __('Order Actions', 'jigoshop'), array($that, 'actionsBox'), Types::ORDER, 'side', 'default');
			// Remove discussion and add comments meta box
			$wp->removeMetaBox('commentstatusdiv', null, 'normal');
			$wp->addMetaBox('commentsdiv', __('Comments'), 'post_comment_meta_box', null, 'normal', 'core');
		});
	}

	public function ajaxAddProduct()
	{
		try {
			$order = $this->orderService->find((int)$_POST['order']);

			if ($order->getId() === null) {
				throw new Exception(__('Order not found.', 'jigoshop'));
			}

			/** @var ProductEntity|ProductEntity\Purchasable $product */
			$product = $this->productService->find((int)$_POST['product']);

			if ($product->getId() === null) {
				throw new Exception(__('Product not found.', 'jigoshop'));
			}

			/** @var Item $item */
			$item = $this->wp->applyFilters('jigoshop\cart\add', null, $product);

			if ($item === null) {
				throw new Exception(__('Product cannot be added to the order.', 'jigoshop'));
			}

			$key = $this->productService->generateItemKey($item);
			$item->setKey($key);

			$order->addItem($item);
			$this->orderService->save($order);

			$row = Render::get('admin/order/item/'.$item->getType(), array(
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

			if ($item === null) {
				throw new Exception(__('Item not found.', 'jigoshop'));
			}

			$item->setQuantity((int)$_POST['quantity']);
			$item->setPrice((float)$_POST['price']);

			if ($item->getQuantity() > 0) {
				$item = $this->wp->applyFilters('jigoshop\admin\order\update_product', $item, $order);
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

			if ($shippingMethod instanceof Shipping\MultipleMethod) {
				if (!isset($_POST['rate'])) {
					throw new Exception(__('Method rate is required.', 'jigoshop'));
				}

				$shippingMethod->setShippingRate((int)$_POST['rate']);
			}

			$order->setShippingMethod($shippingMethod);
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

			$post = $this->wp->getPost((int)$_POST['order']);
			$this->wp->updateGlobalPost($post);
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

	/**
	 * Ajax action for changing state.
	 */
	public function ajaxChangeState()
	{
		try {
			$post = $this->wp->getPost((int)$_POST['order']);
			$this->wp->updateGlobalPost($post);
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

			if (Country::hasStates($address->getCountry()) && !Country::hasState($address->getCountry(), $_POST['value'])) {
				throw new Exception(__('Invalid state.', 'jigoshop'));
			}

			$address->setState($_POST['value']);
			$order = $this->rebuildOrder($order);
			$this->orderService->save($order);

			$result = $this->getAjaxResponse($order);
		} catch (Exception $e) {
			$result = array(
				'success' => false,
				'error' => $e->getMessage(),
			);
		}

		echo json_encode($result);
		exit;
	}

	/**
	 * Ajax action for changing postcode.
	 */
	public function ajaxChangePostcode()
	{
		try {
			$post = $this->wp->getPost((int)$_POST['order']);
			$this->wp->updateGlobalPost($post);
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

			if ($this->options->get('shopping.validate_zip') && !Validation::isPostcode($_POST['value'], $address->getCountry())) {
				throw new Exception(__('Invalid postcode.', 'jigoshop'));
			}

			$address->setPostcode($_POST['value']);
			$order = $this->rebuildOrder($order);
			$this->orderService->save($order);

			$result = $this->getAjaxResponse($order);
		} catch (Exception $e) {
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
		$shippingHtml = array();
		foreach ($this->shippingService->getAvailable() as $method) {
			/** @var $method Shipping\Method */
			if ($method instanceof Shipping\MultipleMethod) {
				/** @var $method Shipping\MultipleMethod */
				foreach ($method->getRates() as $rate) {
					/** @var $rate Shipping\Rate */
					$shipping[$method->getId().'-'.$rate->getId()] = $method->isEnabled() ? $rate->calculate($order) : -1;

					if ($method->isEnabled()) {
						$shippingHtml[$method->getId().'-'.$rate->getId()] = array(
							'price' => ProductHelper::formatPrice($rate->calculate($order)),
							'html' => Render::get('admin/order/totals/shipping/rate', array('method' => $method, 'rate' => $rate, 'order' => $order)),
						);
					}
				}
			} else {
				$shipping[$method->getId()] = $method->isEnabled() ? $method->calculate($order) : -1;

				if ($method->isEnabled()) {
					$shippingHtml[$method->getId()] = array(
						'price' => ProductHelper::formatPrice($method->calculate($order)),
						'html' => Render::get('admin/order/totals/shipping/method', array('method' => $method, 'order' => $order)),
					);
				}
			}
		}

		return array(
			'success' => true,
			'shipping' => $shipping,
			'product_subtotal' => $order->getProductSubtotal(),
			'subtotal' => $order->getSubtotal(),
			'total' => $order->getTotal(),
			'tax' => $tax,
			'html' => array(
				'shipping' => $shippingHtml,
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
		foreach ($order->getCombinedTax() as $class => $value) {
			$result[$class] = array(
				'label' => Tax::getLabel($class, $order),
				'value' => ProductHelper::formatPrice($value),
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
			$item = $this->wp->applyFilters('jigoshop\admin\order\update_product', $item, $order);
			$order->addItem($item);
		}

		if ($method !== null) {
			$order->setShippingMethod($method);
		}

		return $order;
	}
}
