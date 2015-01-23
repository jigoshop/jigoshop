<?php

namespace Jigoshop\Entity;

use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Exception;
use Jigoshop\Frontend\NotEnoughStockException;
use Jigoshop\Helper\Product as ProductHelper;
use WPAL\Wordpress;

class Cart extends Order
{
	/** @var array */
	private $couponData = array();

	public function __construct(Wordpress $wp, array $taxClasses)
	{
		parent::__construct($wp, $taxClasses);
	}

	/**
	 * Updates quantity of selected item by it's key.
	 *
	 * @param $key string Item key in the cart.
	 * @param $quantity int Quantity to set.
	 * @throws Exception When product does not exists or quantity is not numeric.
	 */
	public function updateQuantity($key, $quantity)
	{
		if (!$this->hasItem($key)) {
			throw new Exception(__('Item does not exists', 'jigoshop'));
		}

		if (!is_numeric($quantity)) {
			throw new Exception(__('Quantity has to be numeric value', 'jigoshop'));
		}

		$item = $this->removeItem($key);

		if ($item === null) {
			throw new Exception(__('Item not found.', 'jigoshop'));
		}

		if ($quantity <= 0) {
			return;
		}

		$item->setQuantity($quantity);
		$this->addItem($item);
	}

	/**
	 * Adds item to the cart.
	 * If item is already present - increases it's quantity.
	 *
	 * @param Item $item Item to add to cart.
	 * @throws NotEnoughStockException When user requests more than we have.
	 * @throws Exception On any error.
	 */
	public function addItem(Item $item)
	{
		$product = $item->getProduct();
		$quantity = $item->getQuantity();

		if ($product === null || $product->getId() === 0) {
			throw new Exception(__('Product not found', 'jigoshop'));
		}

		if ($quantity <= 0) {
			throw new Exception(__('Quantity has to be positive number', 'jigoshop'));
		}

		if ($product instanceof Product\Purchasable && !$this->checkStock($product, $quantity)) {
			throw new NotEnoughStockException($product->getStock()->getStock());
		}

		$isValid = $this->wp->applyFilters('jigoshop\cart\validate_new_item', true, $product->getId(), $item->getQuantity());
		if (!$isValid) {
			throw new Exception(__('Could not add to cart.', 'jigoshop'));
		}

		if ($this->hasItem($item->getKey())) {
			/** @var Item $itemInCart */
			$itemInCart = $this->getItem($item->getKey());
			$itemInCart->setQuantity($itemInCart->getQuantity() + $item->getQuantity());
		} else {
			$item = $this->wp->applyFilters('jigoshop\cart\new_item', $item);
			parent::addItem($item);
		}
	}

	/**
	 * @param $product Product\Purchasable
	 * @param $quantity int
	 * @return bool
	 */
	private function checkStock($product, $quantity)
	{
		if (!$product->getStock()->getManage()) {
			return $product->getStock()->getStatus() == StockStatus::IN_STOCK;
		}

		if ($quantity >= $product->getStock()->getStock()) {
			if (in_array($product->getStock()->getAllowBackorders(), array(
				StockStatus::BACKORDERS_ALLOW,
				StockStatus::BACKORDERS_NOTIFY
			))) {
				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * @return bool Is the cart empty?
	 */
	public function isEmpty()
	{
		$items = $this->getItems();
		return empty($items);
	}

	/**
	 * @return array Coupons list.
	 */
	public function getCoupons()
	{
		return array_map(function ($item){
			return $item['object'];
		}, $this->couponData);
	}

	/**
	 * @return bool Whether cart has coupons applied.
	 */
	public function hasCoupons()
	{
		return !empty($this->couponData);
	}

	/**
	 * Removes and adds each coupon currently applied to the cart. This causes to recalculate discount values.
	 */
	public function recalculateCoupons()
	{
		foreach ($this->couponData as $data) {
			/** @var Coupon $coupon */
			$coupon = $data['object'];

			$this->removeCoupon($coupon->getId());
			try {
				$this->addCoupon($coupon);
			} catch (Exception $e) {
				// TODO: Some idea how to report this to the user?
			}
		}
	}

	/**
	 * @param $id int Coupon ID.
	 */
	public function removeCoupon($id)
	{
		if (!isset($this->couponData[$id])) {
			return;
		}

		$coupon = $this->couponData[$id];
		/** @var Coupon $object */
		$object = $coupon['object'];
		parent::removeCoupon($object->getCode());
		$this->removeDiscount($coupon['discount']);
		unset($this->couponData[$id]);
	}

	/**
	 * @param $coupon Coupon
	 */
	public function addCoupon($coupon)
	{
		if (!is_object($coupon)) {
			parent::addCoupon($coupon);

			return;
		}

		if (isset($this->couponData[$coupon->getId()])) {
			return;
		}

		if (is_numeric($coupon->getOrderTotalMinimum()) && $this->getTotal() < $coupon->getOrderTotalMinimum()) {
			throw new Exception(sprintf(__('Cannot apply coupon "%s". Order total less than %s.'), $coupon->getCode(), ProductHelper::formatPrice($coupon->getOrderTotalMinimum())));
		}
		if (is_numeric($coupon->getOrderTotalMaximum()) && $this->getTotal() > $coupon->getOrderTotalMaximum()) {
			throw new Exception(sprintf(__('Cannot apply coupon "%s". Order total more than %s.'), $coupon->getCode(), ProductHelper::formatPrice($coupon->getOrderTotalMaximum())));
		}

		if ($coupon->isIndividualUse()) {
			$this->removeAllCouponsExcept(array());
		}

		$discount = $coupon->getDiscount($this);
		$this->couponData[$coupon->getId()] = array(
			'object' => $coupon,
			'discount' => $discount,
		);

		parent::addCoupon($coupon->getCode());
		$this->addDiscount($discount);
	}

	/**
	 * Removes all coupons except ones listed in the parameter.
	 *
	 * @param $codes array List of actual coupon codes.
	 */
	public function removeAllCouponsExcept($codes)
	{
		foreach ($this->couponData as $coupon) {
			/** @var Coupon $coupon */
			$coupon = $coupon['object'];
			if (!in_array($coupon->getCode(), $codes)) {
				$this->removeCoupon($coupon->getId());
			}
		}
	}

	public function getStateToSave()
	{
		$state = parent::getStateToSave();
		$state['items'] = serialize($state['items']);
		unset($state['update_messages'], $state['updated_at'], $state['completed_at'], $state['total'],
			$state['subtotal']);

		return $state;
	}
}
