<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\EntityInterface;
use Jigoshop\Exception;
use Jigoshop\Factory\Product as ProductFactory;
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
	/** @var \Jigoshop\Factory\Product */
	private $factory;

	public function __construct(Wordpress $wp, ProductFactory $factory)
	{
		$this->wp = $wp;
		$this->factory = $factory;
		$wp->addAction('save_post_'.\Jigoshop\Core\Types\Product::NAME, array($this, 'savePost'), 10);
	}

	/**
	 * Adds new type to managed types.
	 *
	 * @param $type string Unique type name.
	 * @param $class string Class name.
	 * @throws \Jigoshop\Exception When type already exists.
	 */
	public function addType($type, $class)
	{
		$this->factory->addType($type, $class);
	}

	/**
	 * Finds product specified by ID.
	 *
	 * @param $id int Product ID.
	 * @return \Jigoshop\Entity\Product
	 */
	public function find($id)
	{
		$post = null;

		if ($id !== null) {
			$post = $this->wp->getPost($id);
		}

		return $this->factory->fetch($post);
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Product Item found.
	 */
	public function findForPost($post)
	{
		return $this->factory->fetch($post);
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
		$products = array();

		// TODO: Maybe it is good to optimize this to fetch all found products data at once?
		foreach ($results as $product) {
			$products[] = $this->findForPost($product);
		}

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
			// We do not need to save ID or name as they are saved by WordPress itself.
			unset($fields['id'], $fields['name']);
		}

		if (isset($fields['attributes'])) {
			foreach ($fields['attributes']['removed'] as $key) {
				$this->removeProductAttribute($object, $key);
			}

			foreach ($fields['attributes']['new'] as $key => $attribute) {
				$this->saveProductAttribute($object, $key, $attribute);
			}

			unset($fields['attributes']);
		}

		foreach ($fields as $field => $value) {
			$this->wp->updatePostMeta($object->getId(), $field, $value);
		}
	}

	/**
	 * @return array List of products that are out of stock.
	 */
	public function findOutOfStock()
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
	public function findLowStock($threshold)
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

	private function removeProductAttribute($object, $key)
	{
		// TODO: Real remove of the attribute
	}

	private function saveProductAttribute($object, $key, $attribute)
	{
		// TODO: Real save of the attribute
	}

	private function getProductAttributes($id)
	{
		// TODO: Real fetch of product attributes and restoring state
		return array();
	}

	/**
	 * Save the product data upon post saving.
	 *
	 * @param $id int Post ID.
	 */
	public function savePost($id)
	{
		$product = $this->factory->create($id);
		$this->save($product);
	}
}
