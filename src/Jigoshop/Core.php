<?php

namespace Jigoshop;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\PostTypes;
use Jigoshop\Core\Roles;
use WPAL\Wordpress;

class Core
{
	const VERSION = '2.0';

	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Core\Messages */
	private $messages;
	/** @var \Jigoshop\Admin */
	private $admin;
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, Admin $admin)
	{
		PostTypes::initialize();
		Roles::initialize();
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->_addQueryFilters();

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
	}

	private function _addQueryFilters()
	{
		if (!$this->wp->isAdmin()) {
			/* Catalog Filters */
			$this->wp->addFilter('jigoshop\\shop\\query', array($this, '_shopSortingFilter'));
			$this->wp->addFilter('jigoshop\\shop\\columns', array($this, '_shopVisibleColumnsFilter'));
			$this->wp->addFilter('jigoshop\\shop\\per_page', array($this, '_shopPerPageFilter'));
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

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _shopSortingFilter()
	{
		$options = $this->options->get('catalog_sort');

		return array(
			'orderby' => $options['order_by'],
			'order' => $options['order'],
		);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _shopVisibleColumnsFilter()
	{
		return $this->options->get('catalog_sort.columns');
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _shopPerPageFilter()
	{
		return $this->options->get('catalog_per_page');
	}
}