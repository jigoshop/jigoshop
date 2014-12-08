<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute;
use WPAL\Wordpress;

class Variable extends Product implements Shippable, Saleable
{
	const TYPE = 'variable';

	private $variations = array();
	/** @var Attributes\Sales */
	private $sales;

	public function __construct(Wordpress $wp)
	{
		parent::__construct($wp);
		$this->sales = new Attributes\Sales();
	}

	/**
	 * Checks whether the product requires shipping.
	 *
	 * @return bool Whether the product requires shipping.
	 */
	public function isShippable()
	{
		return array_reduce($this->variations, function($value, $item){
			/** @var $item Shippable */
			return $value & $item->isShippable();
		}, true);
	}

	/**
	 * @param Variable\Variation $variation Variation to add.
	 */
	public function addVariation(Product\Variable\Variation $variation)
	{
		$this->variations[$variation->getId()] = $variation;
	}

	/**
	 * Returns variation instance for selected ID.
	 * If ID is not found - returns null.
	 *
	 * @param $id int Variation ID.
	 * @return Product\Variable\Variation Variation found.
	 */
	public function removeVariation($id)
	{
		if (!isset($this->variations[$id])) {
			return null;
		}

		$variation = $this->variations[$id];
		unset($this->variations[$id]);

		return $variation;
	}

	/**
	 * @param int $id Variation ID.
	 * @return bool Variation exists?
	 */
	public function hasVariation($id)
	{
		return isset($this->variations[$id]);
	}

	/**
	 * @param $id int Variation ID.
	 *
	 * @return Product\Variable\Variation
	 */
	public function getVariation($id)
	{
		if (!isset($this->variations[$id])) {
			return null;
		}

		return $this->variations[$id];
	}

	/**
	 * @return array List of all assigned variations.
	 */
	public function getVariations()
	{
		return $this->variations;
	}

	/**
	 * @return float Minimum price of all variations.
	 */
	public function getLowestPrice()
	{
		$prices = array_map(function($item){
			/** @var $item Product\Variable\Variation */
			return $item->getProduct()->getPrice();
		}, $this->variations);
		return !empty($prices) ? min($prices) : '';
	}

	/**
	 * @return float Maximum price of all variations.
	 */
	public function getHighestPrice()
	{
		$prices = array_map(function($item){
			/** @var $item Product\Variable\Variation */
			return $item->getProduct()->getPrice();
		}, $this->variations);
		return !empty($prices) ? max($prices) : '';
	}

	/**
	 * @return string Product type.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * @return Attributes\Sales Current product sales data.
	 */
	public function getSales()
	{
		return $this->sales;
	}

	/**
	 * Sets product sales.
	 * Applies `jigoshop\product\set_sales` filter to allow plugins to modify sales data. When filter returns false sales are not modified at all.
	 *
	 * @param Attributes\Sales $sales Product sales data.
	 */
	public function setSales(Attributes\Sales $sales)
	{
		$sales = $this->wp->applyFilters('jigoshop\product\set_sales', $sales, $this);

		if ($sales !== false) {
			$this->sales = $sales;
			$this->dirtyFields[] = 'sales';
		}
	}

	/**
	 * @return array List of variable attributes.
	 */
	public function getVariableAttributes()
	{
		return array_filter($this->getAttributes(), function($item){
			/** @var $item Product\Attribute\Variable */
			return $item instanceof Product\Attribute\Variable && $item->isVariable();
		});
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		$toSave = parent::getStateToSave();
		$toSave['sales'] = $this->sales;

		return $toSave;
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		parent::restoreState($state);

		if (isset($state['sales']) && !empty($state['sales'])) {
			if (is_array($state['sales'])) {
				$this->sales->setEnabled($state['sales']['enabled'] == 'on');
				$this->sales->setFromTime($state['sales']['from']);
				$this->sales->setToTime($state['sales']['to']);
				$this->sales->setPrice($state['sales']['price']);
			} else {
				$this->sales = unserialize($state['sales']);
			}
		}
	}

	/**
	 * Marks values provided in the state as dirty.
	 *
	 * @param array $state Product state.
	 */
	public function markAsDirty(array $state)
	{
		$this->dirtyFields[] = 'sales';

		parent::markAsDirty($state);
	}

	/**
	 * @return array Minimal state to identify the product.
	 */
	public function getState()
	{
		return array(
			'type' => $this->getType(),
			'id' => $this->getId(),
		);
	}
}
