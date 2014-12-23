<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Service\CustomerServiceInterface;
use Jigoshop\Service\TaxService as Service;
use WPAL\Wordpress;

class TaxService
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	private $customerService;

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService)
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
		$classes = array_map(function($item){ return $item['class']; }, $this->options->get('tax.classes'));
		$service = new Service($this->wp, $classes, $this->customerService, $this->options->get('tax.included'));

		switch ($this->options->get('advanced.cache')) {
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
