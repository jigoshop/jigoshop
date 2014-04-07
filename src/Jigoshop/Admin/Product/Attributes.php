<?php

namespace Jigoshop\Product\Admin;

use Jigoshop\Admin\PageInterface;

/**
 * Product attributes admin page.
 *
 * @package Jigoshop\Product\Admin
 * @author Jigoshop
 */
class Attributes implements PageInterface
{
	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Attributes', 'jigoshop');
	}

	/**
	 * @return string Required capability to view the page.
	 */
	public function getCapability()
	{
		return 'manage_product_terms';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return 'product_attributes';
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		// TODO: Implement display() method.
	}
}