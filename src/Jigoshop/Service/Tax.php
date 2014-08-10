<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Customer;
use Jigoshop\Service\Customer as CustomerService;
use Jigoshop\Entity\Product;
use WPAL\Wordpress;

/**
 * Service calculating tax value for products.
 *
 * @package Jigoshop\Service
 */
class Tax
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Service\Customer */
	private $customers;
	private $taxes = array();
	private $taxClasses = array();

	public function __construct(Wordpress $wp, array $classes, CustomerService $customers)
	{
		$this->wp = $wp;
		$this->taxClasses = $classes;
		$this->customers = $customers;
	}

	/**
	 * @param $product Product\Simple Product to calculate tax for.
	 * @return float Overall tax value.
	 */
	public function calculate(Product\Simple $product)
	{
		$tax = 0.0;
		foreach ($product->getTaxClasses() as $taxClass) {
			$tax += $this->get($product, $taxClass);
		}

		// TODO: Support for compound taxes

		return $tax;
	}

	/**
	 * @param $product Product\Simple Product to calculate tax for.
	 * @param $taxClass string Tax class.
	 * @throws Exception When tax class is not found.
	 * @return float Tax value for selected tax class.
	 */
	public function get(Product\Simple $product, $taxClass)
	{
		if (!in_array($taxClass, $this->taxClasses)) {
			throw new Exception(sprintf('No tax class: %s', $taxClass));
		}

		if (!isset($this->taxes[$taxClass])) {
			$this->taxes[$taxClass] = $this->fetch($taxClass, $this->customers->getCurrent());
		}

		// TODO: Support for compound taxes
		return $this->taxes[$taxClass]['rate'] * $product->getPrice();
	}

	/**
	 * Finds and returns available tax definitions for selected parameters.
	 *
	 * @param $taxClass string Tax class.
	 * @param $customer \Jigoshop\Entity\Customer Customer to fetch data for.
	 * @return array Tax definition.
	 */
	protected function fetch($taxClass, Customer $customer)
	{
//		$wpdb = $this->wp->getWPDB();
//		$query = $wpdb->prepare('
//			SELECT t.id, t.label, t.rate FROM {$wpdb->prefix}jigoshop_tax t
//			WHERE t.class = %s
//		', array($taxClass));
//		$taxes = $wpdb->get_results($query, ARRAY_A);
		// TODO: Finish fetching taxes
		return array(
			'rate' => 0.0,
			'name' => 'No tax',
		);
	}

	/**
	 * @return array List of available tax classes.
	 */
	public function getClasses()
	{
		return $this->taxClasses;
	}

	/**
	 * @param $taxClass string Tax class to get label for.
	 * @return string Tax class label
	 * @throws Exception When tax class is not found.
	 */
	public function getLabel($taxClass)
	{
		if (!in_array($taxClass, $this->taxClasses)) {
			throw new Exception(sprintf('No tax class: %s', $taxClass));
		}

		return $this->taxes[$taxClass]['name'];
	}
}