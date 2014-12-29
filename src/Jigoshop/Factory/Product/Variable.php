<?php

namespace Jigoshop\Factory\Product;

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Variable as VariableProduct;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Variable
{
	/** @var Wordpress */
	private $wp;
	/** @var ProductServiceInterface */
	private $productService;

	public function __construct(Wordpress $wp, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->productService = $productService;
		$wp->addFilter('jigoshop\find\product', array($this, 'fetch'));
		$wp->addAction('jigoshop\admin\product_attribute\add', array($this, 'addAttributes'), 10, 2);
	}

	/**
	 * Creates variation for selected parent.
	 *
	 * @param VariableProduct $product Parent product.
	 * @return VariableProduct\Variation
	 */
	public function createVariation($product)
	{
		$variation = new VariableProduct\Variation();
		$variation->setParent($product);

		foreach ($product->getVariableAttributes() as $attribute) {
			$variationAttribute = new VariableProduct\Attribute();
			$variationAttribute->setAttribute($attribute);
			$variationAttribute->setVariation($variation);
			$variation->addAttribute($variationAttribute);
		}

		return $variation;
	}

	/**
	 * Adds variations for variable products.
	 * Extends default factory for products.
	 *
	 * @param Product $product
	 * @return Product
	 */
	public function fetch($product)
	{
		if ($product instanceof VariableProduct) {
			foreach ($this->getVariations($product) as $variation) {
				$product->addVariation($variation);
			}
		}

		return $product;
	}

	/**
	 * Finds and fetches variation for selected product and variation ID.
	 *
	 * @param Product\Variable $product Parent product.
	 * @param int $variationId Variation ID.
	 * @return Product\Variable\Variation The variation.
	 */
	public function getVariation(Product\Variable $product, $variationId)
	{
		$wpdb = $this->wp->getWPDB();
		$query = $wpdb->prepare("
			SELECT * FROM {$wpdb->prefix}jigoshop_product_variation_attribute pva
				WHERE pva.variation_id = %d
		", array($variationId));
		$results = $wpdb->get_results($query, ARRAY_A);

		$variation = new VariableProduct\Variation();
		$variation->setId($variationId);
		$variation->setParent($product);
		$variation->setProduct($this->productService->find($variationId));

		$results = array_filter($results, function($item){
			return $item['attribute_id'] !== null;
		});

		foreach ($results as $source) {
			$attribute = new VariableProduct\Attribute(VariableProduct\Attribute::VARIATION_ATTRIBUTE_EXISTS);
			$attribute->setAttribute($product->getAttribute($source['attribute_id']));
			$attribute->setValue($source['value']);
			$variation->addAttribute($attribute);
		}

		return $variation;
	}

	/**
	 * @param $product VariableProduct Product to fetch variations for.
	 * @return array List of variations.
	 */
	public function getVariations($product)
	{
		$wpdb = $this->wp->getWPDB();
		$query = $wpdb->prepare("
			SELECT pv.ID, pva.* FROM {$wpdb->posts} pv
				LEFT JOIN {$wpdb->prefix}jigoshop_product_variation_attribute pva ON pv.ID = pva.variation_id
				WHERE pv.post_parent = %d AND pv.post_type = %s
		", array($product->getId(), \Jigoshop\Core\Types\Product\Variable::TYPE));
		$results = $wpdb->get_results($query, ARRAY_A);
//		echo '<pre>'; var_dump($results); exit;
		$variations = array();

		$results = array_filter($results, function($item){
			return $item['attribute_id'] !== null;
		});

		for ($i = 0, $endI = count($results); $i < $endI;) {
			$variation = new VariableProduct\Variation();
			$variation->setId((int)$results[$i]['ID']);
			$variation->setParent($product);
			$variation->setProduct($this->productService->find($results[$i]['ID'])); // TODO: Maybe some kind of fetching together?

			while ($i < $endI && $results[$i]['ID'] == $variation->getId()) {
				$attribute = new VariableProduct\Attribute(VariableProduct\Attribute::VARIATION_ATTRIBUTE_EXISTS);
				$attribute->setVariation($variation);
				$attribute->setAttribute($product->getAttribute($results[$i]['attribute_id']));
				$attribute->setValue($results[$i]['value']);
				$variation->addAttribute($attribute);
				$i++;
			}

			$variations[$variation->getId()] = $variation;
		}

		return $variations;
	}

	/**
	 * @param Product\Attribute $attribute
	 * @param Product $product
	 */
	public function addAttributes($attribute, $product)
	{
		if ($attribute instanceof Product\Attribute\Variable && $product instanceof Product\Variable) {
			/** @var $attribute Product\Attribute|Product\Attribute\Variable */
			/** @var $product Product|Product\Variable */
			if (isset($_POST['options']) && isset($_POST['options']['is_variable'])) {
				$attribute->setVariable($_POST['options']['is_variable'] === 'true');
			}

			if ($attribute->isVariable()) {
				foreach ($product->getVariations() as $variation) {
					/** @var $variation VariableProduct\Variation */
					if (!$variation->hasAttribute($attribute->getId())) {
						$variableAttribute = new VariableProduct\Attribute();
						$variableAttribute->setAttribute($attribute);
						$variableAttribute->setVariation($variation);
						$variation->addAttribute($variableAttribute);
					}
				}
			}
		}
	}
}
