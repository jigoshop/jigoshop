<?php

namespace Jigoshop\Factory\Product;

use Jigoshop\Core\Options;
use Jigoshop\Factory\Product as ProductFactory;
use Jigoshop\Service\Cache\Product\Variable\Simple as SimpleCache;
use Jigoshop\Service\Product\Variable as Service;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class VariableService
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Variable */
	private $factory;
	/** @var ProductServiceInterface */
	private $productService;

	public function __construct(Wordpress $wp, Options $options, Variable $factory, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->factory = $factory;
		$this->productService = $productService;
	}

	/**
	 * @return ProductServiceInterface Products service.
	 * @since 2.0
	 */
	public function getService()
	{
		$service = new Service($this->wp, $this->factory, $this->productService);

		switch ($this->options->get('advanced.cache')) {
			case 'simple':
				$service = new SimpleCache($service);
				break;
			default:
				$service = $this->wp->applyFilters('jigoshop\core\get_product_variable_service', $service);
		}

		return $service;
	}
}
