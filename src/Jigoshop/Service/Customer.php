<?php

namespace Jigoshop\Service;

/**
 * Customer service.
 *
 * @package Jigoshop\Service
 */
class Customer
{
	/**
	 * @return \Jigoshop\Entity\Customer Current customer entity.
	 */
	public function getCurrent()
	{
		// TODO: Properly fetch customer from session and database
		return new \Jigoshop\Entity\Customer();
	}

	/**
	 * @param $id int Customer ID.
	 * @return \Jigoshop\Entity\Customer Customer for selected ID.
	 */
	public function get($id)
	{
		// TODO: Properly fetch customer based on ID
	}
}