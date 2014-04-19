<?php

namespace Jigoshop\Core;

/**
 * Provides abstraction for database calls.
 *
 * @package Jigoshop\Core
 * @author Jigoshop
 */
class Database
{
	/**
	 * @var \wpdb
	 */
	private $wpdb;

	public function __construct()
	{
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public function getWPDB()
	{
		return $this->wpdb;
	}
}