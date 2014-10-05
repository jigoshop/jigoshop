<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Product;
use Jigoshop\Service\Customer as CustomerService;
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
	private $taxIncludedInPrice;
	private $taxes = array();
	private $taxClasses = array();
	private $rules;

	public function __construct(Wordpress $wp, array $classes, CustomerService $customers, $taxIncludedInPrice)
	{
		$this->wp = $wp;
		$this->taxClasses = $classes;
		$this->customers = $customers;
		$this->taxIncludedInPrice = $taxIncludedInPrice;
	}

	/**
	 * @param $product Product|Product\Purchasable Product to calculate tax for.
	 * @return float Overall tax value.
	 */
	public function calculate(Product $product)
	{
		$tax = 0.0;
		foreach ($product->getTaxClasses() as $taxClass) {
			$tax += $this->get($product, $taxClass);
		}

		// TODO: Support for compound taxes

		return $tax;
	}

	/**
	 * @param $product Product|Product\Purchasable Product to calculate tax for.
	 * @param $taxClass string Tax class.
	 * @throws Exception When tax class is not found.
	 * @return float Tax value for selected tax class.
	 */
	public function get(Product $product, $taxClass)
	{
		if (!in_array($taxClass, $this->taxClasses)) {
			throw new Exception(sprintf('No tax class: %s', $taxClass));
		}

		if (!isset($this->taxes[$taxClass])) {
			$this->taxes[$taxClass] = $this->fetch($taxClass, $this->customers->getCurrent());
		}

		// TODO: Support for compound taxes
		if ($this->taxIncludedInPrice) {
			return $product->getPrice() * (1 - 1 / (100 + $this->taxes[$taxClass]['rate']) * 100);
		}

		return $this->taxes[$taxClass]['rate'] * $product->getPrice() / 100;
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
		// TODO: Probably it will be good idea to update getRules() call to fetch and format only proper rules for the customer
		$rules = array_filter($this->getRules(), function($item) use ($taxClass, $customer) {
			return $item['class'] == $taxClass &&
				(empty($item['country']) || $item['country'] == $customer->getCountry()) &&
				(empty($item['states']) || in_array($customer->getState(), $item['states'])) &&
				(empty($item['postcodes']) || in_array($customer->getPostcode(), $item['postcodes']))
			;
		});

		return array_shift($rules);
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
			throw new Exception(sprintf(__('No tax class: %s', 'jigoshop'), $taxClass));
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
		if ($this->rules === null) {
			$wpdb = $this->wp->getWPDB();
			$query = "
				SELECT t.id, t.class, t.label, t.rate, tl.country, tl.state, tl.postcode FROM {$wpdb->prefix}jigoshop_tax t
			  LEFT JOIN {$wpdb->prefix}jigoshop_tax_location tl ON tl.tax_id = t.id
				ORDER BY t.id
			";
			$taxes = $wpdb->get_results($query, ARRAY_A);
			$result = array();
			$processed = array();

			foreach ($taxes as $tax) {
				if (in_array($tax['id'], $processed)) {
					continue;
				}

				$processed[] = $tax['id'];
				$rule = array_filter($taxes, function ($item) use ($tax){
						return $item['id'] == $tax['id'];
					});
				$result[] = $this->formatRule($rule);
			}

			$this->rules = $result;
		}

		return $this->rules;
	}

	private function formatRule(array $source)
	{
		$item = reset($source);
		$rule = array(
			'id' => $item['id'],
			'rate' => (float)$item['rate'],
			'label' => $item['label'],
			'class' => $item['class'],
			'country' => $item['country'],
			'states' => array(),
			'postcodes' => array(),
		);

		foreach ($source as $item) {
			if (!in_array($item['state'], $rule['states']) && !empty($item['state'])) {
				$rule['states'][] = $item['state'];
			}
			if (!in_array($item['postcode'], $rule['postcodes']) && !empty($item['postcode'])) {
				$rule['postcodes'][] = $item['postcode'];
			}
		}

		return $rule;
	}

	/**
	 * @param $rule array Rule to save.
	 * @return array Saved rule.
	 */
	public function save(array $rule)
	{
		$wpdb = $this->wp->getWPDB();

		// Process main rule data
		if ($rule['id'] == '0') {
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
		if (!empty($rule['country'])) {
			$states = explode(',', $rule['states']);
			$postcodes = explode(',', $rule['postcodes']);

			if (!empty($states)) {
				$this->_removeAllStatesExcept($rule, $states);
				foreach ($states as $state) {
					$this->_removeAllPostcodesExcept($rule, $state, $postcodes);
					$this->_addPostcodes($rule, $state, $postcodes);
				}
			} else {
				$this->_removeAllPostcodesExcept($rule, '', $postcodes);
				$this->_addPostcodes($rule, '', $postcodes);
			}
		}

		return $rule;
	}

	/**
	 * @param $ids array IDs to preserve.
	 */
	public function removeAllExcept($ids)
	{
		$wpdb = $this->wp->getWPDB();
		$ids = join(',', array_filter(array_map(function($item){ return (int)$item; }, $ids)));
		// Support for removing all items
		if (empty($ids)) {
			$ids = '0';
		}
		$query = "DELETE FROM {$wpdb->prefix}jigoshop_tax WHERE id NOT IN ({$ids})";
		$wpdb->query($query);
	}

	private function _addPostcodes($rule, $state, $postcodes)
	{
		$wpdb = $this->wp->getWPDB();
		$ids = array();
		$query = $wpdb->prepare("
			SELECT postcode FROM {$wpdb->prefix}jigoshop_tax_location
			WHERE tax_id = %d AND country = %s AND state = %s
		", array($rule['id'], $rule['country'], $state));
		$existing = $wpdb->get_col($query);

		foreach ($postcodes as $postcode) {
			if (!in_array($postcode, $existing)) {
				$wpdb->insert($wpdb->prefix.'jigoshop_tax_location', array(
					'tax_id' => $rule['id'],
					'country' => $rule['country'],
					'state' => $state,
					'postcode' => $postcode,
				));
				$ids[] = $wpdb->insert_id;
			}
		}

		return $ids;
	}

	private function _removeAllStatesExcept($rule, $states)
	{
		$wpdb = $this->wp->getWPDB();
		$values = join(',', array_filter(array_map(function($item){
			$item = esc_sql($item);
			return "'{$item}'";
		}, $states)));
		// Support for removing all items
		if (empty($values)) {
			$values = 'NULL';
		}
		$query = $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}jigoshop_tax_location WHERE state NOT IN ({$values}) AND tax_id = %d",
			array($rule['id'])
		);
		$wpdb->query($query);
	}

	private function _removeAllPostcodesExcept($rule, $state, $postcodes)
	{
		$wpdb = $this->wp->getWPDB();
		$values = join(',', array_filter(array_map(function($item){
			$item = esc_sql($item);
			return "'{$item}'";
		}, $postcodes)));
		// Support for removing all items
		if (empty($values)) {
			$values = 'NULL';
		}
		$query = $wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}jigoshop_tax_location WHERE postcode NOT IN ({$values}) AND state = %s AND tax_id = %d",
			array($state, $rule['id'])
		);
		$wpdb->query($query);
	}
}
