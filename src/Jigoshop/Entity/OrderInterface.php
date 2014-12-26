<?php
namespace Jigoshop\Entity;

use Jigoshop\Entity\Order\Item;
use Jigoshop\Exception;
use Jigoshop\Shipping\Method;

interface OrderInterface
{
	/**
	 * @return string Order ID.
	 */
	public function getId();

	/**
	 * @return Customer The customer.
	 */
	public function getCustomer();

	/**
	 * Adds item to the order
	 * .
	 * If item is already present - increases it's quantity.
	 *
	 * @param Item $item Order item to add.
	 */
	public function addItem(Item $item);

	/**
	 * Removes item from order.
	 *
	 * @param string $key Item id to remove from order.
	 * @return bool Is item removed?
	 */
	public function removeItem($key);

	/**
	 * Updates quantity of selected item by it's key.
	 *
	 * @param $key string Item key in the cart.
	 * @param $quantity int Quantity to set.
	 * @throws Exception When product does not exists or quantity is not numeric.
	 */
	public function updateQuantity($key, $quantity);

	/**
	 * @param $key string Item key.
	 * @return array Item data.
	 */
	public function getItem($key);

	/**
	 * @return array List of items in the order.
	 */
	public function getItems();

	/**
	 * @return float Current total value of the order.
	 */
	public function getTotal();

	/**
	 * @return float Current subtotal of the order.
	 */
	public function getSubtotal();

	/**
	 * @return float Current products subtotal of the order.
	 */
	public function getProductSubtotal();

	/**
	 * @return float Total discount of the cart.
	 */
	public function getDiscount();

	/**
	 * @return array List of tax values per tax class.
	 */
	public function getTax();

	/**
	 * @return array Tax definitions.
	 */
	public function getTaxDefinitions();

	/**
	 * @param array $taxDefinitions New tax definitions.
	 */
	public function setTaxDefinitions($taxDefinitions);

	/**
	 * @return array All tax data combined.
	 */
	public function getCombinedTax();

	/**
	 * @return float Shipping price.
	 */
	public function getShippingPrice();

	/**
	 * @return array List of applied tax classes for shipping with it's values.
	 */
	public function getShippingTax();

	/**
	 * @return Method Currently selected shipping method.
	 */
	public function getShippingMethod();

	/**
	 * Sets shipping method and updates cart totals to reflect it's price.
	 *
	 * @param Method $method New shipping method.
	 */
	public function setShippingMethod(Method $method);

	/**
	 * Checks whether given shipping method is set for current cart.
	 *
	 * @param $method Method Shipping method to check.
	 * @param $rate Rate Shipping rate to check.
	 * @return bool Is the method selected?
	 */
	public function hasShippingMethod($method, $rate = null);
}
