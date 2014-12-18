<?php

namespace Jigoshop\Entity;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer\Guest;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Exception;
use Jigoshop\Payment\Method as PaymentMethod;
use Jigoshop\Shipping\Method;
use Jigoshop\Shipping\Method as ShippingMethod;
use Jigoshop\Shipping\Rate;
use Monolog\Registry;
use WPAL\Wordpress;

/**
 * Order class.
 *
 * @package Jigoshop\Entity
 * @author Amadeusz Starzykiewicz
 */
class Order implements EntityInterface, OrderInterface
{
	/** @var int */
	private $id;
	/** @var string */
	private $key;
	/** @var string */
	private $number;
	/** @var \DateTime */
	private $createdAt;
	/** @var \DateTime */
	private $updatedAt;
	/** @var \DateTime */
	private $completedAt;
	/** @var Customer */
	private $customer;
	/** @var array */
	private $items = array();
	/** @var ShippingMethod */
	private $shippingMethod;
	/** @var int */
	private $shippingMethodRate;
	/** @var PaymentMethod */
	private $paymentMethod;
	/** @var float */
	private $productSubtotal;
	/** @var float */
	private $subtotal = 0.0;
	/** @var float */
	private $total = 0.0;
	/** @var float */
	private $discount = 0.0;
	/** @var array */
	private $coupons = array();
	/** @var array */
	private $tax = array();
	/** @var array */
	private $shippingTax = array();
	/** @var float */
	private $totalTax;
	/** @var float */
	private $shippingPrice = 0.0;
	/** @var string */
	private $status = Status::PENDING;
	/** @var string */
	private $customerNote;
	/** @var array */
	private $updateMessages = array();

	/** @var \WPAL\Wordpress */
	protected $wp;

	public function __construct(Wordpress $wp, array $taxClasses)
	{
		$this->wp = $wp;

		$this->customer = new Guest();
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
		$this->totalTax = null;

		foreach ($taxClasses as $class) {
			$this->tax[$class['class']] = 0.0;
			$this->shippingTax[$class['class']] = 0.0;
		}
	}

	/**
	 * @return int Entity ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param $id int Order ID.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string Title of the order.
	 */
	public function getTitle()
	{
		return sprintf(__('Order %d', 'jigoshop'), $this->getNumber());
	}

	/**
	 * @return int Order number.
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * @param string $number The order number.
	 */
	public function setNumber($number)
	{
		$this->number = $number;
	}

	/**
	 * @return string Order security key.
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $key New security key for the order.
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @return \DateTime Time the order was created at.
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @param \DateTime $createdAt Creation time.
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
	}

	/**
	 * @return \DateTime Time the order was updated at.
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	/**
	 * @param \DateTime $updatedAt Last update time.
	 */
	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;
	}

	/**
	 * Updates completion time to current date.
	 */
	public function setCompletedAt()
	{
		$this->completedAt = new \DateTime();
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
	 * @return float Value of discounts added to the order.
	 */
	public function getDiscount()
	{
		return $this->discount;
	}

	/**
	 * @param float $discount Total value of discounts for the order.
	 */
	public function setDiscount($discount)
	{
		$this->discount = $discount;
	}

	/**
	 * @return array List of used coupons codes.
	 */
	public function getCoupons()
	{
		return $this->coupons;
	}

	/**
	 * @param array $coupons New list of used coupons codes.
	 */
	public function setCoupons($coupons)
	{
		$this->coupons = $coupons;
	}

	/**
	 * @param string $coupon New code of used coupon.
	 */
	public function addCoupon($coupon)
	{
		if (array_search($coupon, $this->coupons) === false) {
			$this->coupons[] = $coupon;
		}
	}

	/**
	 * @param string $coupon Code to remove.
	 */
	public function removeCoupon($coupon)
	{
		$key = array_search($coupon, $this->coupons);
		if ($key !== false) {
			unset($this->coupons[$key]);
		}
	}

	/**
	 * @return array List of items bought.
	 */
	public function getItems()
	{
		return $this->items;
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
	 * Returns item of selected key.
	 *
	 * @param $item string Item key to fetch.
	 * @return Item Order item.
	 * @throws Exception When item is not found.
	 */
	public function getItem($item)
	{
		if (!isset($this->items[$item])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf(__('No item with ID %d in order %d', 'jigoshop'), $item, $this->id));
			}

			Registry::getInstance('jigoshop')->addWarning(sprintf('No item with ID %d in order %d', $item, $this->id));
			return null;
		}

		return $this->items[$item];
	}

	/**
	 * @param Item $item Item to add.
	 */
	public function addItem(Item $item)
	{
		$this->items[$item->getKey()] = $item;
		$this->productSubtotal += $item->getCost();
		$this->subtotal += $item->getCost();
		$this->total += $item->getCost() + $item->getTotalTax();
		$item = $this->wp->applyFilters('jigoshop\order\add_item', $item, $this);

		foreach ($item->getTax() as $class => $tax) {
			$this->tax[$class] += $tax * $item->getQuantity();
		}
		$this->totalTax = null;
	}

	/**
	 * @param $item int Item ID to remove.
	 * @return Item Removed item.
	 */
	public function removeItem($item)
	{
		$item = $this->items[$item];

		/** @var Item $item */
		$this->productSubtotal -= $item->getCost();
		$this->subtotal -= $item->getCost();
		$this->total -= $item->getCost() + $item->getTotalTax();

		foreach ($item->getTax() as $class => $tax) {
			$this->tax[$class] -= $tax * $item->getQuantity();
		}

		$this->totalTax = null;
		unset($this->items[$item->getId()]);
		return $item;
	}

	/**
	 * @return PaymentMethod Payment gateway object.
	 */
	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}

	/**
	 * @param PaymentMethod $payment Method used to pay.
	 */
	public function setPaymentMethod($payment)
	{
		$this->paymentMethod = $payment;
	}

	/**
	 * @return float
	 */
	public function getShippingPrice()
	{
		return $this->shippingPrice;
	}

	/**
	 * @return ShippingMethod Shipping method.
	 */
	public function getShippingMethod()
	{
		return $this->shippingMethod;
	}

	/**
	 * @param ShippingMethod $method Method used for shipping the order.
	 */
	public function setShippingMethod(ShippingMethod $method)
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
	 * Checks whether given shipping method is set for current cart.
	 *
	 * @param $method Method Shipping method to check.
	 * @param $rate Rate Shipping rate to check.
	 * @return bool Is the method selected?
	 */
	public function hasShippingMethod($method, $rate = null)
	{
		if ($this->shippingMethod != null) {
			return $this->shippingMethod->is($method, $rate);
		}

		return false;
	}

	/**
	 * @return string Current order status.
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param string $status Status to set.
	 * @param string $message Message to add with status change.
	 */
	public function setStatus($status, $message = '')
	{
		$currentStatus = $this->status;
		$this->status = $status;

		if ($currentStatus != $status) {
			$this->updateMessages[] = array(
				'message' => $message,
				'old_status' => $currentStatus,
				'new_status' => $status,
			);
		}
	}

	/**
	 * @return string Customer's note on the order.
	 */
	public function getCustomerNote()
	{
		return $this->customerNote;
	}

	/**
	 * @param string $customerNote Customer's note on the order.
	 */
	public function setCustomerNote($customerNote)
	{
		$this->customerNote = $customerNote;
	}

	/**
	 * @return float
	 */
	public function getProductSubtotal()
	{
		return $this->productSubtotal;
	}

	/**
	 * @param float $productSubtotal
	 */
	public function setProductSubtotal($productSubtotal)
	{
		$this->productSubtotal = $productSubtotal;
	}

	/**
	 * @return float Subtotal value of the cart.
	 */
	public function getSubtotal()
	{
		return $this->subtotal;
	}

	/**
	 * @param float $subtotal New subtotal value.
	 */
	public function setSubtotal($subtotal)
	{
		$this->subtotal = $subtotal;
	}

	/**
	 * @return float Total value of the cart.
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * @param float $total New total value.
	 */
	public function setTotal($total)
	{
		$this->total = $total;
	}

	/**
	 * @return array List of applied tax classes with it's values.
	 */
	public function getTax()
	{
		return $this->tax;
	}

	/**
	 * @param array $tax Tax data array.
	 */
	public function setTax($tax)
	{
		$this->totalTax = null;
		$this->tax = $tax;
	}

	/**
	 * @return array List of applied tax classes for shipping with it's values.
	 */
	public function getShippingTax()
	{
		return $this->shippingTax;
	}

	/**
	 * @param array $shippingTax Tax data array for shipping.
	 */
	public function setShippingTax($shippingTax)
	{
		$this->shippingTax = $shippingTax;
	}

	/**
	 * @return array All tax data combined.
	 */
	public function getCombinedTax()
	{
		$tax = $this->tax;
		foreach ($this->shippingTax as $class => $value) {
			if (!isset($tax[$class])) {
				$tax[$class] = 0.0;
			}

			$tax[$class] += $value;
		}

		return $tax;
	}

	/**
	 * Updates stored tax array with provided values.
	 *
	 * @param array $tax Tax divided by classes.
	 */
	public function updateTaxes(array $tax)
	{
		$this->totalTax = null;
		foreach ($tax as $class => $value) {
			$this->tax[$class] += $value;
		}
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
	 * Updates quantity of selected item by it's key.
	 *
	 * @param $key string Item key in the order.
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
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		return array(
			'id' => $this->id,
			'number' => $this->number,
			'updated_at' => $this->updatedAt->getTimestamp(),
			'completed_at' => $this->completedAt ? $this->completedAt->getTimestamp() : 0,
			'items' => $this->items,
			'customer' => serialize($this->customer),
			'customer_id' =>  $this->customer->getId(),
			'shipping' => array(
				'method' => $this->shippingMethod ? $this->shippingMethod->getState() : false,
				'price' => $this->shippingPrice,
				'rate' => $this->shippingMethodRate,
			),
			'payment' => $this->paymentMethod ? $this->paymentMethod->getId() : false,
			'customer_note' => $this->customerNote,
			'total' => $this->total,
			'subtotal' => $this->subtotal,
			'discount' => $this->discount,
			'coupons' => $this->coupons,
			'shipping_tax' => $this->shippingTax,
			'status' => $this->status,
			'update_messages' => $this->updateMessages,
		);
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		if (isset($state['key'])) {
			$this->key = $state['key'];
		}
		if (isset($state['number'])) {
			$this->number = $state['number'];
		}
		if (isset($state['created_at'])) {
			$this->createdAt->setTimestamp($state['created_at']);
		}
		if (isset($state['updated_at'])) {
			$this->updatedAt->setTimestamp($state['updated_at']);
		}
		if (isset($state['completed_at'])) {
			$this->completedAt = new \DateTime();
			$this->completedAt->setTimestamp($state['completed_at']);
		}
		if (isset($state['status'])) {
			$this->status = $state['status'];
		}
		if (isset($state['items'])) {
			foreach ($state['items'] as $item) {
				$this->addItem($item);
			}
		}
		if (isset($state['customer']) && $state['customer'] !== false) {
			$this->customer = $state['customer'];
		}
		if (isset($state['shipping']) && is_array($state['shipping'])) {
			$this->shippingMethod = $state['shipping']['method'];
			$this->shippingPrice = $state['shipping']['price'];
			$this->shippingMethodRate = $state['shipping']['rate'];
		}
		if (isset($state['payment']) && !empty($state['payment'])) {
			$this->paymentMethod = $state['payment'];
		}
		if (isset($state['customer_note'])) {
			$this->customerNote = $state['customer_note'];
		}
		if (isset($state['shipping_tax'])) {
			$tax = unserialize($state['shipping_tax']);
			foreach ($tax as $class => $value) {
				if (!isset($this->shippingTax[$class])) {
					$this->shippingTax[$class] = 0.0;
				}

				$this->shippingTax[$class] += $value;
			}
		}
		if (isset($state['product_subtotal'])) {
			$this->productSubtotal = (float)$state['product_subtotal'];
		}
		if (isset($state['subtotal'])) {
			$this->subtotal = (float)$state['subtotal'];
		}
		if (isset($state['discount'])) {
			$this->discount = (float)$state['discount'];
		}
		if (isset($state['coupons'])) {
			$this->coupons = unserialize($state['coupons']);
		}

		$this->total = $this->subtotal + array_reduce($this->tax, function($value, $item){ return $value + $item; }, 0.0)
			+ array_reduce($this->shippingTax, function($value, $item){ return $value + $item; }, 0.0) - $this->discount;
	}
}
