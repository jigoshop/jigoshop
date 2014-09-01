<?php

namespace Jigoshop\Admin\Product;

use Jigoshop\Admin\PageInterface;

/**
 * Product attributes admin page.
 *
 * @package Jigoshop\Product\Admin
 * @author Amadeusz Starzykiewicz
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

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return 'products';
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
		return 'jigoshop_product_attributes';
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		// TODO: Implement display() method.
	}
}