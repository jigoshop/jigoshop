<?php

namespace Jigoshop\Factory;

use Jigoshop\Entity\Coupon as Entity;
use WPAL\Wordpress;

/**
 * Coupon factory.
 *
 * @package Jigoshop\Factory
 */
class Coupon implements EntityFactoryInterface
{
	/** @var Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
	}

	/**
	 * Creates new product properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return \Jigoshop\Entity\Product
	 */
	public function create($id)
	{
		$coupon = new Entity();
		$coupon->setId($id);

		if (!empty($_POST)) {
			$helpers = $this->wp->getHelpers();
			$coupon->setTitle($helpers->sanitizeTitle($_POST['post_title']));

			$coupon->restoreState($_POST['jigoshop_coupon']);
		}

		return $coupon;
	}

	/**
	 * Fetches product from database.
	 *
	 * @param $post \WP_Post Post to fetch product for.
	 * @return \Jigoshop\Entity\Product
	 */
	public function fetch($post)
	{
		$coupon = new Entity();
		$state = array();

		if($post){
			$state = array_map(function ($item){
				return $item[0];
			}, $this->wp->getPostMeta($post->ID));

			$coupon->setId($post->ID);
			$coupon->setTitle($post->post_title);

			$state['products'] = unserialize($state['products']);
			$state['excluded_products'] = unserialize($state['excluded_products']);
			$state['categories'] = unserialize($state['categories']);
			$state['excluded_categories'] = unserialize($state['excluded_categories']);
			$state['payment_methods'] = unserialize($state['payment_methods']);

			$coupon->restoreState($state);
		}

		return $this->wp->applyFilters('jigoshop\find\coupon', $coupon, $state);
	}
}
