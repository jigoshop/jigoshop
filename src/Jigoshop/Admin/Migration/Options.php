<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Helper\Render;
use Jigoshop\Service\TaxServiceInterface;
use WPAL\Wordpress;

class Options implements Tool
{
	const ID = 'jigoshop_options_migration';

	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var TaxServiceInterface */
	private $taxService;

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options, TaxServiceInterface $taxService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->taxService = $taxService;
	}

	/**
	 * @return string Tool ID.
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * Shows migration tool in Migration tab.
	 */
	public function display()
	{
		Render::output('admin/migration/options', array());
	}

	/**
	 * Migrates data from old format to new one.
	 */
	public function migrate()
	{
		$options = $this->wp->getOption('jigoshop_options');
		$transformations = \Jigoshop_Base::get_options()->__getTransformations();
		$transformations = $this->_addShippingTransformations($transformations);
		$transformations = $this->_addPaymentTransformations($transformations);

		foreach ($transformations as $old => $new) {
			$value = $this->_transform($old, $options[$old]);

			if ($value !== null) {
				$this->options->update($new, $value);
			}
		}

		// Migrate tax rates
		if (!is_array($options['jigoshop_tax_rates'])) {
			$options['jigoshop_tax_rates'] = array();
		}

		$options['jigoshop_tax_rates'] = array_values($options['jigoshop_tax_rates']);
		for ($i = 0, $endI = count($options['jigoshop_tax_rates']); $i < $endI;) {
			$rateDate = array(
				'id' => '0',
				'rate' => $options['jigoshop_tax_rates'][$i]['rate'],
				'label' => empty($options['jigoshop_tax_rates'][$i]['label']) ? __('Tax', 'jigoshop') : $options['jigoshop_tax_rates'][$i]['label'],
				'class' => $options['jigoshop_tax_rates'][$i]['class'] == '*' ? 'standard' : $options['jigoshop_tax_rates'][$i]['class'], // TODO: Check how other classes are used
				'country' => $options['jigoshop_tax_rates'][$i]['country'],
				'states' => $options['jigoshop_tax_rates'][$i]['state'],
				'postcodes' => '',
			);
			$i++;

			while ($i < $endI && $options['jigoshop_tax_rates'][$i]['rate'] == $rateDate['rate'] && $options['jigoshop_tax_rates'][$i]['country'] == $rateDate['country']) {
				if (!empty($options['jigoshop_tax_rates'][$i]['state'])) {
					$rateDate['states'] .= ','.$options['jigoshop_tax_rates'][$i]['state'];
				}

				$i++;
			}

			$this->taxService->save($rateDate);
		}

		// TODO: How to migrate plugin options?
		$this->options->saveOptions();
	}

	private function _transform($key, $value)
	{
		switch ($key) {
			case 'jigoshop_allowed_countries':
				return $value !== 'all';
			case 'jigoshop_tax_classes':
				$value = explode("\n", $value);
				return array_merge($this->options->get('tax.classes', array()), array_map(function($label){
					return array(
						'class' => sanitize_title($label),
						'label' => $label,
					);
				}, $value));
			case 'jigoshop_tax_rates':
				return null;
			case 'jigoshop_free_shipping_enabled':
				return $value == 'yes';
			case 'jigoshop_local_pickup_enabled':
				return $value == 'yes';
			case 'jigoshop_flat_rate_enabled':
				return $value == 'yes';
			case 'jigoshop_cheque_enabled':
				return $value == 'yes';
			case 'jigoshop_cod_enabled':
				return $value == 'yes';
			case 'jigoshop_paypal_enabled':
				return $value == 'yes';
			case 'jigoshop_paypal_force_payment':
				return $value == 'yes';
			case 'jigoshop_paypal_testmode':
				return $value == 'yes';
			case 'jigoshop_paypal_send_shipping':
				return $value == 'yes';
			case 'jigoshop_use_wordpress_tiny_crop':
				return $value == 'yes';
			case 'jigoshop_use_wordpress_thumbnail_crop':
				return $value == 'yes';
			case 'jigoshop_use_wordpress_catalog_crop':
				return $value == 'yes';
			case 'jigoshop_use_wordpress_featured_crop':
				return $value == 'yes';
			case 'jigoshop_force_ssl_checkout':
				return $value == 'yes';
			case 'jigoshop_enable_guest_checkout':
				return $value == 'yes';
			case 'jigoshop_enable_guest_login':
				return $value == 'yes';
			case 'jigoshop_enable_signup_form':
				return $value == 'yes';
			case 'jigoshop_reset_pending_orders':
				return $value == 'yes';
			case 'jigoshop_complete_processing_orders':
				return $value == 'yes';
			case 'jigoshop_downloads_require_login':
				return $value == 'yes';
			case 'jigoshop_flat_rate_tax_status':
				return $value == 'taxable';
			case 'jigoshop_currency_pos':
				switch($value) {
					case 'left':
						return '%1$s%3$s';
					case 'left_space':
						return '%1$s %3$s';
					case 'right':
						return '%3$s%1$s';
					case 'right_space':
						return '%3$s %1$s';
					case 'left_code':
						return '%2$s%3$s';
					case 'left_code_space':
						return '%2$s %3$s';
					case 'right_code':
						return '%3$s%2$s';
					case 'right_code_space':
						return '%3$s %2$s';
					case 'symbol_code':
						return '%1$s%3$s%2$s';
					case 'symbol_code_space':
						return '%1$s %3$s %2$s';
					case 'code_symbol':
						return '%2$s%3$s%1$s';
					case 'code_symbol_space':
						return '%2$s %3$s %1$s';
				}
			default:
				return $value;
		}
	}

	private function _addShippingTransformations($transformations)
	{
		return array_merge($transformations, array(
			'jigoshop_free_shipping_enabled' => 'shipping.free_shipping.enabled',
			'jigoshop_free_shipping_title' => 'shipping.free_shipping.title',
			'jigoshop_free_shipping_minimum_amount' => 'shipping.free_shipping.minimum',
			'jigoshop_free_shipping_availability' => 'shipping.free_shipping.available_for',
			'jigoshop_free_shipping_countries' => 'shipping.free_shipping.countries',
			'jigoshop_local_pickup_enabled' => 'shipping.local_pickup.enabled',
			'jigoshop_flat_rate_enabled' => 'shipping.flat_rate.enabled',
			'jigoshop_flat_rate_title' => 'shipping.flat_rate.title',
			'jigoshop_flat_rate_availability' => 'shipping.flat_rate.available_for',
			'jigoshop_flat_rate_countries' => 'shipping.flat_rate.countries',
			'jigoshop_flat_rate_type' => 'shipping.flat_rate.type',
			'jigoshop_flat_rate_tax_status' => 'shipping.flat_rate.is_taxable',
			'jigoshop_flat_rate_cost' => 'shipping.flat_rate.cost',
			'jigoshop_flat_rate_handling_fee' => 'shipping.flat_rate.fee',
		));
	}

	private function _addPaymentTransformations($transformations)
	{
		return array_merge($transformations, array(
			'jigoshop_cheque_enabled' => 'payment.cheque.enabled',
			'jigoshop_cheque_title' => 'payment.cheque.title',
			'jigoshop_cheque_description' => 'payment.cheque.description',
			'jigoshop_cod_enabled' => 'payment.on_delivery.enabled',
			'jigoshop_cod_title' => 'payment.on_delivery.title',
			'jigoshop_cod_description' => 'payment.on_delivery.description',
			'jigoshop_paypal_enabled' => 'payment.paypal.enabled',
			'jigoshop_paypal_title' => 'payment.paypal.title',
			'jigoshop_paypal_description' => 'payment.paypal.description',
			'jigoshop_paypal_email' => 'payment.paypal.email',
			'jigoshop_paypal_force_payment' => 'payment.paypal.force_payment',
			'jigoshop_paypal_testmode' => 'payment.paypal.test_mode',
			'jigoshop_sandbox_email' => 'payment.paypal.test_email',
			'jigoshop_paypal_send_shipping' => 'payment.paypal.send_shipping',
		));
	}
}
