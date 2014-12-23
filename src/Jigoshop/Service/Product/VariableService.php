<?php

namespace Jigoshop\Service\Product;

use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Product;
use Jigoshop\Factory\Product\Variable as Factory;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class VariableService implements VariableServiceInterface
{
	/** @var Wordpress */
	private $wp;
	/** @var Factory */
	private $factory;
	/** @var ProductServiceInterface */
	private $productService;

	public function __construct(Wordpress $wp, Factory $factory, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->factory = $factory;
		$this->productService = $productService;
		$wp->addAction('jigoshop\service\product\save', array($this, 'save'));
	}

	/**
	 * Finds and fetches variation for selected product and variation ID.
	 *
	 * @param Product\Variable $product Parent product.
	 * @param int $variationId Variation ID.
	 * @return Product\Variable\Variation The variation.
	 */
	public function find(Product\Variable $product, $variationId)
	{
		return $this->factory->getVariation($product, $variationId);
	}

	public function save(EntityInterface $object)
	{
		if ($object instanceof Product\Variable) {
			$wpdb = $this->wp->getWPDB();
			$this->removeAllVariationsExcept($object->getId(), array_map(function($item){
				/** @var Product\Variable\Variation $item */
				return $item->getId();
			}, $object->getVariations()));

			foreach ($object->getVariations() as $variation) {
				/** @var Product\Variable\Variation $variation */
				if ($variation->getProduct() === null) {
					$variation->setProduct($this->_createVariableProduct($variation, $object));
					$variation->setId($variation->getProduct()->getId());
				}

				$this->productService->save($variation->getProduct());

				foreach ($variation->getAttributes() as $attribute) {
					/** @var Product\Variable\Attribute $attribute */
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
						$attribute->setExists(Product\Variable\Attribute::VARIATION_ATTRIBUTE_EXISTS);
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
	 * @param $variation Product\Variable\Variation
	 * @param $product Product\Variable
	 * @return Product
	 */
	private function _createVariableProduct($variation, $product)
	{
		$variableId = $this->createVariablePost($variation);
		/** @var Product|Product\Purchasable $variableProduct */
		$variableProduct = $this->productService->find($variableId);
		$variableProduct->setVisibility(Product::VISIBILITY_NONE);
		$variableProduct->setTaxable($product->isTaxable());
		$variableProduct->setTaxClasses($product->getTaxClasses());
		$variableProduct->getStock()->setManage(true);

		return $variableProduct;
	}

	/**
	 * @param $variation Product\Variable\Variation
	 * @return int
	 */
	private function createVariablePost($variation)
	{
		$wpdb = $this->wp->getWPDB();
		$wpdb->insert($wpdb->posts, array(
			'post_title' => $variation->getTitle(),
			'post_type' => \Jigoshop\Core\Types\Product\Variable::TYPE,
			'post_parent' => $variation->getParent()->getId(),
			'comment_status' => 'closed',
			'ping_status' => 'closed',
		));

		return $wpdb->insert_id;
	}

	public function removeVariation($variation)
	{
		$this->removeVariablePost($variation->getProduct());
	}

	/**
	 * @param $product Product
	 * @return int
	 */
	private function removeVariablePost($product)
	{
		$wpdb = $this->wp->getWPDB();
		$wpdb->delete($wpdb->posts, array(
			'ID' => $product->getId(),
		));
	}
}
