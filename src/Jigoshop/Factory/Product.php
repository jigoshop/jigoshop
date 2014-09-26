<?php

namespace Jigoshop\Factory;

use Jigoshop\Entity\Product\Simple;
use Jigoshop\Exception;
use WPAL\Wordpress;

class Product
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
	 * Returns empty product of selected type.
	 *
	 * @param $type string Type name of product.
	 * @throws \Jigoshop\Exception When product type does not exists.
	 * @return \Jigoshop\Entity\Product
	 */
	public function get($type)
	{
		if (!isset($this->types[$type])) {
			throw new Exception(sprintf('Product type %s does not exists.', $type));
		}

		$class = $this->types[$type];
		return new $class($this->wp);
	}

	/**
	 * Creates new product properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return \Jigoshop\Entity\Product
	 */
	public function create($id)
	{
		$type = isset($_POST['product']['type']) ? $_POST['product']['type'] : Simple::TYPE;
		$product = $this->get($type);
		$product->setId($id);

		if (!empty($_POST)) {
			$product->setName($this->wp->sanitizeTitle($_POST['post_title']));
			$product->setDescription($this->wp->wpautop($this->wp->wptexturize($_POST['post_excerpt'])));
			$product->restoreState($_POST['product']);
			$product->markAsDirty($_POST['product']);
		}

		return $product;
	}

	/**
	 * Fetches product from database.
	 *
	 * @param $post \WP_Post Post to fetch product for.
	 * @return \Jigoshop\Entity\Product
	 */
	public function fetch($post)
	{
		$type = $this->wp->getPostMeta($post->ID, 'type', true);
		if(empty($type)){
			$type = Simple::TYPE;
		}

		$product = $this->get($type);
		$state = array();

		if($post){
			$state = array_map(function ($item){
				return $item[0];
			}, $this->wp->getPostMeta($post->ID));

			$state['attributes'] = $this->getAttributes($post->ID);
			$state['id'] = $post->ID;
			$state['name'] = $post->post_title;
			$state['description'] = $this->wp->wpautop($this->wp->wptexturize($post->post_content));

			$product->restoreState($state);
		}

		return $this->wp->applyFilters('jigoshop\find\product', $product, $state);
	}

	private function getAttributes($id)
	{
		// TODO: Real fetch of product attributes and restoring state
		return array();
	}
}
