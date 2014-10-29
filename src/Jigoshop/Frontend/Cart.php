<?php

namespace Jigoshop\Frontend;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
use Jigoshop\Shipping\Method;
use WPAL\Wordpress;

class Cart implements OrderInterface
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var ProductServiceInterface  */
	private $productService;
	/** @var TaxServiceInterface */
	private $taxService;
	/** @var ShippingServiceInterface */
	private $shippingService;

	/** @var string */
	private $id;
	private $items = array();
	private $tax = array();
	/** @var Method */
	private $shippingMethod;
	private $total = 0.0;
	private $subtotal = 0.0;
	private $productSubtotal = 0.0;

	/**
	 * @param Wordpress $wp
	 * @param Options $options
	 * @param ProductServiceInterface $productService
	 * @param TaxServiceInterface $taxService
	 * @param ShippingServiceInterface $shippingService
	 */
	public function __construct(Wordpress $wp, Options $options, ProductServiceInterface $productService, TaxServiceInterface $taxService, ShippingServiceInterface $shippingService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->taxService = $taxService;
		$this->shippingService = $shippingService;

		foreach ($this->options->get('tax.classes') as $class) {
			$this->tax[$class['class']] = 0.0;
		}
	}

	/**
	 * @param string $id
	 * @param array $data
	 */
	public function initializeFor($id, $data = array())
	{
		$this->id = $id;
		$this->items = array();
		$this->total = 0.0;
		$this->subtotal = 0.0;
		$this->productSubtotal = 0.0;
		$this->tax = array_map(function(){ return 0.0; }, $this->tax);
		$this->shippingMethod = null;

		if (!empty($data)) {
			$this->id = $data['id'];
			$items = unserialize($data['items']);
			if (isset($data['shipping_method'])) {
				$this->setShippingMethod($this->shippingService->findForState($data['shipping_method']), $this->taxService);
			}
			$taxIncludedInPrice = $this->options->get('tax.included');

			if (is_array($items)) {
				foreach ($items as $itemData) {
					$item = new Item();
					$product = $this->productService->findForState($itemData['product']);
					$item->setProduct($product);
					$item->setQuantity($itemData['quantity']);
					$item->setPrice($product->getPrice());
					$item->setName($product->getName());
					$item->setTax($this->taxService->getAll($product));

					foreach ($item->getTax() as $class => $value) {
						$this->tax[$class] += $value * $item->getQuantity();
					}

					$key = $this->getItemKey($product);

					// TODO: Add support for "Price included in tax"
//					if ($taxIncludedInPrice) {
//						$price -= $tax;
//					}

					$this->subtotal += $item->getCost();
					$this->productSubtotal += $item->getCost();
					$this->total += $item->getCost() + $item->getTotalTax();

					$this->items[$key] = $item;
				}
			}
		}
	}

	/**
	 * @return string Cart ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Adds item to the cart.
	 *
	 * If item is already present - increases it's quantity.
	 *
	 * @param Item $item Item to add to cart.
	 */
	public function addItem(Item $item)
	{
		$product = $item->getProduct();
		$quantity = $item->getQuantity();

		if ($product === null || $product->getId() === 0) {
			throw new Exception(__('Product not found', 'jigoshop'));
		}

		if (!($product instanceof Product\Purchasable)) {
			throw new Exception(sprintf(__('Product of type "%s" cannot be added to cart', 'jigoshop'), $product->getType()));
		}

		if ($quantity <= 0) {
			throw new Exception(__('Quantity has to be positive number', 'jigoshop'));
		}

		$key = $this->getItemKey($product);
		if (isset($this->items[$key])) {
			/** @var Item $itemInCart */
			$itemInCart = $this->items[$key];
			$itemInCart->setQuantity($itemInCart->getQuantity() + $item->getQuantity());
		} else {
			$item->setTax($this->taxService->getAll($product));

			foreach ($item->getTax() as $class => $value) {
				$this->tax[$class] += $value * $quantity;
			}

			$tax = $this->taxService->calculate($item);
			$price = $item->getPrice();
			// TODO: Support for "Price includes tax"
//			if ($this->options->get('tax.included')) {
//				$price -= $tax;
//			}

			$this->total += $quantity * ($price + $tax);
			$this->subtotal += $quantity * $price;
			$this->productSubtotal += $quantity * $price;

			$this->items[$key] = $item;
		}
	}

	/**
	 * Removes item from cart.
	 *
	 * @param string $key Item id to remove from cart.
	 * @return Item|bool Removed item or null if not found.
	 */
	public function removeItem($key)
	{
		if (isset($this->items[$key])) {
			// TODO: Support for "Price includes tax"
			/** @var Item $item */
			$item = $this->items[$key];
			$this->total -= $item->getCost() + $item->getTotalTax();
			$this->subtotal -= $item->getCost();
			$this->productSubtotal -= $item->getCost();
			foreach ($item->getTaxClasses() as $class) {
				$this->tax[$class] -= $this->taxService->get($item, $class) * $item->getQuantity();
			}

			unset($this->items[$key]);
			return $item;
		}

		return null;
	}

	public function getRemoveUrl($key)
	{
		// TODO: Move this properly to different class
		return add_query_arg(array('action' => 'remove-item', 'item' => $key));
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
		if (!isset($this->items[$key])) {
			throw new Exception(__('Item does not exists', 'jigoshop')); // TODO: Will be nice to get better error message
		}

		if (!is_numeric($quantity)) {
			throw new Exception(__('Quantity has to be numeric value', 'jigoshop'));
		}

		$item = $this->removeItem($key);

		if ($quantity <= 0) {
			return;
		}

		$item->setQuantity($quantity);
		$this->addItem($item);
	}

	/**
	 * @param $key string Item key.
	 * @return Item Item data.
	 */
	public function getItem($key)
	{
		if (!isset($this->items[$key])) {
			throw new Exception(__('Item does not exists', 'jigoshop')); // TODO: Will be nice to get better error message
		}

		return $this->items[$key];
	}

	/**
	 * @return array List of items in the cart.
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return bool Is the cart empty?
	 */
	public function isEmpty()
	{
		return empty($this->items);
	}

	/**
	 * @return float Current total value of the cart.
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * @return float Current subtotal of the cart.
	 */
	public function getSubtotal()
	{
		return $this->subtotal;
	}

	/**
	 * @return float Current products subtotal of the cart.
	 */
	public function getProductSubtotal()
	{
		return $this->productSubtotal;
	}

	/**
	 * @return array List of tax values per tax class.
	 */
	public function getTax()
	{
		return $this->tax;
	}

	/**
	 * @param $taxClass string Tax class name.
	 * @return string Label for selected tax class.
	 */
	public function getTaxLabel($taxClass)
	{
		return $this->taxService->getLabel($taxClass);
	}

	/**
	 * @return Method Currently selected shipping method.
	 */
	public function getShippingMethod()
	{
		return $this->shippingMethod;
	}

	/**
	 * Sets shipping method and updates cart totals to reflect it's price.
	 *
	 * @param Method $method New shipping method.
	 * @param TaxServiceInterface $taxService Tax service to calculate tax value of shipping.
	 * @param Customer $customer Customer for tax calculation.
	 */
	public function setShippingMethod(Method $method, TaxServiceInterface $taxService, Customer $customer = null)
	{
		// TODO: Improve this part of code.
		if ($this->shippingMethod !== null) {
			$price = $this->shippingMethod->calculate($this);
			$this->subtotal -= $price;
			$this->total -= $price + $taxService->calculateShipping($this->shippingMethod, $price);
			foreach ($this->shippingMethod->getTaxClasses() as $class) {
				$this->tax[$class] -= $taxService->getShipping($this->shippingMethod, $price, $class);
			}
		}

		$this->shippingMethod = $method;
		$price = $method->calculate($this);
		$this->subtotal += $price;
		$this->total += $price + $taxService->calculateShipping($method, $price);
		foreach ($method->getTaxClasses() as $class) {
			$this->tax[$class] += $taxService->getShipping($method, $price, $class);
		}
	}

	/**
	 * Generates representation of current cart state.
	 *
	 * @return string the string representation of the cart or null
	 */
	public function getState()
	{
		return array(
			'id' => $this->id,
			'shipping_method' => $this->shippingMethod !== null ? $this->shippingMethod->getState() : null,
			'items' => serialize(array_map(function($item){
				/** @var $item Item */
				return array(
					'product' => $item->getProduct()->getState(),
					'quantity' => $item->getQuantity(),
				);
			}, $this->items)),
		);
	}

	/**
	 * Returns unique key for product in the cart.
	 *
	 * @param $product Product|Product\Purchasable Product to get key for.
	 * @return string
	 */
	private function getItemKey($product)
	{
		return $product->getId();
	}

	/**
	 * Checks whether given shipping method is set for current cart.
	 *
	 * @param $method Method Shipping method to check.
	 * @return bool Is the method selected?
	 */
	public function hasShippingMethod($method)
	{
		if ($this->shippingMethod !== null) {
			return $this->shippingMethod->getId() == $method->getId();
		}

		return false;
	}

	/**
	 * @return array List of applied tax classes for shipping with it's values.
	 */
	public function getShippingTax()
	{
		return array();
	}
}
