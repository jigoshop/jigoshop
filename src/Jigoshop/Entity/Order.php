<?php

namespace Jigoshop\Entity;

/**
 * Order class.
 * TODO: Fully implement the class.
 *
 * @package Jigoshop\Entity
 * @author Jigoshop
 */
class Order implements EntityInterface
{
	private $id;
	private $date;
	private $update_date;
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
			'pending' => __('Pending', 'jigoshop'),
			'on-hold' => __('On-Hold', 'jigoshop'),
			'processing' => __('Processing', 'jigoshop'),
			'completed' => __('Completed', 'jigoshop'),
			'cancelled' => __('Cancelled', 'jigoshop'),
			'refunded' => __('Refunded', 'jigoshop'),
			'failed' => __('Failed', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
			'denied' => __('Denied', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
			'expired' => __('Expired', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
			'voided' => __('Voided', 'jigoshop'), /* can be set from PayPal, not currently shown anywhere -JAP- */
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

		if($new_status)
		{
			wp_set_object_terms($this->id, array($new_status->slug), 'shop_order_status', false);

			if($this->status != $new_status->slug)
			{
//				do_action('order_status_'.$new_status->slug, $this->id);
//				do_action('order_status_'.$this->status.'_to_'.$new_status->slug, $this->id);
				$this->addNote($message.sprintf(__('Order status changed from %s to %s.', 'jigoshop'), __($old_status->name, 'jigoshop'), __($new_status->name, 'jigoshop')));

				// Date
				if($new_status->slug == 'completed')
				{
					update_post_meta($this->id, 'completed_date', current_time('timestamp'));
					foreach($this->items as $item)
					{
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
}