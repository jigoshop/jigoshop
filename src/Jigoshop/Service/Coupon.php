<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Coupon as Entity;
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
class Coupon implements CouponServiceInterface
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Factory */
	private $factory;
	/** @var array */
	private $types;

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
		if (!($object instanceof Entity)) {
			throw new Exception('Trying to save not a coupon!');
		}

		// TODO: Support for transactions!

		$fields = $object->getStateToSave();

		if (isset($fields['id']) || isset($fields['title']) || isset($fields['code'])) {
			// We do not need to save ID, title and code (post name) as they are saved by WordPress itself.
			unset($fields['id'], $fields['title'], $fields['code']);
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

	/**
	 * @return array List of available coupon types.
	 */
	public function getTypes()
	{
		if ($this->types === null) {
			$this->types = $this->wp->applyFilters('jigoshop\service\coupon\types', array(
				Entity::FIXED_CART => __('Cart Discount', 'jigoshop'),
				Entity::PERCENT_CART => __('Cart % Discount', 'jigoshop'),
				Entity::FIXED_PRODUCT => __('Product Discount', 'jigoshop'),
				Entity::PERCENT_PRODUCT => __('Product % Discount', 'jigoshop')
			));
		}

		return $this->types;
	}

	/**
	 * @param $coupon Entity
	 * @return string Type name.
	 */
	public function getType($coupon)
	{
		$types = $this->getTypes();
		if (!isset($types[$coupon->getType()])) {
			return '';
		}

		return $types[$coupon->getType()];
	}

	/**
	 * @param array $codes List of codes to find.
	 * @return array Found coupons.
	 */
	public function getByCodes(array $codes)
	{
		$coupons = array();

		foreach ($codes as $code) {
			$coupons[] = $this->findByCode($code);
		}

		// TODO: Filter by dates somehow in DB?
		$time = time();
		$coupons = array_filter($coupons, function($coupon) use ($time) {
			/** @var $coupon \Jigoshop\Entity\Coupon */
			if ($coupon === null) {
				return false;
			}

			if ($coupon->getFrom() !== null && $coupon->getFrom()->getTimestamp() < $time) {
				return false;
			}
			if ($coupon->getTo() !== null && $coupon->getTo()->getTimestamp() > $time) {
				return false;
			}

			return true;
		});

		return $coupons;
	}

	/**
	 * @param $code string Code of the coupon to find.
	 * @return \Jigoshop\Entity\Coupon Coupon found.
	 */
	public function findByCode($code)
	{
		$query = new \WP_Query(array(
			'post_type' => Types::COUPON,
			'name' => $code,
		));

		$results = $this->findByQuery($query);

		if (count($results) > 0) {
			return $results[0];
		}

		return null;
	}
}
