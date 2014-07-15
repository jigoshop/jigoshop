<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\EntityInterface;
use Jigoshop\Exception;
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
	private $types = array();

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
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
		if (isset($this->types[$type])) {
			throw new Exception(sprintf('Product of type %s already exists.'), $type);
		}

		$this->types[$type] = $class;
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

		return $this->findForPost($post);
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Product Item found.
	 */
	public function findForPost($post)
	{
		$type = $this->wp->getPostMeta($post->ID, 'type', true);
		$product = $this->getProductForType($type);
		$meta = array();

		if($post){
			$meta = array_map(function ($item){
				return $item[0];
			}, $this->wp->getPostMeta($post->ID));

			$meta['attributes'] = $this->getProductAttributes($post->ID);

			$product->setId($post->ID);
			$product->setName($post->post_title);
			$product->restoreState($meta);
		}

		return $this->wp->applyFilters('jigoshop\\find\\product', $product, $meta);
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

	/**
	 * @param $type string Type name of product.
	 * @throws \Jigoshop\Exception When product type does not exists.
	 * @return \Jigoshop\Entity\Product
	 */
	private function getProductForType($type)
	{
		if (!isset($this->types[$type])) {
			throw new Exception(sprintf('Product type %s does not exists.', $type));
		}

		$class = $this->types[$type];
		return new $class($this->wp);
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
}