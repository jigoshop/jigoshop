<?php

namespace Jigoshop\Entity;

use Jigoshop\Entity\Order\Status;

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
	private $date;
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

	/**
	 * @return array List of available order statuses.
	 */
	public function getStatuses()
	{
		$order_types = array(
			Status::CREATED => __('New', 'jigoshop'),
			Status::PENDING => __('Pending', 'jigoshop'),
			Status::ON_HOLD => __('On-Hold', 'jigoshop'),
			Status::PROCESSING => __('Processing', 'jigoshop'),
			Status::COMPLETED => __('Completed', 'jigoshop'),
			Status::CANCELLED => __('Cancelled', 'jigoshop'),
			Status::REFUNDED => __('Refunded', 'jigoshop'),
		);

		return apply_filters('jigoshop_filter_order_status_names', $order_types);
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
		// TODO: Implement getId() method.
	}

	/**
	 * @return int Order number.
	 */
	public function getNumber()
	{
		return 0; // TODO: Implement
	}

	/**
	 * @return mixed
	 */
	public function getBillingAddress()
	{
		return $this->billingAddress;
	}

	/**
	 * @return mixed
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @return mixed
	 */
	public function getCustomer()
	{
		return $this->customer;
	}

	/**
	 * @return mixed
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @return mixed
	 */
	public function getDiscount()
	{
		return $this->discount;
	}

	/**
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return mixed
	 */
	public function getPayment()
	{
		return $this->payment;
	}

	/**
	 * @return mixed
	 */
	public function getShipping()
	{
		return $this->shipping;
	}

	/**
	 * @return mixed
	 */
	public function getShippingAddress()
	{
		return $this->shippingAddress;
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return mixed
	 */
	public function getSubtotal()
	{
		return $this->subtotal;
	}

	/**
	 * @return mixed
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
	public function getNote()
	{
		// TODO: Implement getNote() method.
		return 'Note';
	}
}
