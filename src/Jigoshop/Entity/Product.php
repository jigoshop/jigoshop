<?php

namespace Jigoshop\Entity;

use Jigoshop\Entity\Product\Attributes\Size;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use WPAL\Wordpress;

/**
 * Product class.
 *
 * @package Jigoshop\Entity
 * @author Amadeusz Starzykiewicz
 */
abstract class Product implements EntityInterface
{
	const VISIBILITY_CATALOG = 1;
	const VISIBILITY_SEARCH = 2;
	const VISIBILITY_PUBLIC = 3; // CATALOG | SEARCH
	const VISIBILITY_NONE = 0;

	private $id;
	private $name;
	private $sku;
	private $taxClasses = array();
	/** @var Size */
	private $size;
	/** @var StockStatus */
	private $stock;

	private $visibility = self::VISIBILITY_PUBLIC;
	private $featured;
	private $attributes;

	protected $dirtyFields = array();
	protected $dirtyAttributes = array(
		'new' => array(),
		'removed' => array(),
	);

	/** @var \WPAL\Wordpress */
	protected $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$this->size = new Size();
		$this->stock = new StockStatus();
	}

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
	 * Sets product visibility.
	 *
	 * Please, use provided constants to set value properly:
	 *   * Product::VISIBILITY_CATALOG - visible only in catalog
	 *   * Product::VISIBILITY_SEARCH - visible only in search
	 *   * Product::VISIBILITY_PUBLIC - visible in search and catalog
	 *   * Product::VISIBILITY_NONE - hidden
	 *
	 * @param int $visibility Product visibility.
	 */
	public function setVisibility($visibility)
	{
		$visibility = intval($visibility);

		if (in_array($visibility, array(self::VISIBILITY_PUBLIC, self::VISIBILITY_SEARCH, self::VISIBILITY_CATALOG, self::VISIBILITY_NONE))) {
			$this->visibility = $visibility;
			$this->dirtyFields[] = 'visibility';
		}
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
	 * Returns whether product is visible to any of sources
	 *
	 * @return boolean Is product visible?
	 */
	public function isVisible()
	{
		return $this->visibility && self::VISIBILITY_PUBLIC != 0;
	}

	/**
	 * @return string Product type.
	 */
	public abstract function getType();

	/**
	 * @param $type string Type name.
	 * @return bool Is product of specified type?
	 */
	public function isType($type)
	{
		return $this->getType() === $type;
	}

	/**
	 * Sets product size.
	 * Applies `jigoshop\product\set_size` filter to allow plugins to modify size data. When filter returns false size is not modified at all.
	 *
	 * @param Size $size New product size.
	 */
	public function setSize(Size $size)
	{
		$size = $this->wp->applyFilters('jigoshop\\product\\set_size', $size, $this);

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
	 * Sets product stock.
	 * Applies `jigoshop\product\set_stock` filter to allow plugins to modify stock data. When filter returns false stock is not modified at all.
	 *
	 * @param StockStatus $stock New product stock status.
	 */
	public function setStock(StockStatus $stock)
	{
		$stock = $this->wp->applyFilters('jigoshop\\product\\set_stock', $stock, $this);

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
	 * Adds new attribute to the product.
	 * If attribute already exists - it is replaced.
	 * Calls `jigoshop\product\add_attribute` filter before adding. If filter returns false - attribute is not added.
	 *
	 * @param \Jigoshop\Entity\Product\Attributes\Attribute $attribute New attribute for product.
	 */
	public function addAttribute(Product\Attributes\Attribute $attribute)
	{
		$key = $this->_findAttribute($attribute->getName());

		if ($key === false) {
			$key = count($this->attributes);
		}

		$attribute = $this->wp->applyFilters('jigoshop\\product\\add_attribute', $attribute, $this);

		if ($attribute !== false) {
			$this->attributes[$key] = $attribute;
			$this->dirtyAttributes['new'][$key] = $attribute;
		}
	}

	/**
	 * Removes attribute from the product.
	 * Calls `jigoshop\product\delete_attribute` filter before removing. If filter returns false - attribute is not removed.
	 *
	 * @param Product\Attributes\Attribute|string $attribute Attribute to remove.
	 */
	public function deleteAttribute($attribute)
	{
		if ($attribute instanceof Product\Attributes\Attribute) {
			$attribute = $attribute->getName();
		}

		$key = $this->_findAttribute($attribute);
		$key = $this->wp->applyFilters('jigoshop\\product\\delete_attribute', $key, $attribute, $this);

		if ($key !== false) {
			unset($this->attributes[$key]);
			$this->dirtyAttributes['removed'][] = $key;
		}
	}

	/**
	 * Returns attribute of the product.
	 * If attribute is not found - returns {@code null}.
	 *
	 * @param $name string Attribute name.
	 * @return Product\Attributes\Attribute|null Attribute found or null.
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
	 * @param array $tax New tax classes of the product.
	 */
	public function setTaxClasses(array $tax)
	{
		$this->taxClasses = $tax;
		$this->dirtyFields[] = 'tax';
	}

	/**
	 * @param string $tax New tax class for the product.
	 */
	public function addTaxClass($tax)
	{
		$this->taxClasses[] = $tax;
		$this->dirtyFields[] = 'tax';
	}

	/**
	 * @return array Tax classes of the product.
	 */
	public function getTaxClasses()
	{
		return $this->taxClasses;
	}

	/**
	 * @param string $attribute Attribute name to find.
	 * @return int Key in attributes array.
	 */
	protected function _findAttribute($attribute)
	{
		return array_search($attribute, array_map(function ($item){
			/** @var $item \Jigoshop\Entity\Product\Attributes\Attribute */
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
				case 'sku':
					$toSave['sku'] = $this->sku;
					break;
				case 'featured':
					$toSave['featured'] = $this->featured;
					break;
				case 'visibility':
					$toSave['visibility'] = $this->visibility;
					break;
				case 'type':
					$toSave['type'] = $this->getType();
					break;
				case 'tax':
					$toSave['tax'] = serialize($this->taxClasses);
					break;
			}
		}

		$toSave['size'] = $this->size;
		$toSave['stock'] = $this->stock;
		$toSave['attributes'] = $this->dirtyAttributes;

		return $toSave;
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		if (isset($state['id'])) {
			$this->id = $state['id'];
		}
		if (isset($state['name'])) {
			$this->name = $state['name'];
		}
		if (isset($state['sku'])) {
			$this->sku = $state['sku'];
		}
		if (isset($state['featured'])) {
			$this->visibility = (bool)$state['featured'];
		}
		if (isset($state['visibility'])) {
			$this->visibility = (int)$state['visibility'];
		}
		if (isset($state['tax'])) {
			$this->taxClasses = unserialize($state['tax']);
		}
		if (isset($state['size']) && !empty($state['size'])) {
			if( is_array($state['size'])) {
				$this->size->setWidth($state['size']['width']);
				$this->size->setHeight($state['size']['height']);
				$this->size->setLength($state['size']['length']);
				$this->size->setWeight($state['size']['weight']);
			} else {
				$this->size = unserialize($state['size']);
			}
		}
		if (isset($state['stock']) && !empty($state['stock'])) {
			if (is_array($state['stock'])) {
				$this->stock->setManage($state['stock']['manage'] == 'on');
				$this->stock->setAllowBackorders($state['stock']['allow_backorders'] == 'on');
				if ($this->stock->getManage()) {
					$this->stock->setStock($state['stock']['stock']);
				} else {
					$this->stock->setStatus($state['stock']['status']);
				}
			} else {
				$this->stock = unserialize($state['stock']);
			}
		}

		if (isset($state['attributes'])) {
			$this->attributes = $state['attributes'];
		}
	}

	/**
	 * Marks values provided in the state as dirty.
	 *
	 * @param array $state Product state.
	 */
	public function markAsDirty(array $state)
	{
		if (isset($state['attributes'])) {
			$this->dirtyAttributes = array(
				'new' => array_keys($state['attributes']),
			);
			unset($state['attributes']);
		}

		$this->dirtyFields[] = 'size';
		$this->dirtyFields[] = 'stock';
		$this->dirtyFields = array_merge($this->dirtyFields, array_keys($state));
	}
}
