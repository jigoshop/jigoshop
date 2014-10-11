<?php

namespace Jigoshop\Frontend;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use Jigoshop\Service\TaxServiceInterface;
use Jigoshop\Shipping\Method;
use WPAL\Wordpress;

class Cart
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
	 * @param string $data
	 */
	public function initializeFor($id, $data = '')
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
				$this->setShippingMethod($this->shippingService->findForState($data['shipping_method']));
			}
			$taxIncludedInPrice = $this->options->get('tax.included');

			if (is_array($items)) {
				foreach ($items as $item) {
					$product = $this->productService->findForState($item['item']);

					foreach ($product->getTaxClasses() as $class) {
						$this->tax[$class] += $this->taxService->get($product, $class) * $item['quantity'];
					}

					$key = $this->getItemKey($product);
					$price = $product->getPrice();
					$tax = $this->taxService->calculate($product);

					if ($taxIncludedInPrice) {
						$price -= $tax;
					}

					$this->subtotal += $price * $item['quantity'];
					$this->productSubtotal += $price * $item['quantity'];
					$this->total += ($price + $tax) * $item['quantity'];

					$this->items[$key] = array(
						'item' => $product,
						'quantity' => $item['quantity'],
						'price' => $price,
						'tax' => $tax,
					);
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
	 * @param Product|Product\Purchasable $product Product to add to cart.
	 * @param $quantity int Quantity of products to add.
	 * @throws Exception On error.
	 */
	public function addItem(Product $product, $quantity)
	{
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
			$this->items[$key]['quantity'] += $quantity;
		} else {
			foreach ($product->getTaxClasses() as $class) {
				$this->tax[$class] += $this->taxService->get($product, $class) * $quantity;
			}

			$tax = $this->taxService->calculate($product);
			$price = $product->getPrice();
			if ($this->options->get('tax.included')) {
				$price -= $tax;
			}

			$this->total += $quantity * ($price + $tax);
			$this->subtotal += $quantity * $price;
			$this->productSubtotal += $quantity * $price;

			$this->items[$key] = array(
				'item' => $product,
				'price' => $price,
				'tax' => $tax,
				'quantity' => $quantity,
			);
		}
	}

	/**
	 * Removes item from cart.
	 *
	 * @param string $key Item id to remove from cart.
	 * @return bool Is item removed?
	 */
	public function removeItem($key)
	{
		if (isset($this->items[$key])) {
			/** @var Product $product */
			$product = $this->items[$key]['item'];
			$this->total -= ($this->items[$key]['price'] + $this->items[$key]['tax']) * $this->items[$key]['quantity'];
			$this->subtotal -= $this->items[$key]['price'] * $this->items[$key]['quantity'];
			$this->productSubtotal -= $this->items[$key]['price'] * $this->items[$key]['quantity'];
			foreach ($product->getTaxClasses() as $class) {
				$this->tax[$class] -= $this->taxService->get($product, $class) * $this->items[$key]['quantity'];
			}

			unset($this->items[$key]);
		}

		return true;
	}

	public function getRemoveUrl($key)
	{
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

		if ($quantity <= 0) {
			$this->removeItem($key);
			return;
		}

		/** @var Product $product */
		$product = $this->items[$key]['item'];
		$difference = $quantity - $this->items[$key]['quantity'];
		$this->total += ($this->items[$key]['price'] + $this->items[$key]['tax']) * $difference;
		$this->subtotal += $this->items[$key]['price'] * $difference;
		$this->productSubtotal += $this->items[$key]['price'] * $difference;
		foreach ($product->getTaxClasses() as $class) {
			$this->tax[$class] += $this->taxService->get($product, $class) * $difference;
		}
		$this->items[$key]['quantity'] = $quantity;
	}

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
	 */
	public function setShippingMethod(Method $method)
	{
		// TODO: Improve this part of code.
		if ($this->shippingMethod !== null) {
			$price = $this->shippingMethod->calculate($this);
			$this->subtotal -= $price;
			$this->total -= $price + $this->taxService->calculateShipping($this->shippingMethod, $price);
			foreach ($this->shippingMethod->getTaxClasses() as $class) {
				$this->tax[$class] -= $this->taxService->getShipping($this->shippingMethod, $price, $class);
			}
		}

		$this->shippingMethod = $method;
		$price = $method->calculate($this);
		$this->subtotal += $price;
		$this->total += $price + $this->taxService->calculateShipping($method, $price);
		foreach ($method->getTaxClasses() as $class) {
			$this->tax[$class] += $this->taxService->getShipping($method, $price, $class);
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
				/** @var $product Product */
				$product = $item['item'];
				return array(
					'item' => $product->getState(),
					'quantity' => $item['quantity'],
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
}
