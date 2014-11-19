<?php

namespace Jigoshop\Core\Types\Product;

use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Core\Options;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Entity\Product\Variable\Attribute as VariableAttribute;
use Jigoshop\Entity\Product\Variable\Variation;
use Jigoshop\Exception;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Variable implements Type
{
	/** @var Wordpress */
	private $wp;
	/** @var ProductServiceInterface */
	private $productService;

	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->productService = $productService;
	}

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
		$wp->addAction('jigoshop\service\product\save', array($this, 'save'));
		$wp->addFilter('jigoshop\find\product', array($this, 'fetch'));

		$wp->addAction('jigoshop\admin\product_attribute\add', array($this, 'addAttributes'));
		$wp->addAction('jigoshop\admin\product\assets', array($this, 'addAssets'), 10, 3);
		$wp->addAction('jigoshop\admin\product\attribute\options', array($this, 'addVariableAttributeOptions'));
		$wp->addFilter('jigoshop\admin\product\menu', array($this, 'addProductMenu'));

		$wp->addAction('wp_ajax_jigoshop.admin.product.add_variation', array($this, 'ajaxAddVariation'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.product.save_variation', array($this, 'ajaxSaveVariation'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.product.remove_variation', array($this, 'ajaxRemoveVariation'), 10, 0);

		$this->_createTables($wp);
	}

	public function fetch($product)
	{
		if ($product instanceof \Jigoshop\Entity\Product\Variable) {
			foreach ($this->getVariations($product) as $variation) {
				$product->addVariation($variation);
			}
		}

		return $product;
	}

	public function ajaxAddVariation()
	{
		try {
			if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
				throw new Exception(__('Product was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop'));
			}

			if (!($product instanceof \Jigoshop\Entity\Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop'));
			}

			$variation = new Variation();
			$variation->setProduct($product);

			foreach ($product->getVariableAttributes() as $attribute) {
				$variationAttribute = new VariableAttribute();
				$variationAttribute->setAttribute($attribute);
				$variationAttribute->setVariation($variation);
				$variation->addAttribute($variationAttribute);
			}

			$this->wp->doAction('jigoshop\admin\product_variation\add', $variation);

			$product->addVariation($variation);
			$this->productService->save($product);

			echo json_encode(array(
				'success' => true,
				'html' => Render::get('admin/product/box/variations/variation', array(
					'variation' => $variation,
					'attributes' => $product->getVariableAttributes(),
				)),
			));
		} catch(Exception $e) {
			echo json_encode(array(
				'success' => false,
				'error' => $e->getMessage(),
			));
		}

		exit;
	}

	public function ajaxSaveVariation()
	{
		try {
			if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
				throw new Exception(__('Product was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop'));
			}
			if (!isset($_POST['variation_id']) || empty($_POST['variation_id'])) {
				throw new Exception(__('Variation was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['variation_id'])) {
				throw new Exception(__('Invalid variation ID.', 'jigoshop'));
			}

			if (!isset($_POST['attributes']) || is_array($_POST['attributes'])) {
				throw new Exception(__('Attribute values are not specified.', 'jigoshop'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop'));
			}

			if (!($product instanceof \Jigoshop\Entity\Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop'));
			}

			if (!$product->hasVariation((int)$_POST['variation_id'])) {
				throw new Exception(__('Variation does not exists.', 'jigoshop'));
			}

			$variation = $product->removeVariation((int)$_POST['variation_id']);
			foreach ($_POST['attributes'] as $attribute => $value) {
				$variation->getAttribute($attribute)->setValue(trim(htmlspecialchars(strip_tags($value))));
			}

			$this->wp->doAction('jigoshop\admin\product_variation\save', $variation);

			$product->addVariation($variation);
			$this->productService->save($product);

			echo json_encode(array(
				'success' => true,
				'html' => Render::get('admin/product/box/variations/variation', array(
					'variation' => $variation,
					'attributes' => $product->getVariableAttributes(),
				)),
			));
		} catch(Exception $e) {
			echo json_encode(array(
				'success' => false,
				'error' => $e->getMessage(),
			));
		}

		exit;
	}

	public function ajaxRemoveVariation()
	{
		try {
			if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
				throw new Exception(__('Product was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['product_id'])) {
				throw new Exception(__('Invalid product ID.', 'jigoshop'));
			}
			if (!isset($_POST['variation_id']) || empty($_POST['variation_id'])) {
				throw new Exception(__('Variation was not specified.', 'jigoshop'));
			}
			if (!is_numeric($_POST['variation_id'])) {
				throw new Exception(__('Invalid variation ID.', 'jigoshop'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop'));
			}

			$product->removeAttribute((int)$_POST['variation_id']);
			$this->productService->save($product);
			echo json_encode(array(
				'success' => true,
			));
		} catch(Exception $e) {
			echo json_encode(array(
				'success' => false,
				'error' => $e->getMessage(),
			));
		}

		exit;
	}

	public function save(EntityInterface $object)
	{
		if ($object instanceof \Jigoshop\Entity\Product\Variable) {
			$wpdb = $this->wp->getWPDB();

			foreach ($object->getVariations() as $variation) {
				/** @var Variation $variation */
				$data = array(
					'product_id' => $variation->getProduct()->getId(),
				);

				if ($variation->getId()) {
					$wpdb->update($wpdb->prefix.'jigoshop_product_variation', $data, array('id' => $variation->getId()));
				} else {
					$wpdb->insert($wpdb->prefix.'jigoshop_product_variation', $data);
					$variation->setId($wpdb->insert_id);
				}

				foreach ($variation->getAttributes() as $attribute) {
					/** @var \Jigoshop\Entity\Product\Variable\Attribute $attribute */
					$data = array(
						'variation_id' => $variation->getId(),
						'attribute_id' => $attribute->getAttribute()->getId(),
						'value' => $attribute->getValue(),
					);

					if ($attribute->exists()) {
						$wpdb->update($wpdb->prefix.'jigoshop_product_variation_attribute', $data, array(
							'variation_id' => $variation->getId(),
							'attribute_id' => $attribute->getAttribute()->getId(),
						));
					} else {
						$wpdb->insert($wpdb->prefix.'jigoshop_product_variation_attribute', $data);
						// TODO: Set attribute to EXISTS
					}
				}
			}
		}
	}

	/**
	 * Adds variable options to attribute field.
	 *
	 * @param Attribute $attribute Attribute.
	 */
	public function addVariableAttributeOptions(Attribute $attribute)
	{
		if ($attribute instanceof Attribute\Variable) {
			Forms::checkbox(array(
				'name' => 'product[attributes]['.$attribute->getId().'][is_variable]',
				'id' => 'product_attributes_'.$attribute->getId().'_variable',
				'classes' => array('attribute-options'),
				'label' => __('Is for variations?', 'jigoshop'),
				'checked' => $attribute->isVariable(),
				'size' => 6,
				// TODO: Visibility based on current product - if not variable should be hidden
			));
		}
	}

	/**
	 * Updates product menu.
	 *
	 * @param $menu array
	 * @return array
	 */
	public function addProductMenu($menu)
	{
		$menu['variations'] = array('label' => __('Variations', 'jigoshop'), 'visible' => array(\Jigoshop\Entity\Product\Variable::TYPE));
		$menu['sales']['visible'][] = \Jigoshop\Entity\Product\Variable::TYPE;
		return $menu;
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

	/**
	 * @param $product \Jigoshop\Entity\Product\Variable Product to fetch variations for.
	 * @return array List of variations.
	 */
	private function getVariations($product)
	{
		$wpdb = $this->wp->getWPDB();
		$query = $wpdb->prepare("
			SELECT * FROM {$wpdb->prefix}jigoshop_product_variation pv
				LEFT JOIN {$wpdb->prefix}jigoshop_product_variation_attribute pva ON pv.id = pva.variation_id
				WHERE pv.product_id = %d
		", array($product->getId()));
		$results = $wpdb->get_results($query, ARRAY_A);
		$variations = array();

		for ($i = 0, $endI = count($results); $i < $endI;) {
			$variation = new Variation();
			$variation->setId((int)$results[$i]['id']);
			$variation->setProduct($product);

			while ($i < $endI && $results[$i]['id'] == $variation->getId()) {
				if ($results[$i]['attribute_id'] !== null) {
					$attribute = new VariableAttribute(VariableAttribute::VARIATION_ATTRIBUTE_EXISTS);
					$attribute->setVariation($variation);
					$attribute->setAttribute($product->getAttribute($results[$i]['attribute_id']));
					$attribute->setValue($results[$i]['value']);
					$variation->addAttribute($attribute);
				}

				$i++;
			}

			$variations[$variation->getId()] = $variation;
		}

		return $variations;
	}
}
