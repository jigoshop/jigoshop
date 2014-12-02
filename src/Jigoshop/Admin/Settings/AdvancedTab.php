<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use WPAL\Wordpress;

/**
 * Advanced tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class AdvancedTab implements TabInterface
{
	const SLUG = 'advanced';

	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var array */
	private $settings;
	/** @var Messages */
	private $messages;

	public function __construct(Wordpress $wp, Options $options, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->settings = $options->get(self::SLUG);
		$this->messages = $messages;
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Advanced', 'jigoshop');
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
		$pages = $this->_getPages();
		$termsPages = $pages;
		$termsPages[0] = __('None', 'jigoshop');

		return array(
			array(
				'title' => __('Cron jobs', 'jigoshop'),
				'id' => 'cron',
				'fields' => array(
					array(
						'name' => '[automatic_complete]',
						'title' => __('Complete processing orders', 'jigoshop'),
						'description' => __("Change all 'Processing' orders older than one month to 'Completed'", 'jigoshop'),
						'tip' => __("For orders that have been completed but the status is still set to 'processing'.  This will move them to a 'completed' status without sending an email out to all the customers.", 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->settings['automatic_complete'],
					),
					array(
						'name' => '[automatic_reset]',
						'title' => __('Reset pending orders', 'jigoshop'),
						'description' => __("Change all 'Pending' orders older than one month to 'On Hold'", 'jigoshop'),
						'tip' => __("For customers that have not completed the Checkout process or haven't paid for an order after a period of time, this will reset the Order to On Hold allowing the Shop owner to take action.  WARNING: For the first use on an existing Shop this setting <em>can</em> generate a <strong>lot</strong> of email!", 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->settings['automatic_reset'],
					),
				),
			),
			array(
				'title' => __('Enforcing', 'jigoshop'),
				'id' => 'enforcing',
				'fields' => array(
					array(
						'name' => '[force_ssl]',
						'title' => __('Force SSL on checkout', 'jigoshop'),
						'description' => __('Enforces WordPress to use SSL on checkout pages.', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->settings['force_ssl'],
					),
				),
			),
			array(
				'title' => __('Integration', 'jigoshop'),
				'id' => 'integration',
				'fields' => array(
					// TODO: Share This integration
//					array(
//						'name' => '[integration][share_this]',
//						'title' => __('ShareThis Publisher ID', 'jigoshop'),
//						'description' => __("Enter your <a href='http://sharethis.com/account/'>ShareThis publisher ID</a> to show ShareThis on product pages.", 'jigoshop'),
//						'tip' => __('ShareThis is a small social sharing widget for posting links on popular sites such as Twitter and Facebook.', 'jigoshop'),
//						'type' => 'text',
//						'value' => $this->settings['integration']['share_this'],
//					),
					array(
						'name' => '[integration][google_analytics]',
						'title' => __('Google Analytics ID', 'jigoshop'),
						'description' => __('Log into your Google Analytics account to find your ID. e.g. <code>UA-XXXXXXX-X</code>', 'jigoshop'),
						'type' => 'text',
						'value' => $this->settings['integration']['google_analytics'],
					),
				),
			),
			array(
				'title' => __('Others', 'jigoshop'),
				'id' => 'others',
				'fields' => array(
					array(
						'name' => '[cache]',
						'title' => __('Caching mechanism', 'jigoshop'),
						'description' => __('Decides which mechanism for caching is used on the page.', 'jigoshop'),
						'type' => 'select',
						'value' => $this->settings['cache'],
						// TODO: Proper options for cache
						'options' => array(
							'simple' => __('Simple', 'jigoshop'),
						),
					),
				),
			),
			array(
				'title' => __('Pages', 'jigoshop'),
				'id' => 'pages',
				'description' => __('This section allows you to change content source page for each part of Jigoshop. It will not change the main behaviour though.', 'jigoshop'),
				'fields' => array(
					array(
						'name' => '[pages][shop]',
						'title' => __('Shop page', 'jigoshop'),
						'type' => 'select',
						'value' => $this->settings['pages']['shop'],
						'options' => $pages,
					),
					array(
						'name' => '[pages][cart]',
						'title' => __('Cart page', 'jigoshop'),
						'type' => 'select',
						'value' => $this->settings['pages']['cart'],
						'options' => $pages,
					),
					array(
						'name' => '[pages][checkout]',
						'title' => __('Checkout page', 'jigoshop'),
						'type' => 'select',
						'value' => $this->settings['pages']['checkout'],
						'options' => $pages,
					),
					array(
						'name' => '[pages][thanks]',
						'title' => __('Thanks page', 'jigoshop'),
						'type' => 'select',
						'value' => $this->settings['pages']['thanks'],
						'options' => $pages,
					),
					array(
						'name' => '[pages][account]',
						'title' => __('My account page', 'jigoshop'),
						'type' => 'select',
						'value' => $this->settings['pages']['account'],
						'options' => $pages,
					),
					array(
						'name' => '[pages][terms]',
						'title' => __('Terms page', 'jigoshop'),
						'type' => 'select',
						'value' => $this->settings['pages']['terms'],
						'options' => $termsPages
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
	public function validate(array $settings)
	{
		$settings['automatic_complete'] = $settings['automatic_complete'] == 'on';
		$settings['automatic_reset'] = $settings['automatic_reset'] == 'on';
		$settings['force_ssl'] = $settings['force_ssl'] == 'on';

		$pages = $this->_getPages();

		if (!in_array($settings['pages']['shop'], array_keys($pages))) {
			$this->messages->addError(__('Invalid shop page, please select again.', 'jigoshop'));
		} else {
			$this->options->setPageId(Pages::SHOP, $settings['pages']['shop']);
		}

		if (!in_array($settings['pages']['cart'], array_keys($pages))) {
			$this->messages->addError(__('Invalid cart page, please select again.', 'jigoshop'));
		} else {
			$this->options->setPageId(Pages::CART, $settings['pages']['cart']);
		}

		if (!in_array($settings['pages']['checkout'], array_keys($pages))) {
			$this->messages->addError(__('Invalid checkout page, please select again.', 'jigoshop'));
		} else {
			$this->options->setPageId(Pages::CHECKOUT, $settings['pages']['checkout']);
		}

		if (!in_array($settings['pages']['thanks'], array_keys($pages))) {
			$this->messages->addError(__('Invalid thanks page, please select again.', 'jigoshop'));
		} else {
			$this->options->setPageId(Pages::THANK_YOU, $settings['pages']['thanks']);
		}

		if (!in_array($settings['pages']['account'], array_keys($pages))) {
			$this->messages->addError(__('Invalid My account page, please select again.', 'jigoshop'));
		} else {
			$this->options->setPageId(Pages::ACCOUNT, $settings['pages']['account']);
		}
		if (!empty($settings['pages']['terms']) && $settings['pages']['terms'] != 0 && !in_array($settings['pages']['terms'], array_keys($pages))) {
			$this->messages->addError(__('Invalid terms page, please select again.', 'jigoshop'));
		}

		return $settings;
	}

	private function _getPages()
	{
		$pages = array();
		foreach (get_pages() as $page) {
			$pages[$page->ID] = $page->post_title;
		}

		return $pages;
	}
}
