<?php

namespace Jigoshop\Frontend;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Coupon;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Service\CouponServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Shipping\Method;
use Jigoshop\Shipping\Rate;
use Monolog\Registry;
use WPAL\Wordpress;

class Cart implements OrderInterface
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var ProductServiceInterface  */
	private $productService;
	/** @var ShippingServiceInterface */
	private $shippingService;
	/** @var CouponServiceInterface */
	private $couponService;
	/** @var Messages */
	private $messages;

	/** @var string */
	private $id;
	/** @var array */
	private $items = array();
	/** @var array */
	private $tax = array();
	/** @var array */
	private $shippingTax = array();
	/** @var float */
	private $shippingPrice = 0.0;
	/** @var Method */
	private $shippingMethod;
	/** @var int */
	private $shippingMethodRate;
	/** @var Customer */
	private $customer;
	/** @var float */
	private $total = 0.0;
	/** @var float */
	private $subtotal = 0.0;
	/** @var float */
	private $productSubtotal = 0.0;
	/** @var float */
	private $discount = 0.0;
	/** @var float */
	private $totalTax;
	/** @var array */
	private $coupons = array();

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService,	ShippingServiceInterface $shippingService,
		CouponServiceInterface $couponService, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->shippingService = $shippingService;
		$this->couponService = $couponService;
		$this->messages = $messages;

		foreach ($this->options->get('tax.classes') as $class) {
			$this->tax[$class['class']] = 0.0;
			$this->shippingTax[$class['class']] = 0.0;
		}
	}

	/**
	 * @param string $id
	 * @param array $data
	 */
	public function initializeFor($id, $data = array())
	{
		$this->id = $id;
		$this->items = array();
		$this->total = 0.0;
		$this->subtotal = 0.0;
		$this->productSubtotal = 0.0;
		$this->discount = 0.0;
		$this->coupons = array();
		$this->totalTax = null;
		$this->shippingPrice = 0.0;
		$this->tax = array_map(function(){ return 0.0; }, $this->tax);
		$this->shippingTax = array_map(function(){ return 0.0; }, $this->shippingTax);
		$this->shippingMethod = null;
		$this->shippingMethodRate = null;

		if (!empty($data)) {
			$this->id = $data['id'];

			if (isset($data['customer'])) {
				$customer = unserialize($data['customer']);
				$this->setCustomer($customer);
			}

			if (isset($data['shipping_method'])) {
				$this->setShippingMethod($this->shippingService->findForState($data['shipping_method']));
			}
			if (isset($data['shipping_method_rate'])) {
				$this->shippingMethodRate = $data['shipping_method_rate'];
			}

			$items = unserialize($data['items']);
//			$taxIncludedInPrice = $this->options->get('tax.included');

			if (is_array($items)) {
				foreach ($items as $item) {
					/** @var Item $item */
					$productState = (array)$item->getProduct();
					$product = $this->productService->findForState($productState);
					$item->setProduct($product);

					foreach ($item->getTax() as $class => $value) {
						$this->tax[$class] += $value * $item->getQuantity();
					}

					$key = $this->productService->generateItemKey($item);
					if ($key != $item->getKey()) {
						Registry::getInstance('jigoshop')->addWarning(sprintf('Initializing cart: item "%d" has invalid key ("%s" instead of "%s").', $item->getId(), $item->getKey(), $key));
					}

					$item->setKey($key);

					// TODO: Add support for "Price included in tax"
//					if ($taxIncludedInPrice) {
//						$price -= $tax;
//					}

					$this->subtotal += $item->getCost();
					$this->productSubtotal += $item->getCost();
					$this->total += $item->getCost() + $item->getTotalTax();

					$this->items[$key] = $item;
				}
			}

			if (isset($data['coupons'])) {
				foreach ($data['coupons'] as $couponId) {
					try {
						$this->addCoupon($this->couponService->find($couponId));
					} catch (Exception $e) {
						$this->messages->addWarning($e->getMessage(), false);
					}
				}
			}
		}
	}

	/**
	 * @return string Cart ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Adds item to the cart.
	 * If item is already present - increases it's quantity.
	 *
	 * @param Item $item Item to add to cart.
	 * @throws NotEnoughStockException When user requests more than we have.
	 * @throws Exception On any error.
	 */
	public function addItem(Item $item)
	{
		$product = $item->getProduct();
		$quantity = $item->getQuantity();

		if ($product === null || $product->getId() === 0) {
			throw new Exception(__('Product not found', 'jigoshop'));
		}

		if ($quantity <= 0) {
			throw new Exception(__('Quantity has to be positive number', 'jigoshop'));
		}

		if ($product instanceof Product\Purchasable && $product->getStock()->getManage()) {
			// TODO: Check for backorders!
			if ($quantity > $product->getStock()->getStock()) {
				throw new NotEnoughStockException($product->getStock()->getStock());
			}
		}

		$key = $this->productService->generateItemKey($item);
		$item->setKey($key);
		if (isset($this->items[$key])) {
			/** @var Item $itemInCart */
			$itemInCart = $this->items[$key];
			$itemInCart->setQuantity($itemInCart->getQuantity() + $item->getQuantity());
		} else {
			$item = $this->wp->applyFilters('jigoshop\cart\add_item', $item, $this);

			$tax = 0.0;
			foreach ($item->getTax() as $class => $value) {
				$this->tax[$class] += $value * $quantity;
				$tax += $value * $quantity;
			}

			$price = $item->getPrice();
			// TODO: Support for "Price includes tax"
//			if ($this->options->get('tax.included')) {
//				$price -= $tax;
//			}

			$this->total += $quantity * ($price + $tax);
			$this->subtotal += $quantity * $price;
			$this->productSubtotal += $quantity * $price;
			$this->totalTax = null;

			$this->items[$key] = $item;
		}
	}

	/**
	 * Removes item from cart.
	 *
	 * @param string $key Item id to remove from cart.
	 * @return Item|bool Removed item or null if not found.
	 */
	public function removeItem($key)
	{
		if (isset($this->items[$key])) {
			// TODO: Support for "Price includes tax"
			/** @var Item $item */
			$item = $this->items[$key];
			$this->total -= $item->getCost() + $item->getTotalTax();
			$this->subtotal -= $item->getCost();
			$this->productSubtotal -= $item->getCost();
			$this->totalTax = null;

			foreach ($item->getTax() as $class => $value) {
				$this->tax[$class] -= $value;
			}


			unset($this->items[$key]);
			return $item;
		}

		return null;
	}

	/**
	 * Removes all items, shipping method and associated taxes from the order.
	 */
	public function removeItems()
	{
		$this->removeShippingMethod();
		$this->items = array();
		$this->productSubtotal = 0.0;
		$this->subtotal = 0.0;
		$this->total = 0.0;
		$this->tax = array_map(function() { return 0.0; }, $this->tax);
		$this->totalTax = null;
	}

	/**
	 * Updates quantity of selected item by it's key.
	 *
	 * @param $key string Item key in the cart.
	 * @param $quantity int Quantity to set.
	 * @throws Exception When product does not exists or quantity is not numeric.
	 */
	public function updateQuantity($key, $quantity)
	{
		if (!isset($this->items[$key])) {
			throw new Exception(__('Item does not exists', 'jigoshop'));
		}

		if (!is_numeric($quantity)) {
			throw new Exception(__('Quantity has to be numeric value', 'jigoshop'));
		}

		$item = $this->removeItem($key);

		if ($quantity <= 0) {
			return;
		}

		$item->setQuantity($quantity);
		$this->addItem($item);
	}

	/**
	 * @param $key string Item key.
	 * @return Item Item data.
	 * @throws Exception When item does not exists.
	 */
	public function getItem($key)
	{
		if (!isset($this->items[$key])) {
			throw new Exception(__('Item does not exists', 'jigoshop'));
		}

		return $this->items[$key];
	}

	/**
	 * @return array List of items in the cart.
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return bool Is the cart empty?
	 */
	public function isEmpty()
	{
		return empty($this->items);
	}

	/**
	 * @return float Current total value of the cart.
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * @return float Current subtotal of the cart.
	 */
	public function getSubtotal()
	{
		return $this->subtotal;
	}

	/**
	 * @return float Current products subtotal of the cart.
	 */
	public function getProductSubtotal()
	{
		return $this->productSubtotal;
	}

	/**
	 * @param $coupon Coupon
	 */
	public function addCoupon($coupon)
	{
		if (isset($this->coupons[$coupon->getId()])) {
			return;
		}

		if (is_numeric($coupon->getOrderTotalMinimum()) && $this->total < $coupon->getOrderTotalMinimum()) {
			throw new Exception(sprintf(__('Cannot apply coupon "%s". Order total less than %s.'), $coupon->getCode(), ProductHelper::formatPrice($coupon->getOrderTotalMinimum())));
		}
		if (is_numeric($coupon->getOrderTotalMaximum()) && $this->total > $coupon->getOrderTotalMaximum()) {
			throw new Exception(sprintf(__('Cannot apply coupon "%s". Order total more than %s.'), $coupon->getCode(), ProductHelper::formatPrice($coupon->getOrderTotalMaximum())));
		}

		if ($coupon->isIndividualUse()) {
			$this->removeAllCouponsExcept(array());
		}

		$discount = $coupon->getDiscount($this);
		$this->coupons[$coupon->getId()] = array(
			'object' => $coupon,
			'discount' => $discount,
		);
		$this->discount += $discount;
		$this->total -= $discount;
	}

	/**
	 * @param $id int Coupon ID.
	 */
	public function removeCoupon($id)
	{
		if (!isset($this->coupons[$id])) {
			return;
		}

		$coupon = $this->coupons[$id];
		$this->discount -= $coupon['discount'];
		$this->total += $coupon['discount'];
		unset($this->coupons[$id]);
	}

	/**
	 * Removes all coupons except ones listed in the parameter.
	 *
	 * @param $codes array List of actual coupon codes.
	 */
	public function removeAllCouponsExcept($codes)
	{
		foreach ($this->coupons as $coupon) {
			/** @var Coupon $coupon */
			$coupon = $coupon['object'];
			if (!in_array($coupon->getCode(), $codes)) {
				$this->removeCoupon($coupon->getId());
			}
		}
	}

	/**
	 * @return array Coupons list.
	 */
	public function getCoupons()
	{
		return array_map(function($item){ return $item['object']; }, $this->coupons);
	}

	/**
	 * @return bool Whether cart has coupons applied.
	 */
	public function hasCoupons()
	{
		return !empty($this->coupons);
	}

	/**
	 * @return float Total discount of the cart.
	 */
	public function getDiscount()
	{
		return $this->discount;
	}

	/**
	 * @return array List of tax values per tax class.
	 */
	public function getTax()
	{
		return $this->tax;
	}

	/**
	 * @return array All tax data combined.
	 */
	public function getCombinedTax()
	{
		$tax = $this->tax;
		foreach ($this->shippingTax as $class => $value) {
			$tax[$class] += $value;
		}

		return $tax;
	}

	/**
	 * @return float Total tax of the order.
	 */
	public function getTotalTax()
	{
		if ($this->totalTax === null) {
			$this->totalTax = array_reduce($this->tax, function($value, $item){ return $value + $item; }, 0.0);;
		}

		return $this->totalTax;
	}

	/**
	 * @return Method Currently selected shipping method.
	 */
	public function getShippingMethod()
	{
		return $this->shippingMethod;
	}

	/**
	 * @return float Price of shipping.
	 */
	public function getShippingPrice()
	{
		return $this->shippingPrice;
	}

	/**
	 * Sets shipping method and updates cart totals to reflect it's price.
	 *
	 * @param Method $method New shipping method.
	 */
	public function setShippingMethod(Method $method)
	{
		// TODO: Refactor to abstract between cart and order = AbstractOrder
		$this->removeShippingMethod();

		$this->shippingMethod = $method;
		$this->shippingPrice = $method->calculate($this);
		$this->subtotal += $this->shippingPrice;
		$totalPrice = $this->wp->applyFilters('jigoshop\order\shipping_price', $this->shippingPrice, $method, $this);
		$this->total += $totalPrice;
		$this->shippingTax = $this->wp->applyFilters('jigoshop\order\shipping_tax', $this->shippingTax, $method, $this);
	}

	/**
	 * Removes shipping method and associated taxes from the order.
	 */
	public function removeShippingMethod()
	{
		$this->subtotal -= $this->shippingPrice;
		$this->total -= $this->shippingPrice + array_reduce($this->shippingTax, function($value, $item){ return $value + $item; }, 0.0);

		$this->shippingMethod = null;
		$this->shippingMethodRate = null;
		$this->shippingPrice = 0.0;
		$this->shippingTax = array_map(function() { return 0.0; }, $this->shippingTax);
	}

	/**
	 * Generates representation of current cart state.
	 *
	 * @return string the string representation of the cart or null
	 */
	public function getState()
	{
		return array(
			'id' => $this->id,
			'shipping_method' => $this->shippingMethod !== null ? $this->shippingMethod->getState() : null,
			'coupons' => array_map(function($item){
				/** @var Coupon $coupon */
				$coupon = $item['object'];
				return $coupon->getId();
			}, $this->coupons),
			'items' => serialize($this->items),
		);
	}

	/**
	 * Checks whether given shipping method is set for current cart.
	 *
	 * @param $method Method Shipping method to check.
	 * @param $rate Rate Shipping rate to check.
	 * @return bool Is the method selected?
	 */
	public function hasShippingMethod($method, $rate = null)
	{
		if ($this->shippingMethod !== null) {
			return $this->shippingMethod->is($method, $rate);
		}

		return false;
	}

	/**
	 * @return array List of applied tax classes for shipping with it's values.
	 */
	public function getShippingTax()
	{
		return array();
	}

	/**
	 * @return Customer The customer.
	 */
	public function getCustomer()
	{
		return $this->customer;
	}

	/**
	 * @param Customer $customer
	 */
	public function setCustomer($customer)
	{
		$this->customer = $customer;
	}

	/**
	 * Removes and adds each coupon currently applied to the cart. This causes to recalculate discount values.
	 */
	public function recalculateCoupons()
	{
		foreach ($this->coupons as $data) {
			/** @var Coupon $coupon */
			$coupon = $data['object'];

			$this->removeCoupon($coupon->getId());
			try {
				$this->addCoupon($coupon);
			} catch (Exception $e) {
				// TODO: Some idea how to report this to the user?
			}
		}
	}

	/**
	 * Checks whether at least one item requires shipping.
	 *
	 * @return bool Is shipping required for the cart?
	 */
	public function isShippingRequired()
	{
		$required = false;
		foreach ($this->items as $item) {
			/** @var $item Item */
			$product = $item->getProduct();
			if ($product instanceof Product\Shippable) {
				$required |= $product->isShippable();
			}
		}

		return $required;
	}
}
