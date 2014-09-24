<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attributes\Sales;
use WPAL\Wordpress;

class Simple extends Product
{
	const TYPE = 'simple';

	private $price = 0.0;
	private $regularPrice = 0.0;
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
	 * Returns real product price.
	 * Applies `jigoshop\product\get_price` filter to allow plugins to modify the price.
	 *
	 * @return float Current product price.
	 */
	public function getPrice()
	{
		return $this->wp->applyFilters('jigoshop\product\get_price', $this->price, $this);
	}

	/**
	 * Sets new product price.
	 * Applies `jigoshop\product\set_price` filter to allow plugins to modify the price. When filter returns false price is not modified at all.
	 *
	 * @param float $price New product price.
	 */
	public function setPrice($price)
	{
		$price = $this->wp->applyFilters('jigoshop\product\set_price', $price, $this);

		if ($price !== false) {
			$this->price = $price;
			$this->dirtyFields[] = 'price';
		}
	}

	/**
	 * @return float Regular product price.
	 */
	public function getRegularPrice()
	{
		return $this->regularPrice;
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

	private function calculatePrice()
	{
		$price = $this->regularPrice;

		if ($this->sales !== null) {
			if (strpos($this->sales->getPrice(), '%') !== false) {
				$discount = trim($this->sales->getPrice(), '%');
				$sale = $this->regularPrice * (1 - $discount / 100);
			} else {
				$sale = $this->regularPrice - $this->sales->getPrice();
			}

			if ($sale < $price) {
				$price = $sale;
			}
		}

		return $price;
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		$toSave = parent::getStateToSave();

		foreach ($this->dirtyFields as $field) {
			switch ($field) {
				case 'regular_price':
					$toSave['regular_price'] = $this->regularPrice;
					break;
			}
		}

		$toSave['sales'] = $this->sales;
		$toSave['price'] = $this->calculatePrice();

		return $toSave;
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		parent::restoreState($state);

		if (isset($state['price'])) {
			$this->price = (float)$state['price'];
		}
		if (isset($state['regular_price'])) {
			$this->regularPrice = (float)$state['regular_price'];
		}

		if (isset($state['sales']) && !empty($state['sales'])) {
			if (is_array($state['sales'])) {
				$this->sales->setEnabled($state['sales']['enabled'] == 'on');
				if ($this->sales->isEnabled()) {
					$this->sales->setFromTime($state['sales']['from']);
					$this->sales->setToTime($state['sales']['to']);
					$this->sales->setPrice($state['sales']['price']);
				}
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
}
