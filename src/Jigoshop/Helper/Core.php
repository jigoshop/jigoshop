<?php

namespace Jigoshop\Helper;

/**
 * Core helper.
 *
 * @package Jigoshop\Helper
 * @author Amadeusz Starzykiewicz
 */
class Core
{
	/**
	 * Retrieves id of specified Jigoshop page.
	 *
	 * @param $page string Page slug.
	 * @return mixed Page ID.
	 */
	public static function getPageId($page)
	{
		return get_option('jigoshop_'.$page.'_id');
	}

	/**
	 * Sets id of specified Jigoshop page.
	 *
	 * @param $page string Page slug.
	 * @param $id int Page ID.
	 */
	public static function setPageId($page, $id)
	{
		update_option('jigoshop_'.$page.'_id', $id);
	}
}