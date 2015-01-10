<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Options;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

/**
 * Jigoshop Web Optimization System tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class OptimizationTab implements TabInterface
{
	const SLUG = 'optimization';

	/** @var array */
	private $options;

	public function __construct(Wordpress $wp, Options $options, Scripts $scripts)
	{
		$this->options = $options->get(self::SLUG);
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
		return __('Web Optimization System', 'jigoshop');
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
		$files = iterator_count(new \FilesystemIterator(JIGOSHOP_DIR.'/cache/assets', \FilesystemIterator::SKIP_DOTS));
		return array(
			array(
				'title' => __('Main', 'jigoshop'),
				'id' => 'main',
				'fields' => array(
					array(
						'name' => '[enabled]',
						'title' => __('Enable Jigoshop Web Optimization System', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['enabled'],
					),
					array(
						'name' => '[files]',
						'title' => __('Files in cache', 'jigoshop'),
						'type' => 'constant',
						'value' => $files,
					),
					array(
						'name' => '[clear_cache]',
						'title' => __('Clear cache', 'jigoshop'),
						'type' => 'checkbox',
						'description' => __('This will remove all files in cache causing the plugin to recreate all data.', 'jigoshop'),
						'tip' => __('To clear cache please check the checkbox and save settings.', 'jigoshop'),
						'checked' => false,
					),
				),
			),
		);
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
		$settings['enabled'] = $settings['enabled'] == 'on';
		unset($settings['files']);
		if (isset($settings['clear_cache']) && $settings['clear_cache'] == 'on') {
			foreach (new \DirectoryIterator(JIGOSHOP_DIR.'/cache/assets') as $file) {
				/** @var $file \DirectoryIterator */
				if (!$file->isDot() && $file->getFilename() != '.ignore') {
					unlink($file->getPathname());
				}
			}
		}
		unset($settings['clear_cache']);

		return $settings;
	}
}
