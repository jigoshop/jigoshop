<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Service\Customer;
use Jigoshop\Service\Tax as Service;
use WPAL\Wordpress;

class TaxService
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	private $customerService;

	public function __construct(Wordpress $wp, Options $options, Customer $customerService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
	}

	/**
	 * @return Service Tax service.
	 * @since 2.0
	 */
	public function getService()
	{
		$service = new Service($this->wp, $this->options->get('tax.classes'), $this->customerService);

		switch ($this->options->get('cache_mechanism')) {
			// TODO: Add caching mechanisms
//			case 'simple':
//				$service = new SimpleCache($service);
//				break;
			default:
				$service = $this->wp->applyFilters('jigoshop\core\get_tax_service', $service);
		}

		return $service;
	}
}
