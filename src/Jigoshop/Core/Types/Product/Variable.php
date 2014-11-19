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

		$this->_createTables($wp);
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

	private function _createTables(Wordpress $wp)
	{
		$wpdb = $wp->getWPDB();
		$wpdb->hide_errors();

		$collate = '';
		if ($wpdb->has_cap('collation')) {
			if (!empty($wpdb->charset)) {
				$collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}
			if (!empty($wpdb->collate)) {
				$collate .= " COLLATE {$wpdb->collate}";
			}
		}

		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_product_variation (
				id INT(9) NOT NULL AUTO_INCREMENT,
				product_id BIGINT UNSIGNED NOT NULL,
				PRIMARY KEY id (id),
				FOREIGN KEY product (product_id) REFERENCES {$wpdb->posts} (ID) ON DELETE CASCADE
			) {$collate};
		";
		$wpdb->query($query);
		$query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_product_variation_attribute (
				variation_id INT(9) NOT NULL,
				attribute_id INT(9) NOT NULL,
				value VARCHAR(255),
				PRIMARY KEY id (variation_id, attribute_id),
				FOREIGN KEY variation (variation_id) REFERENCES {$wpdb->prefix}jigoshop_product_variation (id) ON DELETE CASCADE,
				FOREIGN KEY attribute (attribute_id) REFERENCES {$wpdb->prefix}jigoshop_attribute (id) ON DELETE CASCADE
			) {$collate};
		";
		$wpdb->query($query);
//		var_dump($wpdb->query($query), $wpdb->last_error); exit;
		$wpdb->show_errors();
	}
}
