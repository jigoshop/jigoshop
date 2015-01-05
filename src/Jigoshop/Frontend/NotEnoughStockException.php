<?php

namespace Jigoshop\Frontend;

use Jigoshop\Exception;

class NotEnoughStockException extends Exception
{
	/** @var int */
	private $stock;

	function __construct($stock)
	{
		$this->stock = $stock;
	}

	/**
	 * @return int Current stock value.
	 */
	public function getStock()
	{
		return $this->stock;
	}
}
