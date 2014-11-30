<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Service\Cache\Customer\Simple as SimpleCache;
use Jigoshop\Service\Customer as Service;
use WPAL\Wordpress;

class CustomerService
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Customer */
	private $factory;

	public function __construct(Wordpress $wp, Options $options, Customer $factory)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->factory = $factory;
	}

	/**
	 * @return Service Tax service.
	 * @since 2.0
	 */
	public function getService()
	{
		$service = new Service($this->wp, $this->factory, $this->options);

		switch ($this->options->get('advanced.cache')) {
			case 'simple':
				$service = new SimpleCache($service);
				break;
			default:
				$service = $this->wp->applyFilters('jigoshop\core\get_customer_service', $service);
		}

		return $service;
	}
}
