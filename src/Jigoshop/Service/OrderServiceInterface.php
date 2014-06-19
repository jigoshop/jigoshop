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

	/**
	 * @return array List of orders that are too long in Pending status.
	 */
	public function findOldPending();

	/**
	 * @return array List of orders that are too long in Processing status.
	 */
	public function findOldProcessing();
}