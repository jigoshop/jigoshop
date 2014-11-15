<?php

namespace Jigoshop\Admin\Product;

use Jigoshop\Admin\PageInterface;
use Jigoshop\Core\Messages;
use Jigoshop\Entity\Product\Attributes\Attribute;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;
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
	/** @var ProductServiceInterface */
	private $productService;

	public function __construct(Wordpress $wp, Messages $messages, ProductServiceInterface $productService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->messages = $messages;
		$this->productService = $productService;

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles, $scripts) {
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('edit.php'))) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, array('product_page_'.Attributes::NAME))) {
				return;
			}

			$styles->add('jigoshop.admin.product_attributes', JIGOSHOP_URL.'/assets/css/admin/product_attributes.css');
			$scripts->add('jigoshop.admin.product_attributes', JIGOSHOP_URL.'/assets/js/admin/product_attributes.js', array('jquery'));
			$scripts->localize('jigoshop.admin.product_attributes', 'jigoshop_admin_product_attributes', array(
				'ajax' => $wp->getAjaxUrl(),
			));
		});

		$wp->addAction('wp_ajax_jigoshop.admin.product_attributes.add_attribute', array($this, 'ajaxAddAttribute'));
	}

	public function ajaxAddAttribute()
	{
		if (!isset($_POST['label']) || empty($_POST['label'])) {
			echo json_encode(array(
				'success' => false,
				'error' => __('Attribute label is not set.', 'jigoshop'),
			));
			exit;
		}
		if (!isset($_POST['slug'])) {
			$_POST['slug'] = null;
		}
		if (!isset($_POST['type']) || !in_array($_POST['type'], array('multiselect', 'select', 'text'))) {
			echo json_encode(array(
				'success' => false,
				'error' => __('Attribute type is not valid.', 'jigoshop'),
			));
			exit;
		}

		$this->productService->addAttribute(
			trim(htmlspecialchars(strip_tags($_POST['label']))),
			trim(htmlspecialchars(strip_tags($_POST['slug']))),
			trim(htmlspecialchars(strip_tags($_POST['type'])))
		);
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
			'attributes' => $this->productService->findAllAttributes(),
			'types' => Attribute::getTypes(),
		));
	}
}
