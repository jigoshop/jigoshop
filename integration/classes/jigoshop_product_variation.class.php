<?php

class jigoshop_product_variation extends jigoshop_product
{
	public $variation_id;
	public $variation_data;
	public $sale_price_dates_from;
	public $sale_price_dates_to;

	/** @var \Jigoshop\Entity\Product\Variable\Variation */
	private $__variation;

	public function __construct($product, $variation_id = null, $variation = null)
	{
		parent::__construct($product);

		if ($variation_id !== null) {
			$this->variation_id = $variation_id;
			/** @var \Jigoshop\Entity\Product\Variable $product */
			$product = $this->__getProduct();
			$this->__variation = $product->getVariation($variation_id);
		}

		if ($variation !== null) {
			$this->variation_data = $variation;
		}
	}

	public function get_sku()
	{
		return $this->__variation->getProduct()->getSku();
	}

	public function get_variation_id()
	{
		return (int)$this->variation_id;
	}

	public function get_variation_attributes()
	{
		return $this->variation_data; // @todo: This returns blank if its set to catch all, how would we deal with that?
	}

	public function get_price()
	{
		return $this->__variation->getProduct()->getPrice();
	}

	public function is_on_sale()
	{
		$product = $this->__variation->getProduct();
		if ($product instanceof \Jigoshop\Entity\Product\Saleable) {
			return $product->getSales()->isEnabled();
		}

		return false;
	}

	public function get_stock()
	{
		return $this->__variation->getProduct()->getStock()->getStock();
	}

	/**
	 * Update values of variation attributes using given values
	 *
	 * @param   array $data array of attributes and values
	 */
	public function set_variation_attributes(array $data)
	{
		// TODO: Properly update
		if (!empty($this->variation_data) && is_array($this->variation_data)) {
			foreach ($this->variation_data as $attribute => $value) {
				if (isset($data[$attribute])) {
					$this->variation_data[$attribute] = $data[$attribute];
				}
			}
		}
	}
}
