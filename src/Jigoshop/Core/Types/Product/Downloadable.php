<?php

namespace Jigoshop\Core\Types\Product;

use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Downloadable as Entity;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class Downloadable implements Type
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
		return __('Downloadable', 'jigoshop');
	}

	/**
	 * Returns class name to use as type entity.
	 * This class MUST extend {@code \Jigoshop\Entity\Product}!
	 *
	 * @return string Fully qualified class name.
	 */
	public function getClass()
	{
		return '\Jigoshop\Entity\Product\Downloadable';
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
		$wp->addFilter('jigoshop\admin\product\menu', array($this, 'addProductMenu'));
		$wp->addFilter('jigoshop\admin\product\tabs', array($this, 'addProductTab'), 10, 2);
	}

	/**
	 * @param $value
	 * @param $product
	 * @return null
	 */
	public function addToCart($value, $product)
	{
		if ($product instanceof Entity) {
			$item = new Item();
			$item->setName($product->getName());
			$item->setPrice($product->getPrice());
			$item->setQuantity(1);
			$item->setProduct($product);

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
//		$scripts->add('jigoshop.admin.product.downloadable', JIGOSHOP_URL.'/assets/js/admin/product/downloadable.js', array('jquery'));
	}

	/**
	 * Updates product menu.
	 *
	 * @param $menu array
	 * @return array
	 */
	public function addProductMenu($menu)
	{
		$menu['downloads'] = array('label' => __('Downloads', 'jigoshop'), 'visible' => array(Product\Downloadable::TYPE));
		$menu['advanced']['visible'][] = Product\Downloadable::TYPE;
		$menu['stock']['visible'][] = Product\Downloadable::TYPE;
		$menu['sales']['visible'][] = Product\Downloadable::TYPE;
		return $menu;
	}

	/**
	 * Updates product tabs.
	 *
	 * @param $tabs array
	 * @param $product Product
	 * @return array
	 */
	public function addProductTab($tabs, $product)
	{
		$tabs['downloads'] = array(
			'product' => $product,
		);
		return $tabs;
	}
}
