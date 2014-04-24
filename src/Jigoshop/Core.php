<?php

namespace Jigoshop;

use Jigoshop\Core\Cron;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\PostTypes;
use Jigoshop\Core\Roles;
use Jigoshop\Service\ServiceInterface;
use Jigoshop\Service\Order as OrderService;
use Jigoshop\Service\Product as ProductService;

class Core
{
	const VERSION = '2.0';

	private $_options;
	private $_services = array();
	private $_cron;
	private $_messages;

	public function __construct()
	{
		PostTypes::initialize();
		Roles::initialize();
		$this->_options = new Options();
		$this->_messages = new Messages();
		$this->_addQueryFilters();
		$this->_cron = new Cron($this->getOptions(), $this->getOrderService());
	}

	/**
	 * @return Messages Messages container.
	 */
	public function getMessages()
	{
		return $this->_messages;
	}

	/**
	 * @return Options Options holder.
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * @return OrderService Orders service.
	 */
	public function getOrderService()
	{
		if(!isset($this->_services['order']))
		{
			$this->_services['order'] = $this->_addCaching(new OrderService());
		}

		return $this->_services['order'];
	}

	/**
	 * @return ProductService Products service.
	 */
	public function getProductFactory()
	{
		if(!isset($this->_services['product']))
		{
			$this->_services['product'] = $this->_addCaching(new ProductService());
		}

		return $this->_services['product'];
	}

	private function _addQueryFilters()
	{
		if(!is_admin())
		{
			/* Catalog Filters */
			add_filter('jigoshop\\shop\\query', array($this, '_shopSortingFilter'));
			add_filter('jigoshop\\shop\\columns', array($this, '_shopVisibleColumnsFilter'));
			add_filter('jigoshop\\shop\\per_page', array($this, '_shopPerPageFilter'));
		}
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _shopSortingFilter()
	{
		return array(
			'orderby' => $this->_options->get('catalog_sort_orderby'),
			'order' => $this->_options->get('catalog_sort_direction'),
		);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _shopVisibleColumnsFilter()
	{
		return $this->_options->get('catalog_columns');
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _shopPerPageFilter()
	{
		return $this->_options->get('catalog_per_page');
	}

	/**
	 * Decorates given factory with caching mechanism.
	 *
	 * @param ServiceInterface $service Service to cache.
	 * @return ServiceInterface Caching service.
	 */
	private function _addCaching(ServiceInterface $service)
	{
		switch($this->_options->get('cache_mechanism'))
		{
			default:
				return new Service\Cache\Simple($service);
		}
	}
}