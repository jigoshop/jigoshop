<?php
namespace Jigoshop\Admin\Migration;

/**
 * Interface for migration tools.
 *
 * @package Jigoshop\Admin\Migration
 */
interface Tool
{
	/**
	 * @return string Tool ID.
	 */
	public function getId();

	/**
	 * Shows migration tool in Migration tab.
	 */
	public function display();

	/**
	 * Migrates data from old format to new one.
	 */
	public function migrate();
}
