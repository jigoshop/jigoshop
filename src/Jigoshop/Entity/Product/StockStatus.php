<?php

namespace Jigoshop\Entity\Product;

/**
 * Product's stock status.
 *
 * @package Jigoshop\Entity\Product
 * @author Amadeusz Starzykiewicz
 */
class StockStatus
{
	const OUT_STOCK = 0;
	const IN_STOCK = 1;

	/** @var boolean */
	private $manage = false;
	/** @var int */
	private $status = self::IN_STOCK;
	/** @var boolean */
	private $allowBackorders = false;
	/** @var int */
	private $stock = 0;
	/** @var int */
	private $soldQuantity = 0;

	/**
	 * @param boolean $allowBackorders Allow backorders?
	 */
	public function setAllowBackorders($allowBackorders)
	{
		$this->allowBackorders = (boolean)$allowBackorders;
	}

	/**
	 * @param boolean $manage Manage product's stock?
	 */
	public function setManage($manage)
	{
		$this->manage = (boolean)$manage;
	}

	/**
	 * @param int $soldQuantity Number of products sold.
	 */
	public function addSoldQuantity($soldQuantity)
	{
		$this->soldQuantity += intval($soldQuantity);
	}

	/**
	 * Sets product status.
	 *
	 * Please use provided constants:
	 *   * StockStatus::IN_STOCK - product is in stock
	 *   * StockStatus::OUT_STOCK - product is out of stock
	 *
	 * @param int $status Product status.
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * @param int $stock Number of products in stock.
	 */
	public function setStock($stock)
	{
		$this->stock = $stock;
	}

	/**
	 * @return boolean Does product allow backorders?
	 */
	public function getAllowBackorders()
	{
		return $this->allowBackorders;
	}

	/**
	 * @return boolean Is product's stock managed by Jigoshop?
	 */
	public function getManage()
	{
		return $this->manage;
	}

	/**
	 * @return int Number of product's already sold.
	 */
	public function getSoldQuantity()
	{
		return $this->soldQuantity;
	}

	/**
	 * @return int Product status.
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return int Number of product's in stock.
	 */
	public function getStock()
	{
		return $this->stock;
	}
}