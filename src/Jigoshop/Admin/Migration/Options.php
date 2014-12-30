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
			default:
				return $value;
		}
	}
}
