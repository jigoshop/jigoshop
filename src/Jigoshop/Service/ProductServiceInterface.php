<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute;

/**
 * Products service interface.
 *
 * @package Jigoshop\Service
 * @author Amadeusz Starzykiewicz
 */
interface ProductServiceInterface extends ServiceInterface
{
	/**
	 * Adds new type to managed types.
	 *
	 * @param $type string Unique type name.
	 * @param $class string Class name.
	 * @throws \Jigoshop\Exception When type already exists.
	 */
	public function addType($type, $class);

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return Product
	 */
	public function find($id);

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return Product Item found.
	 */
	public function findForPost($post);

	/**
	 * Finds item specified by state.
	 *
	 * @param array $state State of the product to be found.
	 * @return Product|Product\Purchasable Item found.
	 */
	public function findForState(array $state);

	/**
	 * Finds items by trying to match their name.
	 *
	 * @param $name string Post name to match.
	 * @return array List of matched products.
	 */
	public function findLike($name);

	/**
	 * @param $number int Number of products to find.
	 * @return array List of products that are out of stock.
	 */
	public function findOutOfStock($number);

	/**
	 * @param $threshold int Threshold where to assume product is low in stock.
	 * @param $number int Number of products to find.
	 * @return array List of products that are low in stock.
	 */
	public function findLowStock($threshold, $number);

	/**
	 * @param Product $product Product to find thumbnails for.
	 * @return array List of thumbnails attached to the product.
	 */
	public function getThumbnails(Product $product);

	/**
	 * Finds and returns list of available attributes.
	 *
	 * @return array List of available product attributes
	 */
	public function findAllAttributes();

	/**
	 * Finds and returns number of available attributes.
	 *
	 * @return int Number of available product attributes
	 */
	public function countAttributes();

	/**
	 * Finds and returns list of attributes associated with selected product by it's ID.
	 *
	 * @param $productId int Product ID.
	 * @return array List of attributes attached to selected product.
	 */
	public function getAttributes($productId);

	/**
	 * Finds attribute for selected ID.
	 *
	 * If attribute is not found - returns null.
	 *
	 * @param int $id Attribute ID.
	 * @return Attribute
	 */
	public function getAttribute($id);

	/**
	 * Creates new attribute for selected type.
	 *
	 * @param int $type Attribute type.
	 * @return Attribute
	 */
	public function createAttribute($type);

	/**
	 * Saves attribute to database.
	 *
	 * @param Attribute $attribute Attribute to save.
	 * @return \Jigoshop\Entity\Product\Attribute Saved attribute.
	 */
	public function saveAttribute(Attribute $attribute);

	/**
	 * Removes attribute from database.
	 *
	 * @param int $id Attribute ID.
	 */
	public function removeAttribute($id);

	/**
	 * Returns unique key for product in the cart.
	 *
	 * @param $item Item Item to get key for.
	 * @return string
	 */
	public function generateItemKey(Item $item);
}
