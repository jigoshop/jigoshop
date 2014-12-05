<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Factory\Coupon as Factory;
use WPAL\Wordpress;

/**
 * Coupon service.
 *
 * TODO: Add caching.
 *
 * @package Jigoshop\Service
 */
class Coupon implements ServiceInterface
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Factory */
	private $factory;

	public function __construct(Wordpress $wp, Options $options, Factory $factory)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->factory = $factory;
		$wp->addAction('save_post_'.Types\Coupon::NAME, array($this, 'savePost'), 10);
	}

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return EntityInterface
	 */
	public function find($id)
	{
		$post = null;

		if ($id !== null) {
			$post = $this->wp->getPost($id);
		}

		return $this->factory->fetch($post);
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return EntityInterface Item found.
	 */
	public function findForPost($post)
	{
		return $this->factory->fetch($post);
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery($query)
	{
		$results = $query->get_posts();
		$coupons = array();

		// TODO: Maybe it is good to optimize this to fetch all found products data at once?
		foreach ($results as $coupon) {
			$coupons[] = $this->findForPost($coupon);
		}

		return $coupons;
	}

	/**
	 * Saves entity to database.
	 *
	 * @param $object EntityInterface Entity to save.
	 */
	public function save(EntityInterface $object)
	{
		if (!($object instanceof \Jigoshop\Entity\Coupon)) {
			throw new Exception('Trying to save not a coupon!');
		}

		// TODO: Support for transactions!

		$fields = $object->getStateToSave();

		if (isset($fields['id']) || isset($fields['title'])) {
			// We do not need to save ID and title as they are saved by WordPress itself.
			unset($fields['id'], $fields['title']);
		}

		foreach ($fields as $field => $value) {
			$this->wp->updatePostMeta($object->getId(), $field, $value);
		}

		$this->wp->doAction('jigoshop\service\coupon\save', $object);
	}

	/**
	 * Save the email data upon post saving.
	 *
	 * @param $id int Post ID.
	 */
	public function savePost($id)
	{
		$coupon = $this->factory->create($id);
		$this->save($coupon);
	}
}
