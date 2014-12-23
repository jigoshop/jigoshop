<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Factory\Product as ProductFactory;
use Jigoshop\Service\Cache\Product\Simple as SimpleCache;
use Jigoshop\Service\ProductService as Service;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class ProductService
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Factory\Product */
	private $factory;

	public function __construct(Wordpress $wp, Options $options, ProductFactory $factory)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->factory = $factory;
	}

	/**
	 * @return ProductServiceInterface Products service.
	 * @since 2.0
	 */
	public function getService()
	{
		$service = new Service($this->wp, $this->factory);

		switch ($this->options->get('advanced.cache')) {
			case 'simple':
				$service = new SimpleCache($service);
				break;
			default:
				$service = $this->wp->applyFilters('jigoshop\core\get_product_service', $service);
		}

		return $service;
	}
}
