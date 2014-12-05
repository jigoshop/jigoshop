<?php

namespace Jigoshop\Entity;

/**
 * Shop coupon entity.
 *
 * @package Jigoshop\Entity
 */
class Coupon implements EntityInterface
{
	/** @var int */
	private $id;
	/** @var string */
	private $title;
	/** @var int */
	private $type;
	/** @var string */
	private $code;
	/** @var string */
	private $amount;
	/** @var \DateTime */
	private $from;
	/** @var \DateTime */
	private $to;
	/** @var int */
	private $usageLimit;
	/** @var bool */
	private $individualUse;
	/** @var bool */
	private $freeShipping;
	/** @var float */
	private $orderTotalMinimum;
	/** @var float */
	private $orderTotalMaximum;
	/** @var array */
	private $products = array();
	/** @var array */
	private $excludedProducts = array();
	/** @var array */
	private $categories = array();
	/** @var array */
	private $excludedCategories = array();
	/** @var array */
	private $paymentMethods = array();

	public function __construct()
	{
		$this->from = new \DateTime();
		$this->to = new \DateTime();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return array
	 */
	public function getPaymentMethods()
	{
		return $this->paymentMethods;
	}

	/**
	 * @param array $paymentMethods
	 */
	public function setPaymentMethods($paymentMethods)
	{
		$this->paymentMethods = $paymentMethods;
	}

	/**
	 * @return array
	 */
	public function getCategories()
	{
		return $this->categories;
	}

	/**
	 * @param array $categories
	 */
	public function setCategories($categories)
	{
		$this->categories = $categories;
	}

	/**
	 * @param int $category Category ID.
	 */
	public function addCategory($category)
	{
		$this->categories[] = $category;
	}

	/**
	 * @param int $category Category ID.
	 */
	public function removeCategory($category)
	{
		$key = array_search($category, $this->categories);
		if ($key !== false) {
			unset($this->categories[$key]);
		}
	}

	/**
	 * @return array
	 */
	public function getExcludedCategories()
	{
		return $this->excludedCategories;
	}

	/**
	 * @param array $excludedCategories
	 */
	public function setExcludedCategories($excludedCategories)
	{
		$this->excludedCategories = $excludedCategories;
	}

	/**
	 * @param int $category Category ID.
	 */
	public function addExcludedCategory($category)
	{
		$this->excludedCategories[] = $category;
	}

	/**
	 * @param int $category Category ID.
	 */
	public function removeExcludedCategory($category)
	{
		$key = array_search($category, $this->excludedCategories);
		if ($key !== false) {
			unset($this->excludedCategories[$key]);
		}
	}

	/**
	 * @return array
	 */
	public function getProducts()
	{
		return $this->products;
	}

	/**
	 * @param array $products
	 */
	public function setProducts($products)
	{
		$this->products = $products;
	}

	/**
	 * @param int $product Product ID.
	 */
	public function addProduct($product)
	{
		$this->products[] = $product;
	}

	/**
	 * @param int $product Product ID.
	 */
	public function removeProduct($product)
	{
		$key = array_search($product, $this->products);
		if ($key !== false) {
			unset($this->products[$key]);
		}
	}

	/**
	 * @return array
	 */
	public function getExcludedProducts()
	{
		return $this->excludedProducts;
	}

	/**
	 * @param array $excludedProducts
	 */
	public function setExcludedProducts($excludedProducts)
	{
		$this->excludedProducts = $excludedProducts;
	}

	/**
	 * @param int $product Product ID.
	 */
	public function addExcludedProduct($product)
	{
		$this->excludedProducts[] = $product;
	}

	/**
	 * @param int $product Product ID.
	 */
	public function removeExcludedProduct($product)
	{
		$key = array_search($product, $this->excludedProducts);
		if ($key !== false) {
			unset($this->excludedProducts[$key]);
		}
	}

	/**
	 * @return boolean
	 */
	public function isFreeShipping()
	{
		return $this->freeShipping;
	}

	/**
	 * @param boolean $freeShipping
	 */
	public function setFreeShipping($freeShipping)
	{
		$this->freeShipping = $freeShipping;
	}

	/**
	 * @return \DateTime
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @param \DateTime $from
	 */
	public function setFrom($from)
	{
		$this->from = $from;
	}

	/**
	 * @return boolean
	 */
	public function isIndividualUse()
	{
		return $this->individualUse;
	}

	/**
	 * @param boolean $individualUse
	 */
	public function setIndividualUse($individualUse)
	{
		$this->individualUse = $individualUse;
	}

	/**
	 * @return float
	 */
	public function getOrderTotalMaximum()
	{
		return $this->orderTotalMaximum;
	}

	/**
	 * @param float $orderTotalMaximum
	 */
	public function setOrderTotalMaximum($orderTotalMaximum)
	{
		$this->orderTotalMaximum = $orderTotalMaximum;
	}

	/**
	 * @return float
	 */
	public function getOrderTotalMinimum()
	{
		return $this->orderTotalMinimum;
	}

	/**
	 * @param float $orderTotalMinimum
	 */
	public function setOrderTotalMinimum($orderTotalMinimum)
	{
		$this->orderTotalMinimum = $orderTotalMinimum;
	}

	/**
	 * @return \DateTime
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @param \DateTime $to
	 */
	public function setTo($to)
	{
		$this->to = $to;
	}

	/**
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param int $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return int
	 */
	public function getUsageLimit()
	{
		return $this->usageLimit;
	}

	/**
	 * @param int $usageLimit
	 */
	public function setUsageLimit($usageLimit)
	{
		$this->usageLimit = $usageLimit;
	}

	/**
	 * @return string
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @param string $amount
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	public function getStateToSave()
	{
		return array(
			'code' => $this->code,
			'type' => $this->type,
			'amount' => $this->amount,
			'from' => $this->from->getTimestamp(),
			'to' => $this->to->getTimestamp(),
			'usage_limit' => $this->usageLimit,
			'individual_use' => $this->individualUse,
			'free_shipping' => $this->freeShipping,
			'order_total_minimum' => $this->orderTotalMinimum,
			'order_total_maximum' => $this->orderTotalMaximum,
			'products' => $this->products,
			'excluded_products' => $this->excludedProducts,
			'categories' => $this->categories,
			'excluded_categories' => $this->excludedCategories,
			'payment_methods' => $this->paymentMethods,
		);
	}

	public function restoreState(array $state)
	{
		if (isset($state['code'])) {
			$this->code = $state['code'];
		}
		if (isset($state['type'])) {
			$this->type = $state['type'];
		}
		if (isset($state['amount'])) {
			$this->amount = $state['amount'];
		}
		if (isset($state['from'])) {
			$this->from->setTimestamp($state['from']);
		}
		if (isset($state['to'])) {
			$this->to->setTimestamp($state['to']);
		}
		if (isset($state['usage_limit'])) {
			$this->usageLimit = $state['usage_limit'];
		}
		if (isset($state['individual_use'])) {
			$this->individualUse = $state['individual_use'];
		}
		if (isset($state['free_shipping'])) {
			$this->freeShipping = $state['free_shipping'];
		}
		if (isset($state['order_total_minimum'])) {
			$this->orderTotalMinimum = $state['order_total_minimum'];
		}
		if (isset($state['order_total_maximum'])) {
			$this->orderTotalMaximum = $state['order_total_maximum'];
		}
		if (isset($state['products'])) {
			$this->products = $state['products'];
		}
		if (isset($state['excluded_products'])) {
			$this->excludedProducts = $state['excluded_products'];
		}
		if (isset($state['categories'])) {
			$this->categories = $state['categories'];
		}
		if (isset($state['excluded_categories'])) {
			$this->excludedCategories = $state['excluded_categories'];
		}
		if (isset($state['payment_methods'])) {
			$this->paymentMethods = $state['payment_methods'];
		}
	}
}
