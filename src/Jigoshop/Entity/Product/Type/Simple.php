<?php

namespace Jigoshop\Entity\Product\Type;

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Sales;
use Jigoshop\Entity\Product\StockStatus;
use WPAL\Wordpress;

class Simple extends Product
{
	const TYPE = 'simple';

	private $price = 0.0;
	private $regularPrice = 0.0;
	/** @var Sales */
	private $sales;
	/** @var StockStatus */
	private $stock;

	public function __construct(Wordpress $wp)
	{
		parent::__construct($wp);
	}

	/**
	 * @return string Product type.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * Sets new product price.
	 * Applies `jigoshop\product\set_price` filter to allow plugins to modify the price. When filter returns false price is not modified at all.
	 *
	 * @param float $price New product price.
	 */
	public function setPrice($price)
	{
		$price = $this->wp->applyFilters('jigoshop\\product\\set_price', $price, $this);

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
		return $this->wp->applyFilters('jigoshop\\product\\get_price', $this->price, $this);
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
		$sales = $this->wp->applyFilters('jigoshop\\product\\set_sales', $sales, $this);

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
				case 'sales':
					$toSave['sales_from'] = $this->sales->getFrom()->getTimestamp();
					$toSave['sales_to'] = $this->sales->getTo()->getTimestamp();
					$toSave['sales_price'] = $this->sales->getPrice();
					break;
				case 'stock':
					$toSave['stock_manage'] = $this->stock->getManage();
					$toSave['stock_allowed_backorders'] = $this->stock->getAllowBackorders();
					$toSave['stock_status'] = $this->stock->getStatus();
					$toSave['stock_stock'] = $this->stock->getStock();
					break;
			}
		}

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
			$this->price = floatval($state['price']);
		}
		if (isset($state['regular_price'])) {
			$this->regularPrice = floatval($state['regular_price']);
		}

		$this->sales = new Sales();
		if (isset($state['sales_from'])) {
			$time = new \DateTime();
			$time->setTimestamp($state['sales_from']);
			$this->sales->setFrom($time);
		}
		if (isset($state['sales_to'])) {
			$time = new \DateTime();
			$time->setTimestamp($state['sales_to']);
			$this->sales->setTo($time);
		}
		if (isset($state['sales_price'])) {
			$this->sales->setPrice(floatval($state['sales_price']));
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
	}

	/**
	 * Marks values provided in the state as dirty.
	 *
	 * @param array $state Product state.
	 */
	public function markAsDirty(array $state)
	{
		if (isset($state['sales_from']) || isset($state['sales_to']) || isset($state['sales_price'])) {
			$this->dirtyFields[] = 'sales';
			unset($state['sales_from'], $state['sales_to'], $state['sales_price']);
		}

		if (isset($state['stock_manage']) || isset($state['stock_allowed_backorders']) || isset($state['stock_status']) || isset($state['stock_stock'])) {
			$this->dirtyFields[] = 'size';
			unset($state['stock_manage'], $state['stock_allowed_backorders'], $state['stock_status'], $state['stock_stock']);
		}

		parent::markAsDirty($state);
	}

	private function calculatePrice()
	{
		$price = $this->regularPrice;

		if(strpos($this->sales->getPrice(), '%') !== false)
		{
			$discount = trim('%', $this->sales->getPrice());
			$sale = $this->regularPrice * (1 - $discount/100);
		}
		else
		{
			$sale = $this->regularPrice - $this->sales->getPrice();
		}

		if($sale < $price)
		{
			$price = $sale;
		}

		return $price;
	}
}
