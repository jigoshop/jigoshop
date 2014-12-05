<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product;
use WPAL\Wordpress;

class Simple extends Product implements Purchasable, Shippable, Saleable
{
	const TYPE = 'simple';

	private $regularPrice = 0.0;
	/** @var Attributes\Sales */
	private $sales;
	/** @var Product\Attributes\StockStatus */
	private $stock;

	public function __construct(Wordpress $wp)
	{
		parent::__construct($wp);
		$this->sales = new Attributes\Sales();
		$this->stock = new Product\Attributes\StockStatus();
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
		// TODO: Improve code to calculate price single time only
		return $this->wp->applyFilters('jigoshop\product\get_price', $this->calculatePrice(), $this);
	}

	/**
	 * Sets product stock.
	 * Applies `jigoshop\product\set_stock` filter to allow plugins to modify stock data. When filter returns false stock is not modified at all.
	 *
	 * @param Product\Attributes\StockStatus $stock New product stock status.
	 */
	public function setStock(Product\Attributes\StockStatus $stock)
	{
		$stock = $this->wp->applyFilters('jigoshop\product\set_stock', $stock, $this);

		if ($stock !== false) {
			$this->stock = $stock;
			$this->dirtyFields[] = 'stock';
		}
	}

	/**
	 * @return Product\Attributes\StockStatus Current stock status.
	 */
	public function getStock()
	{
		return $this->stock;
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

	private function calculatePrice()
	{
		$price = $this->regularPrice;

		if ($this->sales !== null && $this->sales->isEnabled()) {
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
		$toSave['stock_manage'] = $this->stock->getManage();
		$toSave['stock_stock'] = $this->stock->getStock();
		$toSave['stock_allow_backorders'] = $this->stock->getAllowBackorders();
		$toSave['stock_status'] = $this->stock->getStatus();
		$toSave['stock_sold'] = $this->stock->getSoldQuantity();

		return $toSave;
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		parent::restoreState($state);

		if (isset($state['regular_price'])) {
			$this->regularPrice = (float)$state['regular_price'];
		}

		if (isset($state['sales']) && !empty($state['sales'])) {
			// TODO: Change into list of fields
			if (is_array($state['sales'])) {
				$this->sales->setEnabled($state['sales']['enabled'] == 'on');
				$this->sales->setFromTime($state['sales']['from']);
				$this->sales->setToTime($state['sales']['to']);
				$this->sales->setPrice($state['sales']['price']);
			} else {
				$this->sales = unserialize($state['sales']);
			}
		}
		if (isset($state['stock_manage'])) {
			$this->stock->setManage((bool)$state['stock_manage']);
		}
		if (isset($state['stock_stock'])) {
			$this->stock->setStock((int)$state['stock_stock']);
		}
		if (isset($state['stock_allow_backorders'])) {
			$this->stock->setAllowBackorders($state['stock_allow_backorders']);
		}
		if (isset($state['stock_status'])) {
			$this->stock->setStatus((int)$state['stock_status']);
		}
		if (isset($state['stock_sold'])) {
			$this->stock->setSoldQuantity((int)$state['stock_sold']);
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
		$this->dirtyFields[] = 'stock';

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
		return true;
	}
}
