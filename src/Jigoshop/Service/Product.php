<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\EntityInterface;

/**
 * Product service.
 *
 * @package Jigoshop\Service
 * @author Jigoshop
 */
class Product implements ServiceInterface
{
	/**
	 * Finds product specified by ID.
	 *
	 * @param $id int Product ID.
	 * @return \Jigoshop\Entity\Product
	 */
	public function find($id)
	{
		$product = new \Jigoshop\Entity\Product();

		if($id !== null)
		{
			$post = get_post($id);
			$meta = get_post_meta($id);

			$product->setId($id);
			$product->setType($meta['type'][0]);
			$product->setName($post->post_title);
			$product->setPrice(floatval($meta['price'][0]));
			$product->setRegularPrice(floatval($meta['regular_price'][0]));
			$product->setVisibility(intval($meta['visibility'][0]));
			$product->setSales($meta['sales'][0]);
			$product->setSize($meta['size'][0]);
			$product->setStock($meta['stock'][0]);

			$product = apply_filters('jigoshop\\find\\product', $product, $meta);
		}

		return $product;
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery(\WP_Query $query)
	{
		// TODO: Implement findByQuery() method.
	}

	/**
	 * Saves product to database.
	 *
	 * @param \Jigoshop\Entity\EntityInterface $object Product to save.
	 * @throws Exception
	 */
	public function save(EntityInterface $object)
	{
		if(!($object instanceof \Jigoshop\Entity\Product))
		{
			throw new Exception('Trying to save not a product!');
		}

		$fields = $object->getDirtyFields();

		if(in_array('id', $fields) || in_array('name', $fields))
		{
			wp_update_post(array(
				'ID' => $object->getId(),
				'post_title' => $object->getName(),
			));
			unset($fields[array_search('id', $fields)], $fields[array_search('name', $fields)]);
		}

		foreach($fields as $field)
		{
			update_post_meta($object->getId(), $field, $object->get($field));
		}
	}
}