<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\EntityInterface;
use WPAL\Wordpress;

/**
 * Product service.
 *
 * @package Jigoshop\Service
 * @author Amadeusz Starzykiewicz
 */
class Product implements ProductServiceInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
	}

	/**
	 * Finds product specified by ID.
	 *
	 * @param $id int Product ID.
	 * @return \Jigoshop\Entity\Product
	 */
	public function find($id)
	{
		$product = new \Jigoshop\Entity\Product();

		if ($id !== null) {
			$post = $this->wp->getPost($id);
			$meta = array_map(function ($item){
				return $item[0];
			}, $this->wp->getPostMeta($id));

			$product->setId($id);
			$product->setName($post->post_title);
			$product->restoreState($meta);
			// TODO: Restoring attributes

			$product = $this->wp->applyFilters('jigoshop\\find\\product', $product, $meta);
		}

		return $product;
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Product Item found.
	 */
	public function findForPost($post)
	{
		$product = new \Jigoshop\Entity\Product();

		$meta = array_map(function ($item){
			return $item[0];
		}, $this->wp->getPostMeta($post->ID));

		$product->setId($post->ID);
		$product->setName($post->post_title);
		$product->restoreState($meta);
		// TODO: Restoring attributes

		$product = $this->wp->applyFilters('jigoshop\\find\\product', $product, $meta);

		return $product;
	}

	/**
	 * Finds items specified using WordPress query.
	 * TODO: Replace \WP_Query in order to make Jigoshop testable
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery($query)
	{
		// Fetch only IDs
		$query->query_vars['fields'] = 'ids';
		$results = $query->get_posts();
		$that = $this;
		// TODO: Maybe it is good to optimize this to fetch all found products data at once?
		$products = array_map(function ($product) use ($that){
			return $that->find($product->ID);
		}, $results);

		return $products;
	}

	/**
	 * Saves product to database.
	 *
	 * @param \Jigoshop\Entity\EntityInterface $object Product to save.
	 * @throws Exception
	 */
	public function save(EntityInterface $object)
	{
		if (!($object instanceof \Jigoshop\Entity\Product)) {
			throw new Exception('Trying to save not a product!');
		}

		$fields = $object->getStateToSave();

		if (isset($fields['id']) || isset($fields['name'])) {
			$this->wp->wpUpdatePost(array(
				'ID' => $object->getId(),
				'post_title' => $object->getName(),
			));
			unset($fields['id'], $fields['name']);
		}

		foreach ($fields as $field => $value) {
			$this->wp->updatePostMeta($object->getId(), $field, $value);
		}

		// TODO: Saving attributes
	}

	/**
	 * @return array List of products that are out of stock.
	 */
	function findOutOfStock()
	{
		// TODO: Replace \WP_Query in order to make Jigoshop testable
		$query = new \WP_Query(array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'stock_manage',
					'value' => 1,
					'compare' => '=',
				),
				array(
					'key' => 'stock_stock',
					'value' => 0,
					'compare' => '=',
				),
			),
		));

		return $this->findByQuery($query);
	}

	/**
	 * @param $threshold int Threshold where to assume product is low in stock.
	 * @return array List of products that are low in stock.
	 */
	function findLowStock($threshold)
	{
		// TODO: Replace \WP_Query in order to make Jigoshop testable
		$query = new \WP_Query(array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'stock_manage',
					'value' => 1,
					'compare' => '=',
				),
				array(
					'key' => 'stock_stock',
					'value' => $threshold,
					'compare' => '<',
				),
			),
		));

		return $this->findByQuery($query);
	}
}