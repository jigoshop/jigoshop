<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Service\TaxServiceInterface;
use WPAL\Wordpress;

/**
 * Taxes tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class TaxesTab implements TabInterface
{
	const SLUG = 'tax';

	/** @var array */
	private $options;
	/** @var TaxServiceInterface */
	private $taxService;

	public function __construct(Wordpress $wp, Options $options, TaxServiceInterface $taxService, Scripts $scripts)
	{
		$this->options = $options->get(self::SLUG);
		$this->taxService = $taxService;
		$options = $this->options;

		$wp->addAction('admin_enqueue_scripts', function() use ($scripts, $options){
			if (!isset($_GET['tab']) || $_GET['tab'] != TaxesTab::SLUG) {
				return;
			}

			$classes = array();
			foreach ($options['classes'] as $class) {
				$classes[$class['class']] = $class['label'];
			}

			$states = array();
			foreach (Country::getAllStates() as $country => $stateList) {
				$states[$country] = array(
					array('id' => '', 'text' => _x('All states', 'admin_taxing', 'jigoshop')),
				);
				foreach ($stateList as $code => $state) {
					$states[$country][] = array('id' => $code, 'text' => $state);
				}
			}

			$countries = array_merge(
				array('' => __('All countries', 'jigoshop')),
				Country::getAll()
			);

			$scripts->add('jigoshop.admin.settings.taxes', JIGOSHOP_URL.'/assets/js/admin/settings/taxes.js', array('jquery'), array('page' => 'jigoshop_page_jigoshop_settings'));
			$scripts->localize('jigoshop.admin.settings.taxes', 'jigoshop_admin_taxes', array(
				'new_class' => Render::get('admin/settings/tax/class', array('class' => array('label' => '', 'class' => ''))),
				'new_rule' => Render::get('admin/settings/tax/rule', array(
					'rule' => array('id' => '', 'label' => '', 'class' => '', 'is_compound' => false, 'rate' => '', 'country' => '', 'states' => array(), 'postcodes' => array()),
					'classes' => $classes,
					'countries' => $countries,
				)),
				'states' => $states,
			));
		});
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Taxes', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return self::SLUG;
	}

	/**
	 * @return array List of items to display.
	 */
	public function getSections()
	{
		$classes = array();
		foreach ($this->options['classes'] as $class) {
			$classes[$class['class']] = $class['label'];
		}

		return array(
			array(
				'title' => __('Main', 'jigoshop'),
				'id' => 'main',
				'fields' => array(
					// TODO: Add support for tax included in prices
//					array(
//						'name' => '[included]',
//						'title' => __('Included in product price?', 'jigoshop'),
//						'type' => 'checkbox',
//						'checked' => $this->options['included'],
//					),
					array(
						'name' => '[price_tax]',
						'title' => __('Show prices', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['price_tax'],
						'options' => array(
							'with_tax' => __('With tax', 'jigoshop'),
							'without_tax' => __('Without tax', 'jigoshop'),
						)
					),
					array(
						'name' => '[shipping]',
						'title' => __('Taxes based on shipping country?', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['shipping'],
					),
				),
			),
			array(
				'title' => __('Classes', 'jigoshop'),
				'id' => 'classes',
				'fields' => array(
					array(
						'title' => '',
						'name' => '[classes]',
						'type' => 'user_defined',
						'display' => array($this, 'displayClasses'),
					),
				),
			),
			array(
				'title' => __('Rules', 'jigoshop'),
				'id' => 'rules',
				'fields' => array(
					array(
						'title' => '',
						'name' => '[rules]',
						'type' => 'user_defined',
						'display' => array($this, 'displayRules'),
					),
				),
			),
			array(
				'title' => __('New products', 'jigoshop'),
				'description' => __('This section defines default tax settings for new products.', 'jigoshop'),
				'id' => 'defaults',
				'fields' => array(
					array(
						'title' => __('Is taxable?', 'jigoshop'),
						'name' => '[defaults][taxable]',
						'type' => 'checkbox',
						'checked' => $this->options['defaults']['taxable'],
					),
					array(
						'title' => __('Tax classes', 'jigoshop'),
						'name' => '[defaults][classes]',
						'type' => 'select',
						'multiple' => true,
						'options' => $classes,
						'value' => $this->options['defaults']['classes'],
					),
				),
			),
		);
	}

	public function displayClasses()
	{
		Render::output('admin/settings/tax/classes', array(
			'classes' => $this->options['classes'],
		));
	}

	public function displayRules()
	{
		$classes = array();
		foreach ($this->options['classes'] as $class) {
			$classes[$class['class']] = $class['label'];
		}
		$countries = array_merge(
			array('' => __('All countries', 'jigoshop')),
			Country::getAll()
		);
		Render::output('admin/settings/tax/rules', array(
			'rules' => $this->taxService->getRules(),
			'classes' => $classes,
			'countries' => $countries,
		));
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate($settings)
	{
		// TODO: Re-enable
//		$settings['included'] = $settings['included'] == 'on';
		$settings['shipping'] = $settings['shipping'] == 'on';
		$classes = $settings['classes'];
		$settings['classes'] = array();
		foreach ($classes['class'] as $key => $class) {
			$settings['classes'][] = array(
				'class' => $class,
				'label' => $classes['label'][$key],
			);
		}

		$settings['defaults']['taxable'] = $settings['defaults']['taxable'] == 'on';
		$settings['defaults']['classes'] = array_filter($settings['defaults']['classes'], function($class) use ($classes) {
			return in_array($class, $classes['class']);
		});

		if (!isset($settings['rules'])) {
			$settings['rules'] = array('id' => array());
		}

		$this->taxService->removeAllExcept($settings['rules']['id']);

		$currentKey = 0;
		foreach ($settings['rules']['id'] as $key => $id) {
			if (empty($id) && $settings['rules']['compound'][$key+1] == 'on') {
				$currentKey++;
			}

			$this->taxService->save(array(
				'id' => $id,
				'rate' => $settings['rules']['rate'][$key],
				'is_compound' => $settings['rules']['compound'][$key+$currentKey] == 'on',
				'label' => $settings['rules']['label'][$key],
				'class' => $settings['rules']['class'][$key],
				'country' => $settings['rules']['country'][$key],
				'states' => $settings['rules']['states'][$key],
				'postcodes' => $settings['rules']['postcodes'][$key],
			));
		}
		unset($settings['rules']);

		return $settings;
	}
}
