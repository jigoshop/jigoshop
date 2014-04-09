<?php

namespace Jigoshop\Service;

/**
 * Products service interface.
 *
 * @package Jigoshop\Service
 * @author Jigoshop
 */
interface ProductServiceInterface extends ServiceInterface
{
	/**
	 * @return array List of products that are out of stock.
	 */
	public function findOutOfStock();

	/**
	 * @param $threshold int Threshold where to assume product is low in stock.
	 * @return array List of products that are low in stock.
	 */
	public function findLowStock($threshold);
}