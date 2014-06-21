<?php

namespace Jigoshop\Entity;

use Jigoshop\Entity\Product\Sales;
use Jigoshop\Entity\Product\Size;
use Jigoshop\Entity\Product\StockStatus;

/**
 * Product class.
 *
 * @package Jigoshop\Entity
 * @author Amadeusz Starzykiewicz
 */
class Product implements EntityInterface
{
	const TYPE = 'simple';

	const VISIBILITY_CATALOG = 1;
	const VISIBILITY_SEARCH = 2;
	const VISIBILITY_PUBLIC = 3; // CATALOG | SEARCH

	private $id;
	private $name;
	private $sku;
	private $price = 0.0;
	private $regularPrice = 0.0;
	/** @var Sales */
	private $sales;
	/** @var Size */
	private $size;
	private $tax;
	private $visibility = self::VISIBILITY_PUBLIC;
	private $featured;
	/** @var StockStatus */
	private $stock;
	private $attributes;

	private $dirtyFields = array();

	/**
	 * @param int $id New ID for the product.
	 */
	public function setId($id)
	{
		$this->id = $id;
		$this->dirtyFields[] = 'id';
	}

	/**
	 * @return int Product ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $name New product name.
	 */
	public function setName($name)
	{
		$this->name = $name;
		$this->dirtyFields[] = 'name';
	}

	/**
	 * @return string Product name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Adds new attribute to the product.
	 * If attribute already exists - it is replaced.
	 * Calls `jigoshop\product\add_attribute` filter before adding. If filter returns false - attribute is not added.
	 *
	 * @param \Jigoshop\Entity\Product\Attribute $attribute New attribute for product.
	 */
	public function addAttribute(Product\Attribute $attribute)
	{
		$key = $this->_findAttribute($attribute->getName());

		if ($key === false) {
			$key = count($this->attributes);
		}

		$attribute = apply_filters('jigoshop\\product\\add_attribute', $attribute, $this);

		if ($attribute !== false) {
			$this->attributes[$key] = $attribute;
			$this->dirtyFields[] = 'attributes';
		}
	}

	/**
	 * Removes attribute from the product.
	 * Calls `jigoshop\product\delete_attribute` filter before removing. If filter returns false - attribute is not removed.
	 *
	 * @param Product\Attribute|string $attribute Attribute to remove.
	 */
	public function deleteAttribute($attribute)
	{
		if ($attribute instanceof Product\Attribute) {
			$attribute = $attribute->getName();
		}

		$key = $this->_findAttribute($attribute);
		$key = apply_filters('jigoshop\\product\\delete_attribute', $key, $attribute, $this);

		if ($key !== false) {
			unset($this->attributes[$key]);
			$this->dirtyFields[] = 'attributes';
		}
	}

	/**
	 * Returns attribute of the product.
	 * If attribute is not found - returns {@code null}.
	 *
	 * @param $name string Attribute name.
	 * @return Product\Attribute|null Attribute found or null.
	 */
	public function getAttribute($name)
	{
		$key = $this->_findAttribute($name);

		if ($key !== false) {
			return $this->attributes[$key];
		}

		return null;
	}

	/**
	 * @return array List of product attributes.
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * @param boolean $featured Whether product is featured.
	 */
	public function setFeatured($featured)
	{
		$this->featured = $featured;
		$this->dirtyFields[] = 'featured';
	}

	/**
	 * @return boolean Is product featured?
	 */
	public function isFeatured()
	{
		return $this->featured;
	}

	/**
	 * Sets new product price.
	 * Applies `jigoshop\product\set_price` filter to allow plugins to modify the price. When filter returns false price is not modified at all.
	 *
	 * @param float $price New product price.
	 */
	public function setPrice($price)
	{
		$price = apply_filters('jigoshop\\product\\set_price', $price, $this);

		if ($price !== false) {
			$this->price = $price;
			$this->dirtyFields[] = 'price';
		}
	}

	/**
	 * Returns real product price.
	 * Applies `jigoshop\product\get_price` filter to allow plugins to modify the price.
	 *
	 * @return float Current product price.
	 */
	public function getPrice()
	{
		return apply_filters('jigoshop\\product\\get_price', $this->price, $this);
	}

	/**
	 * @param float $regularPrice New regular product price.
	 */
	public function setRegularPrice($regularPrice)
	{
		$this->regularPrice = $regularPrice;
		$this->dirtyFields[] = 'regularPrice';
	}

	/**
	 * @return float Regular product price.
	 */
	public function getRegularPrice()
	{
		return $this->regularPrice;
	}

	/**
	 * Sets product sales.
	 * Applies `jigoshop\product\set_sales` filter to allow plugins to modify sales data. When filter returns false sales are not modified at all.
	 *
	 * @param Sales $sales Product sales data.
	 */
	public function setSales(Sales $sales)
	{
		$sales = apply_filters('jigoshop\\product\\set_sales', $sales, $this);

		if ($sales !== false) {
			$this->sales = $sales;
			$this->dirtyFields[] = 'sales';
		}
	}

	/**
	 * @return Sales Current product sales data.
	 */
	public function getSales()
	{
		return $this->sales;
	}

	/**
	 * Sets product size.
	 * Applies `jigoshop\product\set_size` filter to allow plugins to modify size data. When filter returns false size is not modified at all.
	 *
	 * @param Size $size New product size.
	 */
	public function setSize(Size $size)
	{
		$size = apply_filters('jigoshop\\product\\set_size', $size, $this);

		if ($size !== false) {
			$this->size = $size;
			$this->dirtyFields[] = 'size';
		}
	}

	/**
	 * @return Size Product size.
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param string $sku New SKU (Stock-Keeping Unit).
	 */
	public function setSku($sku)
	{
		$this->sku = $sku;
		$this->dirtyFields[] = 'sku';
	}

	/**
	 * @return string Product's SKU (Stock-Keeping Unit).
	 */
	public function getSku()
	{
		return $this->sku;
	}

	/**
	 * Sets product stock.
	 * Applies `jigoshop\product\set_stock` filter to allow plugins to modify stock data. When filter returns false stock is not modified at all.
	 *
	 * @param StockStatus $stock New product stock status.
	 */
	public function setStock(StockStatus $stock)
	{
		$stock = apply_filters('jigoshop\\product\\set_stock', $stock, $this);

		if ($stock !== false) {
			$this->stock = $stock;
			$this->dirtyFields[] = 'stock';
		}
	}

	/**
	 * @return StockStatus Current stock status.
	 */
	public function getStock()
	{
		return $this->stock;
	}

	/**
	 * TODO: Implement taxing. Probably it is worth to use the same filters as in other setters.
	 *
	 * @param mixed $tax
	 */
	public function setTax($tax)
	{
		$this->tax = $tax;
		$this->dirtyFields[] = 'tax';
	}

	/**
	 * TODO: Implement taxing.
	 *
	 * @return mixed
	 */
	public function getTax()
	{
		return $this->tax;
	}

	/**
	 * @return string Product type.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * @param $type string Type name.
	 * @return bool Is product of specified type?
	 */
	public function isType($type)
	{
		return $this->getType() == $type;
	}

	/**
	 * Sets product visibility.
	 * Please, use provided constants to set value properly:
	 *   * Product::VISIBILITY_CATALOG - visible only in catalog
	 *   * Product::VISIBILITY_SEARCH - visible only in search
	 *   * Product::VISIBILITY_PUBLIC - visible in search and catalog
	 *
	 * @param int $visibility Product visibility.
	 */
	public function setVisibility($visibility)
	{
		$this->visibility = $visibility;
		$this->dirtyFields[] = 'visibility';
	}

	/**
	 * Returns bitwise value of product visibility.
	 * Do determine if product is visible in specified type simply check it with "&" bit operator.
	 *
	 * @return int Current product visibility.
	 */
	public function getVisibility()
	{
		return $this->visibility;
	}

	/**
	 * @param string $attribute Attribute name to find.
	 * @return int Key in attributes array.
	 */
	private function _findAttribute($attribute)
	{
		return array_search($attribute, array_map(function ($item){
			/** @var $item \Jigoshop\Entity\Product\Attribute */
			return $item->getName();
		}, $this->attributes));
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		$toSave = array();

		foreach ($this->dirtyFields as $field) {
			switch ($field) {
				case 'sales':
					$toSave['sales_from'] = $this->sales->getFrom()->getTimestamp();
					$toSave['sales_to'] = $this->sales->getTo()->getTimestamp();
					$toSave['sales_price'] = $this->sales->getPrice();
					break;
				case 'size':
					$toSave['size_weight'] = $this->size->getWeight();
					$toSave['size_width'] = $this->size->getWidth();
					$toSave['size_height'] = $this->size->getHeight();
					$toSave['size_length'] = $this->size->getLength();
					break;
				case 'stock':
					$toSave['stock_manage'] = $this->stock->getManage();
					$toSave['stock_allowed_backorders'] = $this->stock->getAllowBackorders();
					$toSave['stock_status'] = $this->stock->getStatus();
					$toSave['stock_stock'] = $this->stock->getStock();
					break;
				// TODO: Save tax properly
				default:
					$toSave[$field] = $this->$field;
			}
		}

		// TODO: Save attributes?

		return $toSave;
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		if (isset($state['price'])) {
			$this->price = floatval($state['price']);
		}
		if (isset($state['regular_price'])) {
			$this->regularPrice = floatval($state['regular_price']);
		}
		if (isset($state['visibility'])) {
			$this->visibility = intval($state['visibility']);
		}

		$this->sales = new Sales();
		if (isset($state['sales_from'])) {
			$this->sales->setFrom(new \DateTime($state['sales_from']));
		}
		if (isset($state['sales_to'])) {
			$this->sales->setTo(new \DateTime($state['sales_to']));
		}
		if (isset($state['sales_price'])) {
			$this->sales->setPrice(floatval($state['sales_price']));
		}

		$this->size = new Size();
		if (isset($state['size_weight'])) {
			$this->size->setWeight(floatval($state['size_weight']));
		}
		if (isset($state['size_width'])) {
			$this->size->setWidth(floatval($state['size_width']));
		}
		if (isset($state['size_height'])) {
			$this->size->setHeight(floatval($state['size_height']));
		}
		if (isset($state['size_length'])) {
			$this->size->setLength(floatval($state['size_length']));
		}

		$this->stock = new StockStatus();
		if (isset($state['stock_manage'])) {
			$this->stock->setManage(boolval($state['stock_manage']));
		}
		if (isset($state['stock_allowed_backorders'])) {
			$this->stock->setAllowBackorders(boolval($state['stock_allowed_backorders']));
		}
		if (isset($state['stock_status'])) {
			$this->stock->setStatus(intval($state['stock_status']));
		}
		if (isset($state['stock_stock'])) {
			$this->stock->setStock(intval($state['stock_stock']));
		}

		// TODO: Restore tax (after thinking it over and implementing).
		// TODO: Restore attributes?
	}
}