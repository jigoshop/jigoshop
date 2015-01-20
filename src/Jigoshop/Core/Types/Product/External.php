<?php

namespace Jigoshop\Core\Types\Product;

use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\External as Entity;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

class External implements Type
{
	/**
	 * Returns identifier for the type.
	 *
	 * @return string Type identifier.
	 */
	public function getId()
	{
		return Entity::TYPE;
	}

	/**
	 * Returns human-readable name for the type.
	 *
	 * @return string Type name.
	 */
	public function getName()
	{
		return __('External / Affiliate', 'jigoshop');
	}

	/**
	 * Returns class name to use as type entity.
	 * This class MUST extend {@code \Jigoshop\Entity\Product}!
	 *
	 * @return string Fully qualified class name.
	 */
	public function getClass()
	{
		return '\Jigoshop\Entity\Product\External';
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
//		$wp->addFilter('jigoshop\core\types\variable\subtypes', array($this, 'addVariableSubtype'), 10, 1); // TODO: Enable variable subtypes changing

		$wp->addAction('jigoshop\admin\product\assets', array($this, 'addAssets'), 10, 0);
		$wp->addFilter('jigoshop\admin\product\menu', array($this, 'addProductMenu'));
		$wp->addFilter('jigoshop\product\tabs\general', array($this, 'addToGeneralTab'), 10, 1);
		$wp->addAction('jigoshop\admin\variation', array($this, 'addVariationFields'), 10, 2);
	}

	/**
	 * Renders additional fields for variations.
	 *
	 * @param $variation Product\Variable\Variation
	 * @param $product Product\Variable
	 */
	public function addVariationFields($variation, $product)
	{
		Render::output('admin/product/box/variations/variation/external', array(
			'variation' => $variation,
			'product' => $variation->getProduct(),
			'parent' => $product,
		));
	}

	/**
	 * Adds downloadable as proper subtype for variations.
	 *
	 * @param $subtypes array Current list of subtypes.
	 * @return array Updated list of subtypes.
	 */
	public function addVariableSubtype($subtypes) {
		$subtypes[] = Entity::TYPE;
		return $subtypes;
	}

	/**
	 * @param $value
	 * @param $product
	 * @return null
	 */
	public function addToCart($value, $product)
	{
		if ($product instanceof Entity) {
			return null;
		}

		return $value;
	}

	public function addAssets()
	{
		Scripts::add('jigoshop.admin.product.external', JIGOSHOP_URL.'/assets/js/admin/product/external.js', array(
			'jquery',
			'jigoshop.helpers'
		));
	}

	/**
	 * Updates product menu.
	 *
	 * @param $menu array
	 * @return array
	 */
	public function addProductMenu($menu)
	{
		$menu['sales']['visible'][] = Product\External::TYPE;
		return $menu;
	}

	/**
	 * Updates product tab with external URL field.
	 *
	 * @param $product Product
	 */
	public function addToGeneralTab($product)
	{
		Render::output('admin/product/box/general/external', array(
			'product' => $product,
		));
	}
}
