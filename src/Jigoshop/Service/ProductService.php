<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Entity\Product\Purchasable;
use Jigoshop\Exception;
use Jigoshop\Factory\Product as ProductFactory;
use WPAL\Wordpress;

/**
 * Product service.
 *
 * @package Jigoshop\Service
 * @author Amadeusz Starzykiewicz
 */
class ProductService implements ProductServiceInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Factory\Product */
	private $factory;

	public function __construct(Wordpress $wp, ProductFactory $factory)
	{
		$this->wp = $wp;
		$this->factory = $factory;
		$wp->addAction('save_post_'.Types\Product::NAME, array($this, 'savePost'), 10);
		$wp->addAction('jigoshop\product\sold', array($this, 'addSoldQuantity'), 10, 2);
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
	 * @param $product \Jigoshop\Entity\Product|Purchasable The product.
	 * @param $quantity int Quantity to add.
	 */
	public function addSoldQuantity($product, $quantity)
	{
		$product->getStock()->addSoldQuantity($quantity);
		$this->save($product);
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

		return $this->wp->applyFilters('jigoshop\service\product\find', $this->factory->fetch($post), $id);
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Product Item found.
	 */
	public function findForPost($post)
	{
		return $this->wp->applyFilters('jigoshop\service\product\find_for_post', $this->factory->fetch($post), $post);
	}

	/**
	 * Finds item specified by state.
	 *
	 * @param array $state State of the product to be found.
	 * @return \Jigoshop\Entity\Product Item found.
	 */
	public function findForState(array $state)
	{
		$post = $this->wp->getPost($state['id']);
		$product = $this->factory->fetch($post);
		$product->restoreState($state);
		return $this->wp->applyFilters('jigoshop\service\product\find_for_state', $product, $state);
	}

	/**
	 * Finds items by trying to match their name.
	 *
	 * @param $name string Post name to match.
	 * @return array List of matched products.
	 */
	public function findLike($name)
	{
		$query = new \WP_Query(array(
			'post_type' => Types::PRODUCT,
			's' => $name,
		));

		return $this->wp->applyFilters('jigoshop\service\product\find_like', $this->findByQuery($query), $name);
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery($query)
	{
		$results = $query->get_posts();
		$products = array();

		// TODO: Maybe it is good to optimize this to fetch all found products at once?
		foreach ($results as $product) {
			$products[$product->ID] = $this->findForPost($product);
		}

		return $this->wp->applyFilters('jigoshop\service\product\find_by_query', $products, $query);
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

		// TODO: Support for transactions!

		$fields = $object->getStateToSave();

		if (isset($fields['id']) || isset($fields['name']) || isset($fields['description'])) {
			// We do not need to save ID, name and description (excerpt) as they are saved by WordPress itself.
			unset($fields['id'], $fields['name'], $fields['description']);
		}

		if (isset($fields['attributes'])) {
			$this->_removeAllProductAttributesExcept($object->getId(), array_map(function($item){
				/** @var $item Attribute */
				return $item->getId();
			}, $fields['attributes']));

			foreach ($fields['attributes'] as $attribute) {
				$this->_saveProductAttribute($object, $attribute);
			}

			unset($fields['attributes']);
		}

		foreach ($fields as $field => $value) {
			$this->wp->updatePostMeta($object->getId(), $field, $value);
		}

		$this->wp->doAction('jigoshop\service\product\save', $object);
	}

	/**
	 * @param $productId int Product ID.
	 * @param $ids array List of existing attribute IDs.
	 */
	private function _removeAllProductAttributesExcept($productId, $ids)
	{
		$wpdb = $this->wp->getWPDB();
		$ids = join(',', array_filter(array_map(function($item){ return (int)$item; }, $ids)));
		// Support for removing all items
		if (empty($ids)) {
			$ids = '0';
		}
		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}jigoshop_product_attribute WHERE attribute_id NOT IN ({$ids}) AND product_id = %d", array($productId));
		$wpdb->query($query);
	}

	/**
	 * @param $object \Jigoshop\Entity\Product
	 * @param $attribute Attribute
	 */
	private function _saveProductAttribute($object, $attribute)
	{
		$wpdb = $this->wp->getWPDB();

		$value = $attribute->getValue();
		if (is_array($value)) {
			$value = join('|', $value);
		}

		$data = array(
			'product_id' => $object->getId(),
			'attribute_id' => $attribute->getId(),
			'value' => $value,
		);

		if ($attribute->exists()) {
			$wpdb->update($wpdb->prefix.'jigoshop_product_attribute', $data, array(
				'product_id' => $object->getId(),
				'attribute_id' => $attribute->getId(),
			));
		} else {
			$wpdb->insert($wpdb->prefix.'jigoshop_product_attribute', $data);
			$attribute->setExists(Attribute::PRODUCT_ATTRIBUTE_EXISTS);
		}

		foreach ($attribute->getFieldsToSave() as $field) {
			/** @var $field Attribute\Field */
			$data = array(
				'product_id' => $object->getId(),
				'attribute_id' => $attribute->getId(),
				'meta_key' => $field->getKey(),
				'meta_value' => esc_sql($field->getValue()),
			);
			if ($field->getId()) {
				$wpdb->update($wpdb->prefix.'jigoshop_product_attribute_meta', $data, array(
					'id' => $field->getId(),
				));
			} else {
				$wpdb->insert($wpdb->prefix.'jigoshop_product_attribute_meta', $data);
				$field->setId($wpdb->insert_id);
			}
		}
	}

	/**
	 * @param $number int Number of products to find.
	 * @return array List of products that are out of stock.
	 */
	public function findOutOfStock($number)
	{
		$query = new \WP_Query(array(
			'post_type' => Types::PRODUCT,
			'post_status' => 'publish',
			'posts_per_page' => $number,
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
	 * @param $number int Number of products to find.
	 * @return array List of products that are low in stock.
	 */
	public function findLowStock($threshold, $number)
	{
		$query = new \WP_Query(array(
			'post_type' => Types::PRODUCT,
			'post_status' => 'publish',
			'posts_per_page' => $number,
			'meta_query' => array(
				array(
					'key' => 'stock_manage',
					'value' => 1,
					'compare' => '=',
				),
				array(
					'key' => 'stock_stock',
					'value' => $threshold,
					'compare' => '<=',
				),
			),
		));

		return $this->findByQuery($query);
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

	/**
	 * @param \Jigoshop\Entity\Product $product Product to find thumbnails for.
	 * @param string $size Size for images.
	 * @return array List of thumbnails attached to the product.
	 */
	public function getThumbnails(\Jigoshop\Entity\Product $product, $size = Options::IMAGE_THUMBNAIL)
	{
		$query = new \WP_Query();
		$args = array(
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'orderby' => 'menu_order',
			'order' => 'asc',
			'numberposts' => -1,
			'post_status' => 'inherit',
			'post_parent' => $product->getId(),
			'suppress_filters' => true,
			'post__not_in' => array($this->wp->getPostThumbnailId($product->getId())),
		);

		$thumbnails = array();
		foreach ($query->query($args) as $thumbnail) {
			$thumbnails[$thumbnail->ID] = array(
				'title' => $thumbnail->post_title,
				'url' => $this->wp->wpGetAttachmentUrl($thumbnail->ID),
				'image' => $this->wp->wpGetAttachmentImage($thumbnail->ID, $size),
			);
		}

		return $thumbnails;
	}

	/**
	 * Finds and returns list of available attributes.
	 *
	 * @return array List of available product attributes
	 */
	public function findAllAttributes()
	{
		$wpdb = $this->wp->getWPDB();
		$query = "
		SELECT a.id, a.is_local, a.slug, a.label, a.type,
			ao.id AS option_id, ao.value AS option_value, ao.label as option_label
		FROM {$wpdb->prefix}jigoshop_attribute a
			LEFT JOIN {$wpdb->prefix}jigoshop_attribute_option ao ON a.id = ao.attribute_id
			WHERE a.is_local = 0
		";
		$results = $wpdb->get_results($query, ARRAY_A);
		$attributes = array();

		for ($i = 0, $endI = count($results); $i < $endI;) {
			$attribute = $this->factory->createAttribute($results[$i]['type']);
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

			$attributes[$attribute->getId()] = $attribute;
		}

		return $attributes;
	}

	/**
	 * Finds and returns number of available attributes.
	 *
	 * @return int Number of available product attributes
	 */
	public function countAttributes()
	{
		$wpdb = $this->wp->getWPDB();
		$query = "
		SELECT COUNT(*) FROM {$wpdb->prefix}jigoshop_attribute a
			WHERE a.is_local = 0
		";
		return $wpdb->get_var($query);
	}

	/**
	 * Finds and returns list of attributes associated with selected product by it's ID.
	 *
	 * @param $productId int Product ID.
	 * @return array List of attributes attached to selected product.
	 */
	public function getAttributes($productId)
	{
		return $this->factory->getAttributes($productId);
	}

	/**
	 * Finds attribute for selected ID.
	 *
	 * If attribute is not found - returns null.
	 *
	 * @param int $id Attribute ID.
	 * @return Attribute
	 */
	public function getAttribute($id)
	{
		return $this->factory->getAttribute($id);
	}

	/**
	 * Creates new attribute for selected type.
	 *
	 * @param int $type Attribute type.
	 * @return Attribute
	 */
	public function createAttribute($type)
	{
		return $this->factory->createAttribute($type);
	}

	/**
	 * Saves attribute to database.
	 *
	 * @param Attribute $attribute Attribute to save.
	 * @return \Jigoshop\Entity\Product\Attribute Saved attribute.
	 */
	public function saveAttribute(Attribute $attribute)
	{
		$wpdb = $this->wp->getWPDB();
		$data = array(
			'label' => $attribute->getLabel(),
			'slug' => $attribute->getSlug(),
			'type' => $attribute->getType(),
			'is_local' => $attribute->isLocal(),
		);

		if ($attribute->getId()) {
			$wpdb->update($wpdb->prefix.'jigoshop_attribute', $data, array('id' => $attribute->getId()));
		} else {
			$wpdb->insert($wpdb->prefix.'jigoshop_attribute', $data);
			$attribute->setId($wpdb->insert_id);
		}

		$this->wp->doAction('jigoshop\attribute\save', $attribute);

		$this->removeAllAttributesExcept($attribute->getId(), array_map(function($item){
			/** @var $item Attribute\Option */
			return $item->getId();
		}, $attribute->getOptions()));

		foreach ($attribute->getOptions() as $option) {
			/** @var $option Attribute\Option */
			$data = array(
				'attribute_id' => $option->getAttribute()->getId(),
				'label' => $option->getLabel(),
				'value' => $option->getValue(),
			);
			if ($option->getId()) {
				$wpdb->update($wpdb->prefix.'jigoshop_attribute_option', $data, array('id' => $option->getId()));
			} else {
				$wpdb->insert($wpdb->prefix.'jigoshop_attribute_option', $data);
				$option->setId($wpdb->insert_id);
			}
		}

		return $attribute;
	}

	/**
	 * @param $attributeId int ID of parent attribute.
	 * @param $ids array IDs to preserve.
	 */
	private function removeAllAttributesExcept($attributeId, $ids)
	{
		$wpdb = $this->wp->getWPDB();
		$ids = join(',', array_filter(array_map(function($item){ return (int)$item; }, $ids)));
		// Support for removing all items
		if (empty($ids)) {
			$ids = '0';
		}
		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}jigoshop_attribute_option WHERE id NOT IN ({$ids}) AND attribute_id = %d", array($attributeId));
		$wpdb->query($query);
	}

	/**
	 * Removes attribute from database.
	 *
	 * @param int $id Attribute ID.
	 */
	public function removeAttribute($id)
	{
		$wpdb = $this->wp->getWPDB();
		$wpdb->delete($wpdb->prefix.'jigoshop_attribute', array('id' => $id));
	}

	/**
	 * Returns unique key for product in the cart.
	 *
	 * @param $item Item Item to get key for.
	 * @return string
	 */
	public function generateItemKey(Item $item)
	{
		$parts = array(
			$item->getProduct()->getId(),
		);

		$parts = $this->wp->applyFilters('jigoshop\cart\generate_item_key', $parts, $item);

		return hash('md5', join('_', $parts));
	}
}
