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
		return $this->formatRule(array());
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

		return $this->taxes[$taxClass]['label'];
	}

	/**
	 * Fetches and returns properly formatted list of tax rules.
	 *
	 * @return array List of rules.
	 */
	public function getRules()
	{
		$wpdb = $this->wp->getWPDB();
		$query = "SELECT t.id, t.class, t.label, t.rate FROM {$wpdb->prefix}jigoshop_tax t ORDER BY t.id";
		$taxes = $wpdb->get_results($query, ARRAY_A);
		$result = array();

		foreach ($taxes as $tax) {
			$result[] = $this->formatRule($tax);
		}

		return $result;
	}

	private function formatRule($rule)
	{
		return array(
			'id' => $rule['id'],
			'rate' => (float)$rule['rate'],
			'label' => $rule['label'],
			'class' => $rule['class'],
			'country' => '',
			'states' => array(),
			'postcode' => '',
		);
	}

	/**
	 * Saves tax rule to database.
	 *
	 * @param $rule array Rule to save.
	 */
	public function save(array $rule)
	{
		$wpdb = $this->wp->getWPDB();

		// Process main rule data
		if ($rule['id'] == 0) {
			$wpdb->insert($wpdb->prefix.'jigoshop_tax', array(
				'class' => $rule['class'],
				'label' => $rule['label'],
				'rate' => (float)$rule['rate'],
			));
			$rule['id'] = $wpdb->insert_id;
		} else {
			$wpdb->update($wpdb->prefix.'jigoshop_tax', array(
				'class' => $rule['class'],
				'label' => $rule['label'],
				'rate' => (float)$rule['rate'],
			), array(
				'id' => $rule['id'],
			));
		}

		// Process rule locations
		// TODO
	}

	/**
	 * @param $ids array IDs to preserve.
	 */
	public function removeAllExcept($ids)
	{
		$wpdb = $this->wp->getWPDB();
		$ids = join(',', array_filter(array_map(function($item){ return (int)$item; }, $ids)));
		// Support for removing all tax rules
		if (empty($ids)) {
			$ids = '0';
		}
		$query = "DELETE FROM {$wpdb->prefix}jigoshop_tax WHERE id NOT IN ({$ids})";
		$wpdb->query($query);
	}
}