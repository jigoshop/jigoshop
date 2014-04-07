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

	private $options;
	private $services = array();
	private $cron;
	private $messages;

	public function __construct()
	{
		PostTypes::initialize();
		Roles::initialize();
		$this->options = new Options();
		$this->messages = new Messages();
		$this->_addQueryFilters();
		$this->cron = new Cron($this->options, $this->getOrderService());
		$this->assets = new Assets($this->options);
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
		if(!isset($this->services['order']))
		{
			$this->services['order'] = $this->_addCaching(new OrderService());
		}

		return $this->services['order'];
	}

	/**
	 * Decorates given factory with caching mechanism.
	 *
	 * @param ServiceInterface $service Service to cache.
	 * @return ServiceInterface Caching service.
	 */
	private function _addCaching(ServiceInterface $service)
	{
		switch($this->options->get('cache_mechanism'))
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
	 * @return ProductService Products service.
	 * @since 2.0
	 */
	public function getProductService()
	{
		if(!isset($this->services['product']))
		{
			$this->services['product'] = $this->_addCaching(new ProductService());
		}

		return $this->services['product'];
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _shopSortingFilter()
	{
		return array(
			'orderby' => $this->options->get('catalog_sort_orderby'),
			'order' => $this->options->get('catalog_sort_direction'),
		);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _shopVisibleColumnsFilter()
	{
		return $this->options->get('catalog_columns');
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function _shopPerPageFilter()
	{
		return $this->options->get('catalog_per_page');
	}
}