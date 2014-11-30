<?php
namespace Jigoshop\Service\Product;

use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Product;

interface VariableServiceInterface
{
	/**
	 * Finds and fetches variation for selected product and variation ID.
	 *
	 * @param Product\Variable $product Parent product.
	 * @param int $variationId Variation ID.
	 * @return Product\Variable\Variation The variation.
	 */
	public function find(Product\Variable $product, $variationId);

	/**
	 * Saves variable product.
	 *
	 * @param EntityInterface $object The product.
	 */
	public function save(EntityInterface $object);

	/**
	 * Removes variation from database.
	 *
	 * @param $variation Product\Variable\Variation Variation to remove.
	 */
	public function removeVariation($variation);
}
