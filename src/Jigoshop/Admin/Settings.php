<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\Settings\GeneralTab;
use Jigoshop\Admin\Settings\OwnerTab;
use Jigoshop\Admin\Settings\TabInterface;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

/**
 * Jigoshop settings.
 *
 * @package Jigoshop\Admin
 * @author Amadeusz Starzykiewicz
 */
class Settings implements PageInterface
{
	const NAME = 'jigoshop_settings';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	private $tabs = array();
	private $currentTab;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
		$wp->addAction('current_screen', array($this, 'register'));
//		$this->wp->addAction('admin_print_scripts-'.$admin_page, array($this, 'settings_scripts')); // TODO: Use JWOS ability to check what current page is to properly include scripts
//		$this->wp->addAction('admin_print_styles-'.$admin_page, array($this, 'settings_styles'));
	}

	/**
	 * Adds new tab to settings screen.
	 *
	 * @param TabInterface $tab The tab.
	 */
	public function addTab(TabInterface $tab)
	{
		$this->tabs[$tab->getSlug()] = $tab;
	}

	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Settings', 'jigoshop');
	}

	/**
	 * @return string Required capability to view the page.
	 */
	public function getCapability()
	{
		return 'manage_jigoshop';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/**
	 * Registers setting item.
	 */
	public function register()
	{
		// Weed out all admin pages except the Jigoshop Settings page hits
		if (!in_array($this->wp->getPageNow(), array('admin.php', 'options.php'))) {
			return;
		}

		$screen = $this->wp->getCurrentScreen();
		if (!in_array($screen->base, array('jigoshop_page_'.self::NAME, 'options'))) {
			return;
		}

		$this->wp->registerSetting(self::NAME, Options::NAME, array($this, 'validate'));

		$tab = $this->getCurrentTab();
		$tab = $this->tabs[$tab];

		// Workaround for PHP pre-5.4
		$that = $this;
		/** @var TabInterface $tab */
		foreach ($tab->getSections() as $section) {
			$this->wp->addSettingsSection($section['id'], $section['title'], function() use ($tab, $that){
				$that->displayTab($tab);
			}, self::NAME);

			foreach($section['fields'] as $field){
				$field = $this->validateField($field);
				$this->wp->addSettingsField($field['id'], $field['title'], array($this, 'displayField'), self::NAME, $section['id'], $field);
			}
		}
	}

	/**
	 * @return string Slug of currently displayed tab
	 */
	protected function getCurrentTab()
	{
		if($this->currentTab === null){
			$this->currentTab = GeneralTab::SLUG;
			// Use REQUEST to work with both GET and POST parameters
			if (isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], array_keys($this->tabs))) {
				$this->currentTab = $_REQUEST['tab'];
			}
		}

		return $this->currentTab;
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		Render::output('admin/settings', array(
			'tabs' => $this->tabs,
			'current_tab' => $this->getCurrentTab(),
		));
	}

	/**
	 * Displays the tab.
	 *
	 * @param TabInterface $tab Tab to display.
	 */
	public function displayTab(TabInterface $tab)
	{
		Render::output('admin/settings/tab', array(
			'tab' => $tab,
		));
	}

	protected function validateField(array $field)
	{
		$defaults = array(
			'id' => null,
			'title' => '',
			'name' => '',
			'type' => '',
			'description' => '',
			'tip' => '',
			'value' => '',
			'options' => array(),
			'classes' => array(),
		);

		$field = $this->wp->wpParseArgs($field, $defaults);

		if($field['id'] === null){
			$field['id'] = Forms::prepareIdFromName($field['name']);
		}
		$field['label_for'] = $field['id'];

		// TODO: Think on how to improve this name hacking
		$field['name'] = Options::NAME.$field['name'];
		// TODO: Properly check if fields are valid.

		return $field;
	}

	/**
	 * Displays field according to definition.
	 *
	 * @param array $field Field parameters.
	 * @return string Field output to display.
	 */
	public function displayField(array $field)
	{
		switch ($field['type']) {
			case 'user_defined':
				// Workaround for PHP pre-5.4
				$f = $field['display'];
				$f($field);
				break;
			case 'text':
				Forms::text($field);
				break;
			case 'select':
				Forms::select($field);
				break;
			case 'checkbox':
				Forms::checkbox($field);
				break;
			case 'constant':
				Forms::constant($field);
				break;
			default:
				// TODO: Filter for custom admin field types.
		}
	}

	/**
	 * Validates settings for WordPress to save.
	 *
	 * @param array $input Input data to validate.
	 * @return array Sanitized output for saving.
	 */
	public function validate($input)
	{
		$options = $this->options->getAll();
		$currentTab = $this->getCurrentTab();
		/** @var TabInterface $tab */
		$tab = $this->tabs[$currentTab];
		$options[$currentTab] = $tab->validate($input);
		return $options;
	}
}
