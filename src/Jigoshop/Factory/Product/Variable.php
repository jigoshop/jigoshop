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
			SELECT * FROM {$wpdb->prefix}jigoshop_product_variation pv
				LEFT JOIN {$wpdb->prefix}jigoshop_product_variation_attribute pva ON pv.id = pva.variation_id
				WHERE pv.parent_id = %d AND pv.id = %d
		", array($product->getId(), $variationId));
		$results = $wpdb->get_results($query, ARRAY_A);

		$i = 0;
		$endI = count($results);
		$variation = new VariableProduct\Variation();
		$variation->setId((int)$results[$i]['id']);
		$variation->setParent($product);
		$variation->setProduct($this->productService->find($results[$i]['product_id'])); // TODO: Maybe some kind of fetching together?

		while ($i < $endI && $results[$i]['id'] == $variation->getId()) {
			if ($results[$i]['attribute_id'] !== null) {
				$attribute = new VariableProduct\Attribute(VariableProduct\Attribute::VARIATION_ATTRIBUTE_EXISTS);
				$attribute->setVariation($variation);
				$attribute->setAttribute($product->getAttribute($results[$i]['attribute_id']));
				$attribute->setValue($results[$i]['value']);
				$variation->addAttribute($attribute);
			}

			$i++;
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
			SELECT * FROM {$wpdb->prefix}jigoshop_product_variation pv
				LEFT JOIN {$wpdb->prefix}jigoshop_product_variation_attribute pva ON pv.id = pva.variation_id
				WHERE pv.parent_id = %d
		", array($product->getId()));
		$results = $wpdb->get_results($query, ARRAY_A);
		$variations = array();

		for ($i = 0, $endI = count($results); $i < $endI;) {
			$variation = new VariableProduct\Variation();
			$variation->setId((int)$results[$i]['id']);
			$variation->setParent($product);
			$variation->setProduct($this->productService->find($results[$i]['product_id'])); // TODO: Maybe some kind of fetching together?

			while ($i < $endI && $results[$i]['id'] == $variation->getId()) {
				if ($results[$i]['attribute_id'] !== null) {
					$attribute = new VariableProduct\Attribute(VariableProduct\Attribute::VARIATION_ATTRIBUTE_EXISTS);
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
