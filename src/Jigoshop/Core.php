<?php

namespace Jigoshop;

use Jigoshop\Core\Assets;
use Jigoshop\Core\Cron;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\PostTypes;
use Jigoshop\Core\Roles;
use Jigoshop\Service\Order as OrderService;
use Jigoshop\Service\Product as ProductService;
use Jigoshop\Service\ServiceInterface;

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
		$this->_cron = new Cron($this->_options, $this->getOrderService());
		$this->_assets = new Assets($this->_options);
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

	/**
	 * @return OrderService Orders service.
	 * @since 2.0
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

	/**
	 * @return Options Options holder.
	 * @since 2.0
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * @return Messages Messages container.
	 * @since 2.0
	 */
	public function getMessages()
	{
		return $this->_messages;
	}

	/**
	 * @return ProductService Products service.
	 * @since 2.0
	 */
	public function getProductService()
	{
		if(!isset($this->_services['product']))
		{
			$this->_services['product'] = $this->_addCaching(new ProductService());
		}

		return $this->_services['product'];
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
}