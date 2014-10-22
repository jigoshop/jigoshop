<?php

namespace Jigoshop\Entity;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer\Guest;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Exception;
use Jigoshop\Payment\Method as PaymentMethod;
use Jigoshop\Shipping\Method as ShippingMethod;
use WPAL\Wordpress;

/**
 * Order class.
 * TODO: Fully implement the class.
 *
 * @package Jigoshop\Entity
 * @author Amadeusz Starzykiewicz
 */
class Order implements EntityInterface
{
	/** @var int */
	private $id;
	/** @var string */
	private $number;
	/** @var \DateTime */
	private $created_at;
	/** @var \DateTime */
	private $updated_at;
	/** @var Customer */
	private $customer;
	/** @var array */
	private $items = array();
	/** @var Order\Address */
	private $billingAddress;
	/** @var Order\Address */
	private $shippingAddress;
	/** @var ShippingMethod */
	private $shipping;
	/** @var PaymentMethod */
	private $payment;
	/** @var float */
	private $productSubtotal;
	/** @var float */
	private $subtotal = 0.0;
	/** @var float */
	private $total = 0.0;
	/** @var float */
	private $discount = 0.0;
	/** @var array */
	private $tax = array();
	/** @var string */
	private $status = Status::CREATED;
	/** @var string */
	private $customerNote;

	/** @var \WPAL\Wordpress */
	protected $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;

		$this->customer = new Guest();
		$this->billingAddress = new Order\Address();
		$this->shippingAddress = new Order\Address();
		$this->created_at = new \DateTime();
		$this->updated_at = new \DateTime();
	}

	/**
	 * Adds a note to the order.
	 *
	 * @param $note string Note text.
	 * @param $private bool Is note private?
	 * @return int Note ID.
	 */
	public function addNote($note, $private = true)
	{
		// TODO: Remove WP calls
		$comment = array(
			'comment_post_ID' => $this->id,
			'comment_author' => __('Jigoshop', 'jigoshop'),
			'comment_author_email' => '',
			'comment_author_url' => '',
			'comment_content' => $note,
			'comment_type' => 'order_note',
			'comment_agent' => __('Jigoshop', 'jigoshop'),
			'comment_parent' => 0,
			'comment_date' => current_time('timestamp'),
			'comment_date_gmt' => current_time('timestamp', true),
			'comment_approved' => true
		);

		$comment_id = wp_insert_comment($comment);
		add_comment_meta($comment_id, 'private', $private);

		return $comment_id;
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
	 * @return Order\Address Billing address.
	 */
	public function getBillingAddress()
	{
		return $this->billingAddress;
	}

	/**
	 * @param Order\Address $billingAddress
	 */
	public function setBillingAddress($billingAddress)
	{
		$this->billingAddress = $billingAddress;
	}

	/**
	 * @return Order\Address Shipping address.
	 */
	public function getShippingAddress()
	{
		return $this->shippingAddress;
	}

	/**
	 * @param Order\Address $shippingAddress
	 */
	public function setShippingAddress($shippingAddress)
	{
		$this->shippingAddress = $shippingAddress;
	}

	/**
	 * @return \DateTime Time the order was created at.
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @param \DateTime $created_at Creation time.
	 */
	public function setCreatedAt($created_at)
	{
		$this->created_at = $created_at;
	}

	/**
	 * @return \DateTime Time the order was updated at.
	 */
	public function getUpdatedAt()
	{
		return $this->updated_at;
	}

	/**
	 * @param \DateTime $updated_at Last update time.
	 */
	public function setUpdatedAt($updated_at)
	{
		$this->updated_at = $updated_at;
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
	 * @return array List of items bought.
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @param Item $item Item to add.
	 */
	public function addItem(Item $item)
	{
		$this->items[$item->getId()] = $item;
		$this->productSubtotal += $item->getCost();
		$this->subtotal += $item->getCost();
		$this->total += $item->getCost();
	}

	/**
	 * @param $item int Item ID to remove.
	 * @return Item Removed item.
	 */
	public function removeItem($item)
	{
		$item = $this->items[$item];
		unset($this->items[$item->getId()]);

		/** @var Item $itemId */
		$this->productSubtotal -= $item->getCost();
		$this->subtotal -= $item->getCost();
		$this->total -= $item->getCost();

		return $item;
	}

	/**
	 * Returns item of selected ID.
	 *
	 * @param $item int Item ID to fetch.
	 * @return Item Order item.
	 * @throws Exception When item is not found.
	 */
	public function getItem($item)
	{
		if (!isset($this->items[$item])) {
			throw new Exception(sprintf(__('No item with ID %d in order %d', 'jigoshop'), $item, $this->id));
		}

		return $this->items[$item];
	}

	/**
	 * @return PaymentMethod Payment gateway object.
	 */
	public function getPayment()
	{
		return $this->payment;
	}

	/**
	 * @param PaymentMethod $payment Method used to pay.
	 */
	public function setPayment($payment)
	{
		$this->payment = $payment;
	}

	/**
	 * @return ShippingMethod Shipping method.
	 */
	public function getShipping()
	{
		return $this->shipping;
	}

	/**
	 * @param ShippingMethod $shipping Method used for shipping the order.
	 */
	public function setShipping($shipping)
	{
		$this->shipping = $shipping;
	}

	/**
	 * @return string Current order status.
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param string $status New order status.
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * @param $status string New order status.
	 * @param $message string Message to add.
	 * @since 2.0
	 */
	public function updateStatus($status, $message = '')
	{
		if ($status) {
			if ($this->status != $status) {
				// Do actions for changing statuses
				$this->wp->doAction('jigoshop\order\before\\'.$status, $this);
				$this->wp->doAction('jigoshop\order\\'.$this->status.'_to_'.$status, $this);

				$this->addNote($message.sprintf(__('Order status changed from %s to %s.', 'jigoshop'), Status::getName($this->status), Status::getName($status)));
				$this->status = $status;

				// Date
				if ($status == Status::COMPLETED) {
					// TODO: Add completion date and save overall quantity sold.
//					update_post_meta($this->id, 'completed_date', current_time('timestamp'));
//					foreach ($this->items as $item) {
//						/** @var \Jigoshop\Entity\Order\Item $item */
//						$sales = get_post_meta($item->getProduct()->getId(), 'quantity_sold', true) + $item->getQuantity();
//						update_post_meta($item->getProduct()->getId(), 'quantity_sold', $sales);
//					}
				}

				$this->wp->doAction('jigoshop\order\after\\'.$status, $this);
			}
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
	 * @return float Total tax of the order.
	 */
	public function getTotalTax()
	{
		$tax = 0.0;
		foreach ($this->tax as $value) {
			$tax += $value;
		}

		return $tax;
	}

	/**
	 * @param array $tax Tax data array.
	 */
	public function setTax($tax)
	{
		$this->tax = $tax;
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		return array(
			'id' => $this->id,
			'number' => $this->number,
			'updated_at' => $this->updated_at->getTimestamp(),
			'items' => $this->items, // TODO: Store items
			'billing_address' => serialize($this->billingAddress),
			'shipping_address' => serialize($this->shippingAddress),
			'customer' => $this->customer->getId(),
			'shipping' => $this->shipping ? $this->shipping->getState() : false,
			'payment' => $this->payment ? $this->payment->getId() : false, // TODO: Maybe a state as for shipping methods?
			'customer_note' => $this->customerNote,
			'total' => $this->total,
			'subtotal' => $this->subtotal,
			'discount' => $this->discount,
			'tax' => serialize($this->tax),
			'status' => $this->status,
		);
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		if (isset($state['number'])) {
			$this->number = $state['number'];
		}
		if (isset($state['updated_at'])) {
			$this->updated_at->setTimestamp($state['updated_at']);
		}
		if (isset($state['items'])) {
			foreach ($state['items'] as $item) {
				/** @var $item Item */
				$this->items[$item->getId()] = $item;
			}
		}
		if (isset($state['billing_address'])) {
			$this->billingAddress = unserialize($state['billing_address']);
		}
		if (isset($state['shipping_address'])) {
			$this->shippingAddress = unserialize($state['shipping_address']);
		}
		if (isset($state['customer'])) {
			$this->customer = $state['customer'];
		}
		if (isset($state['shipping']) && !empty($state['shipping'])) {
			$this->shipping = $state['shipping'];
		}
		if (isset($state['payment']) && !empty($state['payment'])) {
			$this->payment = $state['payment'];
		}
		if (isset($state['customer_note'])) {
			$this->customerNote = $state['customer_note'];
		}
		if (isset($state['total'])) {
			$this->total = (float)$state['total'];
		}
		if (isset($state['subtotal'])) {
			$this->subtotal = (float)$state['subtotal'];
		}
		if (isset($state['product_subtotal'])) {
			$this->productSubtotal = (float)$state['product_subtotal'];
		}
		if (isset($state['discount'])) {
			$this->discount = (float)$state['discount'];
		}
		if (isset($state['tax']) && !empty($state['payment'])) {
			$this->tax = unserialize($state['tax']);
		}
		if (isset($state['status'])) {
			$this->status = $state['status'];
		}
	}
}
