<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Order as Entity;
use WPAL\Wordpress;

class Order implements EntityFactoryInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
	}

	/**
	 * Creates new order properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return Entity
	 */
	public function create($id)
	{
		$order = new Entity($this->wp);
		$order->setId($id);

		if (!empty($_POST)) {
//			$order->setName($this->wp->sanitizeTitle($_POST['post_title']));
//			$order->setDescription($this->wp->wpautop($this->wp->wptexturize($_POST['post_excerpt'])));
//			$_POST['product']['categories'] = $this->getTerms($id, Types::PRODUCT_CATEGORY, $this->wp->getTerms(Types::PRODUCT_CATEGORY, array(
//				'posts__in' => $_POST['tax_input']['product_category'],
//			)));
//			$_POST['product']['tags'] = $this->getTerms($id, Types::PRODUCT_TAG, $this->wp->getTerms(Types::PRODUCT_TAG, array(
//				'posts__in' => $_POST['tax_input']['product_tag'],
//			)));
//
//			if (!isset($_POST['product']['tax_classes'])) {
//				$_POST['product']['tax_classes'] = array();
//			}
//
//			$order->restoreState($_POST['product']);
//			$order->markAsDirty($_POST['product']);
		}

		return $order;
	}

	/**
	 * Fetches order from database.
	 *
	 * @param $post \WP_Post Post to fetch order for.
	 * @return \Jigoshop\Entity\Order
	 */
	public function fetch($post)
	{
		$order = new Entity($this->wp);
		$state = array();

//		if($post){
//			$state = array_map(function ($item){
//				return $item[0];
//			}, $this->wp->getPostMeta($post->ID));
//
//			$state['id'] = $post->ID;
//			$state['name'] = $post->post_title;
//			$state['description'] = $this->wp->wpautop($this->wp->wptexturize($post->post_content));
//
//			$order->restoreState($state);
//		}

		return $this->wp->applyFilters('jigoshop\find\order', $order, $state);
	}
}
