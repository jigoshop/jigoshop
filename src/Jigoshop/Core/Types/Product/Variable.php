<?php

namespace Jigoshop\Core\Types\Product;

use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Core\Options;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Entity\Product\Variable\Attribute as VariableAttribute;
use Jigoshop\Entity\Product\Variable\Variation;
use Jigoshop\Exception;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

/**
 * Variable product type definition.
 *
 * TODO: Extract Service\Product\Variable
 * TODO: Extract Factory\Product\Variable
 *
 * @package Jigoshop\Core\Types\Product
 */
class Variable implements Type
{
	const TYPE = 'product_variation';

	/** @var Wordpress */
	private $wp;
	/** @var ProductServiceInterface */
	private $productService;
	/** @var array */
	private $allowedSubtypes = array();

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
		return Product\Variable::TYPE;
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
	 * @return array
	 */
	public function getAllowedSubtypes()
	{
		return $this->allowedSubtypes;
	}

	/**
	 * Initializes product type.
	 *
	 * @param Wordpress $wp WordPress Abstraction Layer
	 * @param array $enabledTypes List of all available types.
	 */
	public function initialize(Wordpress $wp, array $enabledTypes)
	{
		$wp->addAction('jigoshop\service\product\save', array($this, 'save'));
		$wp->addFilter('jigoshop\find\product', array($this, 'fetch'));

		$wp->addAction('jigoshop\admin\product_attribute\add', array($this, 'addAttributes'), 10, 2);
		$wp->addAction('jigoshop\admin\product\assets', array($this, 'addAssets'), 10, 3);
		$wp->addAction('jigoshop\admin\product\attribute\options', array($this, 'addVariableAttributeOptions'));
		$wp->addFilter('jigoshop\admin\product\menu', array($this, 'addProductMenu'));
		$wp->addFilter('jigoshop\admin\product\tabs', array($this, 'addProductTab'), 10, 2);

		$wp->addAction('wp_ajax_jigoshop.admin.product.add_variation', array($this, 'ajaxAddVariation'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.product.save_variation', array($this, 'ajaxSaveVariation'), 10, 0);
		$wp->addAction('wp_ajax_jigoshop.admin.product.remove_variation', array($this, 'ajaxRemoveVariation'), 10, 0);

		$allowedSubtypes = $wp->applyFilters('jigoshop\core\types\variable\subtypes', array(
			Product\Simple::TYPE,
		));
		$this->allowedSubtypes = array_filter($enabledTypes, function($type) use ($allowedSubtypes){
			/** @var $type Type */
			return in_array($type->getId(), $allowedSubtypes);
		});

		// TODO: Move this to Installer class (somehow).
		$this->createTables();
	}

	public function fetch($product)
	{
		if ($product instanceof Product\Variable) {
			foreach ($this->getVariations($product) as $variation) {
				$product->addVariation($variation);
			}
		}

		return $product;
	}

	public function save(EntityInterface $object)
	{
		if ($object instanceof Product\Variable) {
			$wpdb = $this->wp->getWPDB();
			$this->removeAllVariationsExcept($object->getId(), array_map(function($item){
				/** @var Variation $item */
				return $item->getId();
			}, $object->getVariations()));

			foreach ($object->getVariations() as $variation) {
				/** @var Variation $variation */
				$data = array(
					'parent_id' => $variation->getParent()->getId(),
					'product_id' => $variation->getProduct()->getId(),
				);

				if ($variation->getId()) {
					$wpdb->update($wpdb->prefix.'jigoshop_product_variation', $data, array('id' => $variation->getId()));
				} else {
					$wpdb->insert($wpdb->prefix.'jigoshop_product_variation', $data);
					$variation->setId($wpdb->insert_id);
				}

				foreach ($variation->getAttributes() as $attribute) {
					/** @var VariableAttribute $attribute */
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
	 * @param $productId int ID of parent product.
	 * @param $ids array IDs to preserve.
	 */
	private function removeAllVariationsExcept($productId, $ids)
	{
		$wpdb = $this->wp->getWPDB();
		$ids = join(',', array_filter(array_map(function($item){ return (int)$item; }, $ids)));
		// Support for removing all items
		if (empty($ids)) {
			$ids = '0';
		}
		$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}jigoshop_product_variation WHERE id NOT IN ({$ids}) AND product_id = %d", array($productId));
		$wpdb->query($query);
	}

	/**
	 * Adds variable options to attribute field.
	 *
	 * @param Attribute|Attribute\Variable $attribute Attribute.
	 */
	public function addVariableAttributeOptions(Attribute $attribute)
	{
		if ($attribute instanceof Attribute\Variable) {
			/** @var $attribute Attribute|Attribute\Variable */
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
		$menu['variations'] = array('label' => __('Variations', 'jigoshop'), 'visible' => array(Product\Variable::TYPE));
		$menu['sales']['visible'][] = Product\Variable::TYPE;
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
		$types = array();
		foreach ($this->allowedSubtypes as $type) {
			/** @var $type Type */
			$types[$type->getId()] = $type->getName();
		}

		$tabs['variations'] = array(
			'product' => $product,
			'allowedSubtypes' => $types,
		);
		return $tabs;
	}

	/**
	 * @param Attribute $attribute
	 * @param Product $product
	 */
	public function addAttributes($attribute, $product)
	{
		if ($attribute instanceof Attribute\Variable && $product instanceof Product\Variable) {
			/** @var $attribute Attribute|Attribute\Variable */
			/** @var $product Product|Product\Variable */
			if (isset($_POST['options']) && isset($_POST['options']['is_variable'])) {
				$attribute->setVariable($_POST['options']['is_variable'] === 'true');
			}

			if ($attribute->isVariable()) {
				foreach ($product->getVariations() as $variation) {
					/** @var $variation Variation */
					if (!$variation->hasAttribute($attribute->getId())) {
						$variableAttribute = new VariableAttribute();
						$variableAttribute->setAttribute($attribute);
						$variableAttribute->setVariation($variation);
						$variation->addAttribute($variableAttribute);
					}
				}
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
			'i18n' => array(
				'confirm_remove' => __('Are you sure?', 'jigoshop'),
				'variation_removed' => __('Variation successfully removed.', 'jigoshop'),
				'saved' => __('Variation saved.', 'jigoshop'),
			),
		));
	}

	private function createTables()
	{
		$wpdb = $this->wp->getWPDB();
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
				parent_id BIGINT UNSIGNED NOT NULL,
				product_id BIGINT UNSIGNED NOT NULL,
				PRIMARY KEY id (id),
				FOREIGN KEY parent (parent_id) REFERENCES {$wpdb->posts} (ID) ON DELETE CASCADE,
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
		$wpdb->show_errors();
	}

	/**
	 * @param $product Product\Variable Product to fetch variations for.
	 * @return array List of variations.
	 */
	private function getVariations($product)
	{
		$wpdb = $this->wp->getWPDB();
		$query = $wpdb->prepare("
			SELECT * FROM {$wpdb->prefix}jigoshop_product_variation pv
				LEFT JOIN {$wpdb->prefix}jigoshop_product_variation_attribute pva ON pv.id = pva.variation_id
				WHERE pv.parent_id = %d
		", array($product->getId()));
		$results = $wpdb->get_results($query, ARRAY_A);
		$variations = array();

		for ($i = 0, $endI = count($results); $i < $endI;) {
			$variation = new Variation();
			$variation->setId((int)$results[$i]['id']);
			$variation->setParent($product);
			$variation->setProduct($this->productService->find($results[$i]['product_id'])); // TODO: Maybe some kind of fetching together?

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

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop'));
			}

			$variation = new Variation();
			$variation->setParent($product);

			foreach ($product->getVariableAttributes() as $attribute) {
				$variationAttribute = new VariableAttribute();
				$variationAttribute->setAttribute($attribute);
				$variationAttribute->setVariation($variation);
				$variation->addAttribute($variationAttribute);
			}

			$variableId = $this->createVariablePost($variation);
			$variableProduct = $this->productService->find($variableId);
			$variableProduct->setVisibility(Product::VISIBILITY_NONE);
			$variableProduct->setTaxable($product->isTaxable());
			$variableProduct->setTaxClasses($product->getTaxClasses());
			$this->productService->save($variableProduct);
			$variation->setProduct($variableProduct);

			$this->wp->doAction('jigoshop\admin\product_variation\add', $variation);

			$product->addVariation($variation);
			$this->productService->save($product);

			echo json_encode(array(
				'success' => true,
				'html' => Render::get('admin/product/box/variations/variation', array(
					'variation' => $variation,
					'attributes' => $product->getVariableAttributes(),
					'allowedSubtypes' => $this->allowedSubtypes,
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

			if (!isset($_POST['attributes']) || !is_array($_POST['attributes'])) {
				throw new Exception(__('Attribute values are not specified.', 'jigoshop'));
			}

			$product = $this->productService->find((int)$_POST['product_id']);

			if (!$product->getId()) {
				throw new Exception(__('Product does not exists.', 'jigoshop'));
			}

			if (!($product instanceof Product\Variable)) {
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
					'allowedSubtypes' => $this->allowedSubtypes,
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

			if (!($product instanceof Product\Variable)) {
				throw new Exception(__('Product is not variable - unable to add variation.', 'jigoshop'));
			}

			$product->removeVariation((int)$_POST['variation_id']);
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

	/**
	 * @param $variation Variation
	 * @return int
	 */
	private function createVariablePost($variation)
	{
		$wpdb = $this->wp->getWPDB();
		$wpdb->insert($wpdb->posts, array(
			'post_title' => $variation->getTitle(),
			'post_type' => self::TYPE,
			'post_parent' => $variation->getParent()->getId(),
			'comment_status' => 'closed',
			'ping_status' => 'closed',
		));

		return $wpdb->insert_id;
	}
}
