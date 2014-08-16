<?php

namespace Jigoshop;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Template;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

class Core
{
	const VERSION = '2.0';

	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Core\Messages */
	private $messages;
	/** @var \Jigoshop\Core\Pages */
	private $pages;
	/** @var \Jigoshop\Core\Template */
	private $template;
	/** @var \Jigoshop\Admin */
	private $admin;
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, Pages $pages, Template $template, Admin $admin)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->pages = $pages;
		$this->template = $template;

		if ($wp->isAdmin()) {
			$this->admin = $admin;
		}
	}

	/**
	 * Starts Jigoshop extensions and Jigoshop itself.
	 */
	public function run()
	{
		// TODO: Build required extensions
		$this->_addQueryFilters();
		$this->wp->addFilter('template_include', array($this->template, 'process'));
	}

	private function _addQueryFilters()
	{
		if (!$this->wp->isAdmin()) {
			/* Catalog Filters */
			$this->wp->addFilter('jigoshop\shop\query', array($this, 'shopSortingFilter'));
			$this->wp->addFilter('jigoshop\shop\columns', array($this, 'shopVisibleColumnsFilter'));
			$this->wp->addFilter('jigoshop\shop\per_page', array($this, 'shopPerPageFilter'));
		}
	}

	/**
	 * @return \WPAL\Wordpress WordPress abstraction instance.
	 */
	public function getWordpress()
	{
		return $this->wp;
	}

	/**
	 * Returns admin panel manager.
	 *
	 * @return Admin Admin panel manager.
	 * @throws Exception When called not in admin.
	 */
	public function getAdmin()
	{
		if (!$this->wp->isAdmin()) {
			throw new Exception('Invalid use of Core::getAdmin() function - not in admin panel!');
		}

		return $this->admin;
	}

	/**
	 * @return Options Options holder.
	 * @since 2.0
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @return Messages Messages container.
	 * @since 2.0
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * @return Pages Helper for checking Jigoshop pages.
	 * @since 2.0
	 */
	public function getPages()
	{
		return $this->pages;
	}

	/**
	 * @return array Arguments for post sorting of product list.
	 */
	public function shopSortingFilter()
	{
		$options = $this->options->get('catalog_sort');

		return array(
			'orderby' => $options['order_by'],
			'order' => $options['order'],
		);
	}

	/**
	 * @return int Number of columns in product list.
	 */
	public function shopVisibleColumnsFilter()
	{
		return $this->options->get('catalog_sort.columns');
	}

	/**
	 * @return int Number of items per page in product list.
	 */
	public function shopPerPageFilter()
	{
		return $this->options->get('catalog_per_page');
	}
}