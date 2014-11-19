<?php

namespace Jigoshop\Core\Types\Product;

use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class Variable implements Type
{
	/**
	 * Returns identifier for the type.
	 *
	 * @return string Type identifier.
	 */
	public function getId()
	{
		return \Jigoshop\Entity\Product\Variable::TYPE;
	}

	/**
	 * Returns human-readable name for the type.
	 *
	 * @return string Type name.
	 */
	public function getName()
	{
		return __('Variable', 'jigoshop');
	}

	/**
	 * Returns class name to use as type entity.
	 * This class MUST extend {@code \Jigoshop\Entity\Product}!
	 *
	 * @return string Fully qualified class name.
	 */
	public function getClass()
	{
		return '\Jigoshop\Entity\Product\Variable';
	}

	/**
	 * Initializes product type.
	 *
	 * @param Wordpress $wp WordPress Abstraction Layer
	 */
	public function initialize(Wordpress $wp)
	{
		$wp->addAction('jigoshop\admin\product_attribute\add', array($this, 'addAttributes'));
		$wp->addAction('jigoshop\admin\product\assets', array($this, 'addAssets'), 10, 3);
	}

	/**
	 * @param Attribute $attribute
	 */
	public function addAttributes($attribute)
	{
		if ($attribute instanceof Attribute\Variable) {
			if (isset($_POST['options']) && isset($_POST['options']['is_variable'])) {
				$attribute->setVariable($_POST['options']['is_variable'] === 'true');
			}
		}
	}

	/**
	 * @param Wordpress $wp
	 * @param Styles $styles
	 * @param Scripts $scripts
	 */
	public function addAssets(Wordpress $wp, Styles $styles, Scripts $scripts)
	{
		$styles->add('jigoshop.admin.product.variable', JIGOSHOP_URL.'/assets/css/admin/product/variable.css');
		$scripts->add('jigoshop.admin.product.variable', JIGOSHOP_URL.'/assets/js/admin/product/variable.js', array('jquery'));
		$scripts->localize('jigoshop.admin.product.variable', 'jigoshop_admin_product_variable', array(
			'ajax' => $wp->getAjaxUrl(),
		));
	}
}
