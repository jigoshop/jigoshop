<?php
namespace Jigoshop\Factory;

interface EntityFactoryInterface
{
	/**
	 * Creates new product properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return \Jigoshop\Entity\Product
	 */
	public function create($id);

	/**
	 * Fetches product from database.
	 *
	 * @param $post \WP_Post Post to fetch product for.
	 * @return \Jigoshop\Entity\Product
	 */
	public function fetch($post);
}
