<?php

namespace Jigoshop\Service\Cache\Product\Variable;

use Jigoshop\Entity\EntityInterface;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Service\Product\VariableServiceInterface;

/**
 * Simple cache class for Jigoshop variable products service.
 *
 * @package Jigoshop\Service\Cache\Product\Variable
 */
class Simple implements VariableServiceInterface
{
	private $objects = array();

	/** @var VariableServiceInterface */
	private $service;

	public function __construct(VariableServiceInterface $service)
	{
		$this->service = $service;
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
		if (!isset($this->objects[$product->getId()])) {
			$this->objects[$product->getId()] = $this->service->find($product, $variationId);
		}

		return $this->objects[$product->getId()];
	}

	/**
	 * Saves entity to database.
	 *
	 * @param $object EntityInterface Entity to save.
	 */
	public function save(EntityInterface $object)
	{
		$this->objects[$object->getId()] = $object;
		$this->service->save($object);
	}

	/**
	 * Removes variation from database.
	 *
	 * @param $variation Product\Variable\Variation Variation to remove.
	 */
	public function removeVariation($variation)
	{
		if ($variation) {
			unset($this->objects[$variation->getId()]);
			$this->service->removeVariation($variation);
		}
	}
}
