<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Product\Attributes\Attribute;
use Jigoshop\Entity\Product\Simple;
use Jigoshop\Exception;
use WPAL\Wordpress;

class Product implements EntityFactoryInterface
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
			$_POST['product']['categories'] = $this->getTerms($id, Types::PRODUCT_CATEGORY, $this->wp->getTerms(Types::PRODUCT_CATEGORY, array(
				'posts__in' => $_POST['tax_input']['product_category'],
			)));
			$_POST['product']['tags'] = $this->getTerms($id, Types::PRODUCT_TAG, $this->wp->getTerms(Types::PRODUCT_TAG, array(
				'posts__in' => $_POST['tax_input']['product_tag'],
			)));

			if (!isset($_POST['product']['tax_classes'])) {
				$_POST['product']['tax_classes'] = array();
			}

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
			$state['categories'] = $this->getTerms($post->ID, Types::PRODUCT_CATEGORY);
			$state['tags'] = $this->getTerms($post->ID, Types::PRODUCT_TAG);

			if (isset($state['tax_classes'])) {
				$state['tax_classes'] = unserialize($state['tax_classes']);
			}

			$product->restoreState($state);
		}

		return $this->wp->applyFilters('jigoshop\find\product', $product, $state);
	}

	private function getTerms($id, $taxonomy, $items = null)
	{
		$wp = $this->wp;
		if ($items === null) {
			$items = $wp->getTheTerms($id, $taxonomy);
		}

		if (!is_array($items)) {
			return array();
		}

		return array_map(function($item) use ($wp, $taxonomy) {
			return array(
				'id' => $item->term_id,
				'name' => $item->name,
				'slug' => $item->slug,
				'link' => $wp->getTermLink($item, $taxonomy),
				'object' => $item,
			);
		}, $items);
	}

	/**
	 * Fetches attribute for selected ID.
	 *
	 * If attribute is not found - returns null.
	 *
	 * @param int $id Attribute ID.
	 * @return Attribute
	 */
	public function getAttribute($id)
	{
		$wpdb = $this->wp->getWPDB();
		$query = $wpdb->prepare("
		SELECT a.id, a.is_local, a.slug, a.label, a.type, ao.id AS option_id, ao.value AS option_value, ao.label as option_label
		FROM {$wpdb->prefix}jigoshop_attribute a
			LEFT JOIN {$wpdb->prefix}jigoshop_attribute_option ao ON a.id = ao.attribute_id
			WHERE a.id = %d
		", array($id));

		$results = $wpdb->get_results($query, ARRAY_A);

		if (count($results) == 0) {
			return null;
		}

		$i = 0;
		$endI = count($results);
		$attribute = new Attribute();
		$attribute->setId((int)$results[$i]['id']);
		$attribute->setSlug($results[$i]['slug']);
		$attribute->setLabel($results[$i]['label']);
		$attribute->setType((int)$results[$i]['type']);
		$attribute->setLocal((bool)$results[$i]['is_local']);

		while ($i < $endI && $results[$i]['id'] == $attribute->getId()) {
			$option = new Attribute\Option();
			$option->setId($results[$i]['option_id']);
			$option->setLabel($results[$i]['option_label']);
			$option->setValue($results[$i]['option_value']);
			$option->setAttribute($attribute);
			$attribute->addOption($option);
			$i++;
		}

		return $attribute;
	}

	/**
	 * Finds and returns list of attributes associated with selected product by it's ID.
	 *
	 * @param $productId int Product ID.
	 * @return array List of attributes attached to selected product.
	 */
	public function getAttributes($productId)
	{
		$wpdb = $this->wp->getWPDB();
		$query = $wpdb->prepare("
		SELECT a.id, a.slug, a.label, a.type, ao.id AS option_id, ao.value AS option_value, ao.label as option_label, pa.value
		FROM {$wpdb->prefix}jigoshop_attribute a
			LEFT JOIN {$wpdb->prefix}jigoshop_attribute_option ao ON a.id = ao.attribute_id
			LEFT JOIN {$wpdb->prefix}jigoshop_product_attribute pa ON pa.attribute_id = a.id
			WHERE pa.product_id = %d
		", array($productId));
		$results = $wpdb->get_results($query, ARRAY_A);
		$attributes = array();

		for ($i = 0, $endI = count($results); $i < $endI;) {
			$attribute = new Attribute();
			$attribute->setId((int)$results[$i]['id']);
			$attribute->setSlug($results[$i]['slug']);
			$attribute->setLabel($results[$i]['label']);
			$attribute->setType((int)$results[$i]['type']);
			$attribute->setLocal((bool)$results[$i]['is_local']);
			$attribute->setValue($results[$i]['value']);

			while ($i < $endI && $results[$i]['id'] == $attribute->getId()) {
				$option = new Attribute\Option();
				$option->setId($results[$i]['option_id']);
				$option->setLabel($results[$i]['option_label']);
				$option->setValue($results[$i]['option_value']);
				$option->setAttribute($attribute);
				$attribute->addOption($option);
				$i++;
			}

			$attributes[] = $attribute;
		}

		return $attributes;
	}
}
