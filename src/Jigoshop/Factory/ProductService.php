<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Service\Cache\Product\Simple as SimpleCache;
use Jigoshop\Service\Product as Service;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class ProductService
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
	}

	/**
	 * @return ProductServiceInterface Products service.
	 * @since 2.0
	 */
	public function getService()
	{
		$service = new Service($this->wp);

		switch ($this->options->get('cache_mechanism')) {
			case 'simple':
				$service = new SimpleCache($service);
				break;
			default:
				$service = $this->wp->applyFilters('jigoshop\\core\\get_product_service', $service);
		}

		return $service;
	}
}