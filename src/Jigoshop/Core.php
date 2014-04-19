<?php

namespace Jigoshop;

use Jigoshop\Core\Assets;
use Jigoshop\Core\Cron;
use Jigoshop\Core\Database;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\PostTypes;
use Jigoshop\Core\Roles;
use Jigoshop\Service\Order as OrderService;
use Jigoshop\Service\Product as ProductService;

class Core
{
	const VERSION = '2.0';

	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var array  */
	private $services = array();
	/** @var \Jigoshop\Core\Cron  */
	private $cron;
	/** @var \Jigoshop\Core\Messages */
	private $messages;
	/** @var \Jigoshop\Admin */
	private $admin;
	/** @var \Jigoshop\Core\Database */
	private $database;

	public function __construct()
	{
		PostTypes::initialize();
		Roles::initialize();
		$this->database = new Database();
		$this->options = new Options();
		$this->messages = new Messages();
		$this->_addQueryFilters();
		$this->cron = new Cron($this->database, $this->options, $this->getOrderService());
		$this->assets = new Assets($this->options);

		if(is_admin())
		{
			$this->admin = new Admin($this);
		}
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
	 * @return \wpdb Database instance.
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * Returns admin panel manager.
	 *
	 * @return Admin Admin panel manager.
	 * @throws Exception When called not in admin.
	 */
	public function getAdmin()
	{
		if(!is_admin())
		{
			throw new Exception('Invalid use of Core::getAdmin() function - not in admin panel!');
		}

		return $this->admin;
	}

	/**
	 * @return OrderService Orders service.
	 * @since 2.0
	 */
	public function getOrderService()
	{
		if(!isset($this->services['order']))
		{
			$service = new OrderService();

			switch($this->options->get('cache_mechanism'))
			{
				case 'simple':
					$service = new Service\Cache\Order\Simple($service);
					break;
				default:
					$service = apply_filters('jigoshop\\core\\get_order_service', $service);
			}

			$this->services['order'] = $service;
		}

		return $this->services['order'];
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
			$service = new ProductService();

			switch($this->options->get('cache_mechanism'))
			{
				case 'simple':
					$service = new Service\Cache\Product\Simple($service);
					break;
				default:
					$service = apply_filters('jigoshop\\core\\get_product_service', $service);
			}

			$this->services['product'] = $service;
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