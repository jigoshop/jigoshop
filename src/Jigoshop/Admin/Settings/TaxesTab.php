<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Currency;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Service\Tax;

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
	/** @var \Jigoshop\Service\Tax */
	private $taxService;

	public function __construct(Options $options, Tax $taxService, Scripts $scripts)
	{
		$this->options = $options->get(self::SLUG);
		$this->taxService = $taxService;
		$scripts->add('jigoshop.admin.taxes', JIGOSHOP_URL.'/assets/js/admin/settings/taxes.js', array('jquery'));
		$scripts->localize('jigoshop.admin.taxes', 'jigoshop_admin_taxes', array(
			'new_class' => Render::get('admin/settings/tax/class', array('class' => array('label' => '', 'class' => ''))),
			'new_rule' => Render::get('admin/settings/tax/rule', array(
				'rule' => array('id' => 0, 'label' => '', 'class' => '', 'rate' => ''),
				'classes' => $this->options['classes'],
			)),
		));
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
		return array(
			array(
				'title' => __('Main', 'jigoshop'),
				'id' => 'main',
				'fields' => array(
					array(
						'name' => '[before_coupons]',
						'title' => __('Apply before coupons?', 'jigoshop'),
						'type' => 'checkbox',
						'value' => $this->options['before_coupons'],
					),
					array(
						'name' => '[included]',
						'title' => __('Included in product price?', 'jigoshop'),
						'type' => 'checkbox',
						'value' => $this->options['included'],
					),
				),
			),
			array(
				'title' => __('Classes', 'jigoshop'),
				'id' => 'classes',
				'fields' => array(
					array(
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
						'name' => '[rules]',
						'type' => 'user_defined',
						'display' => array($this, 'displayRules'),
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
		Render::output('admin/settings/tax/rules', array(
			'rules' => $this->taxService->getRules(),
			'classes' => $classes,
		));
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate(array $settings)
	{
		$settings['before_coupons'] = $settings['before_coupons'] == 'on';
		$settings['included'] = $settings['included'] == 'on';
		$classes = $settings['classes'];
		$settings['classes'] = array();
		foreach ($classes['class'] as $key => $class) {
			$settings['classes'][] = array(
				'class' => $class,
				'label' => $classes['label'][$key],
			);
		}
		foreach ($settings['rules']['rate'] as $key => $rate) {
			$this->taxService->save(array(
				'id' => $settings['rules'][$key]['id'],
				'rate' => $rate,
				'label' => $settings['rules'][$key]['label'],
				'class' => $settings['rules'][$key]['class'],
			));
		}
		unset($settings['rules']);

		return $settings;
	}
}
