<?php

namespace Jigoshop\Admin\Product;

use Jigoshop\Admin\PageInterface;
use Jigoshop\Core\Messages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

/**
 * Product attributes admin page.
 *
 * @package Jigoshop\Product\Admin
 * @author Amadeusz Starzykiewicz
 */
class Attributes implements PageInterface
{
	const NAME = 'jigoshop_product_attributes';

	/** @var Wordpress */
	private $wp;
	/** @var Messages */
	private $messages;

	public function __construct(Wordpress $wp, Messages $messages, Styles $styles)
	{
		$this->wp = $wp;
		$this->messages = $messages;

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles) {
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('edit.php'))) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, array('product_page_'.Attributes::NAME))) {
				return;
			}

			$styles->add('jigoshop.admin.settings', JIGOSHOP_URL.'/assets/css/admin/settings.css');
		});
	}
	/**
	 * @return string Title of page.
	 */
	public function getTitle()
	{
		return __('Attributes', 'jigoshop');
	}

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return 'products';
	}

	/**
	 * @return string Required capability to view the page.
	 */
	public function getCapability()
	{
		return 'manage_product_terms';
	}

	/**
	 * @return string Menu slug.
	 */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/**
	 * Displays the page.
	 */
	public function display()
	{
		Render::output('admin/product_attributes', array(
			'messages' => $this->messages,
			'attributes' => array(),
		));
	}
}
