<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attributes\Sales;
use WPAL\Wordpress;

class Variable extends Product implements Shippable, Saleable
{
	const TYPE = 'variable';

	private $variations = array();
	/** @var Sales */
	private $sales;

	public function __construct(Wordpress $wp)
	{
		parent::__construct($wp);
		$this->sales = new Sales();
	}

	/**
	 * @return string Product type.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * @return Sales Current product sales data.
	 */
	public function getSales()
	{
		return $this->sales;
	}

	/**
	 * Sets product sales.
	 * Applies `jigoshop\product\set_sales` filter to allow plugins to modify sales data. When filter returns false sales are not modified at all.
	 *
	 * @param Sales $sales Product sales data.
	 */
	public function setSales(Sales $sales)
	{
		$sales = $this->wp->applyFilters('jigoshop\product\set_sales', $sales, $this);

		if ($sales !== false) {
			$this->sales = $sales;
			$this->dirtyFields[] = 'sales';
		}
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		$toSave = parent::getStateToSave();

		foreach ($this->dirtyFields as $field) {
			switch ($field) {
//				case 'regular_price':
//					$toSave['regular_price'] = $this->regularPrice;
//					break;
			}
		}

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
}