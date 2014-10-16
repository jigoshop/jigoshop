<?php

namespace Jigoshop\Entity;

use Jigoshop\Entity\Customer\Guest;
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
	private $id;
	private $number;
	private $created_at;
	private $updated_at;
	private $customer;
	private $items;
	private $billingAddress;
	private $shippingAddress;
	private $shipping;
	private $payment;
	private $subtotal;
	private $discount;
	private $tax;
	private $status;
	private $customerNote;

	/** @var \WPAL\Wordpress */
	protected $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$this->customer = new Guest();
		$this->billingAddress = new Order\Address();
		$this->shippingAddress = new Order\Address();
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
	 * @param $status string New status slug.
	 * @param $message string Message to add.
	 * @since 2.0
	 */
	public function updateStatus($status, $message = '')
	{
		// TODO: Update order status
		$old_status = get_term_by('slug', $this->status, 'shop_order_status');
		$new_status = get_term_by('slug', $status, 'shop_order_status');

		if ($new_status) {
			wp_set_object_terms($this->id, array($new_status->slug), 'shop_order_status', false);

			if ($this->status != $new_status->slug) {
//				do_action('order_status_'.$new_status->slug, $this->id);
//				do_action('order_status_'.$this->status.'_to_'.$new_status->slug, $this->id);
				$this->addNote($message.sprintf(__('Order status changed from %s to %s.', 'jigoshop'), __($old_status->name, 'jigoshop'), __($new_status->name, 'jigoshop')));

				// Date
				if ($new_status->slug == 'completed') {
					update_post_meta($this->id, 'completed_date', current_time('timestamp'));
					foreach ($this->items as $item) {
						/** @var \Jigoshop\Entity\Order\Item $item */
						$sales = get_post_meta($item->getProduct()->getId(), 'quantity_sold', true) + $item->getQuantity();
						update_post_meta($item->getProduct()->getId(), 'quantity_sold', $sales);
					}
				}
			}
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
	 * @return Order\Address Billing address.
	 */
	public function getBillingAddress()
	{
		return $this->billingAddress;
	}

	/**
	 * @return Order\Address Shipping address.
	 */
	public function getShippingAddress()
	{
		return $this->shippingAddress;
	}

	/**
	 * @return mixed
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @return Customer The customer.
	 */
	public function getCustomer()
	{
		return $this->customer;
	}

	/**
	 * @return float Value of discounts added to the order.
	 */
	public function getDiscount()
	{
		return $this->discount;
	}

	/**
	 * @return array List of items bought.
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return string Payment gateway ID.
	 */
	public function getPayment()
	{
		return $this->payment;
	}

	/**
	 * @return string Shipping method ID.
	 */
	public function getShipping()
	{
		return $this->shipping;
	}

	/**
	 * @return string Current order status.
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return float Subtotal value of the cart.
	 */
	public function getSubtotal()
	{
		return $this->subtotal;
	}

	/**
	 * @return array List of applied tax classes with it's values.
	 */
	public function getTax()
	{
		return $this->tax;
	}

	/**
	 * @return mixed
	 */
	public function getUpdatedAt()
	{
		return $this->updated_at;
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		// TODO: Implement getStateToSave() method.
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		// TODO: Implement restoreState() method.
	}

	/**
	 * @return string Customer's note on the order.
	 */
	public function getCustomerNote()
	{
		return $this->customerNote;
	}
}
