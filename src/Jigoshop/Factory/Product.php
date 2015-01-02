<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Entity\Product\Purchasable;
use Jigoshop\Entity\Product\Simple;
use Jigoshop\Exception;
use Monolog\Registry;
use WPAL\Wordpress;

class Product implements EntityFactoryInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	private $types = array();

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
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
			if (WP_DEBUG) {
				throw new Exception(sprintf(__('Product of type "%s" already exists.', 'jigoshop'), $type));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addWarning(sprintf('Product of type "%s" already exists.', $type));
			return;
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
			if (WP_DEBUG) {
				throw new Exception(sprintf('Product type "%s" does not exists.', $type));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addWarning(sprintf('Product type "%s" does not exists.', $type));
			$type = Simple::TYPE;
		}

		$class = $this->types[$type];
		/** @var \Jigoshop\Entity\Product $instance */
		$instance = new $class($this->wp);

		if ($instance instanceof Purchasable) {
			/** @var \Jigoshop\Entity\Product\Purchasable $instance */
			$instance->getStock()->setManage($this->options->get('products.manage_stock'));
		}

		$instance->setTaxable($this->options->get('tax.defaults.taxable'));
		$instance->setTaxClasses($this->options->get('tax.defaults.classes'));

		return $instance;
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
			$helpers = $this->wp->getHelpers();
			$product->setName($helpers->sanitizeTitle($_POST['post_title']));
			$product->setDescription($helpers->parsePostBody($_POST['post_excerpt']));
			$_POST['product']['categories'] = $this->getTerms($id, Types::PRODUCT_CATEGORY, $this->wp->getTerms(Types::PRODUCT_CATEGORY, array(
				'posts__in' => $_POST['tax_input']['product_category'],
			)));
			$_POST['product']['tags'] = $this->getTerms($id, Types::PRODUCT_TAG, $this->wp->getTerms(Types::PRODUCT_TAG, array(
				'posts__in' => $_POST['tax_input']['product_tag'],
			)));

			if (!isset($_POST['product']['tax_classes'])) {
				$_POST['product']['tax_classes'] = array();
			}

			unset($_POST['product']['attributes']);
			$_POST['product']['stock_manage'] = $_POST['product']['stock_manage'] == 'on';
			$_POST['product']['sales_enabled'] = $_POST['product']['sales_enabled'] == 'on';

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
			$state['description'] = $this->wp->getHelpers()->parsePostBody($post->post_content);
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
		SELECT a.id, a.is_local, a.slug, a.label, a.type,
			ao.id AS option_id, ao.value AS option_value, ao.label as option_label
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
		$attribute = $this->createAttribute($results[$i]['type']);
		$attribute->setId((int)$results[$i]['id']);
		$attribute->setSlug($results[$i]['slug']);
		$attribute->setLabel($results[$i]['label']);
		$attribute->setLocal((bool)$results[$i]['is_local']);

		while ($i < $endI && $results[$i]['id'] == $attribute->getId()) {
			if ($results[$i]['option_id'] !== null) {
				$option = new Attribute\Option();
				$option->setId($results[$i]['option_id']);
				$option->setLabel($results[$i]['option_label']);
				$option->setValue($results[$i]['option_value']);
				$attribute->addOption($option);
			}

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
		SELECT a.id, a.is_local, a.slug, a.label, a.type, pa.value,
			ao.id AS option_id, ao.value AS option_value, ao.label as option_label,
			pam.id AS meta_id, pam.meta_key, pam.meta_value
		FROM {$wpdb->prefix}jigoshop_attribute a
			LEFT JOIN {$wpdb->prefix}jigoshop_attribute_option ao ON a.id = ao.attribute_id
			LEFT JOIN {$wpdb->prefix}jigoshop_product_attribute pa ON pa.attribute_id = a.id
			LEFT JOIN {$wpdb->prefix}jigoshop_product_attribute_meta pam ON pa.attribute_id = pam.attribute_id AND pa.product_id = pam.product_id
			WHERE pa.product_id = %d
		", array($productId));
		$results = $wpdb->get_results($query, ARRAY_A);
		$attributes = array();

		for ($i = 0, $endI = count($results); $i < $endI;) {
			$attribute = $this->createAttribute($results[$i]['type'], Attribute::PRODUCT_ATTRIBUTE_EXISTS);
			$attribute->setId((int)$results[$i]['id']);
			$attribute->setSlug($results[$i]['slug']);
			$attribute->setLabel($results[$i]['label']);
			$attribute->setLocal((bool)$results[$i]['is_local']);
			$attribute->setValue($results[$i]['value']);
			$fields = array();

			while ($i < $endI && $results[$i]['id'] == $attribute->getId()) {
				$option = new Attribute\Option();
				if ($results[$i]['option_id'] !== null) {
					$option->setId($results[$i]['option_id']);
					$option->setLabel($results[$i]['option_label']);
					$option->setValue($results[$i]['option_value']);
					$attribute->addOption($option);
				}

				while ($i < $endI && $results[$i]['id'] == $attribute->getId() && $results[$i]['option_id'] == $option->getId()) {
					if ($results[$i]['meta_id'] !== null && !isset($fields[$results[$i]['meta_key']])) {
						$field = new Attribute\Field();
						$field->setId($results[$i]['meta_id']);
						$field->setKey($results[$i]['meta_key']);
						$field->setValue($results[$i]['meta_value']);
						$field->setAttribute($attribute);
						$fields[$results[$i]['meta_key']] = $field;
					}

					$i++;
				}
			}

			$attribute->restoreFields($fields);
			$attributes[$attribute->getId()] = $attribute;
		}

		return $attributes;
	}

	/**
	 * Creates new attribute object based on type.
	 *
	 * @param $type int Attribute type.
	 * @param bool $exists Is attribute loaded from DB.
	 * @return Attribute\Multiselect|Attribute\Select|Attribute\Text
	 */
	public function createAttribute($type, $exists = false)
	{
		switch ($type) {
			case Attribute\Multiselect::TYPE:
				return new Attribute\Multiselect($exists);
			case Attribute\Select::TYPE:
				return new Attribute\Select($exists);
			case Attribute\Text::TYPE:
				return new Attribute\Text($exists);
			default:
				return $this->wp->applyFilters('jigoshop\factory\product\create_attribute', null, $type, $exists);
		}
	}
}
