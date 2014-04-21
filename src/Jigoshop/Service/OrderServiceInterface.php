<?php

namespace Jigoshop\Service;

/**
 * Orders service interface.
 *
 * @package Jigoshop\Service
 * @author Amadeusz Starzykiewicz
 */
interface OrderServiceInterface extends ServiceInterface
{
	/**
	 * @param $month int Month to find orders from.
	 * @return array List of orders from selected month.
	 */
	public function findFromMonth($month);
}