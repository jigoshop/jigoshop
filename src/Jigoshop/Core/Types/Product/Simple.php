<?php

namespace Jigoshop\Core\Types\Product;

use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class Simple implements Type
{
	/**
	 * Returns identifier for the type.
	 *
	 * @return string Type identifier.
	 */
	public function getId()
	{
		return \Jigoshop\Entity\Product\Simple::TYPE;
	}

	/**
	 * Returns human-readable name for the type.
	 *
	 * @return string Type name.
	 */
	public function getName()
	{
		return __('Simple', 'jigoshop');
	}

	/**
	 * Returns class name to use as type entity.
	 * This class MUST extend {@code \Jigoshop\Entity\Product}!
	 *
	 * @return string Fully qualified class name.
	 */
	public function getClass()
	{
		return '\Jigoshop\Entity\Product\Simple';
	}

	/**
	 * Initializes product type.
	 *
	 * @param Wordpress $wp WordPress Abstraction Layer
	 * @param array $enabledTypes List of all available types.
	 */
	public function initialize(Wordpress $wp, array $enabledTypes)
	{
		$wp->addFilter('jigoshop\cart\add', array($this, 'addToCart'), 10, 2);
		$wp->addAction('jigoshop\admin\product\assets', array($this, 'addAssets'), 10, 3);
	}

	public function addToCart($value, $product)
	{
		if ($product instanceof \Jigoshop\Entity\Product\Simple) {
			$item = new Item();
			$item->setName($product->getName());
			$item->setPrice($product->getPrice());
			$item->setQuantity(1);
			$item->setProduct($product);
			// TODO: Set tax

			return $item;
		}

		return $value;
	}

	/**
	 * @param Wordpress $wp
	 * @param Styles $styles
	 * @param Scripts $scripts
	 */
	public function addAssets(Wordpress $wp, Styles $styles, Scripts $scripts)
	{
		$scripts->add('jigoshop.admin.product.simple', JIGOSHOP_URL.'/assets/js/admin/product/simple.js', array('jquery'));
		$scripts->localize('jigoshop.admin.product.simple', 'jigoshop_admin_product_simple', array(
			'ajax' => $wp->getAjaxUrl(),
		));
	}
}
