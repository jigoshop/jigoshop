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

		foreach ($transformations as $old => $new) {
			$value = $this->_transform($old, $options[$old]);

			if ($value !== null) {
				$this->options->update($new, $value);
			}
		}

		// Migrate tax rules
		foreach ($options['jigoshop_tax_rates'] as $key => $rate) {
			$this->taxService->save(array(
				'id' => '0',
				'rate' => $rate['rate'],
				'label' => empty($rate['label']) ? __('Tax', 'jigoshop') : $rate['label'],
				'class' => $rate['class'] == '*' ? 'standard' : $rate['class'], // TODO: Check how other classes are used
				'country' => $rate['country'],
				'states' => $rate['state'],
				'postcodes' => '',
			));
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
			default:
				return $value;
		}
	}
}
