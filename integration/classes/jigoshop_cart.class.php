<?php

use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Helper\Product;
use Jigoshop\Integration;

class jigoshop_cart
{
	public static $cart_contents_total;
	public static $cart_contents_total_ex_tax;
	public static $cart_contents_weight;
	public static $cart_contents_count;
	public static $cart_dl_count;
	public static $cart_contents_total_ex_dl;
	public static $total;
	public static $subtotal;
	public static $subtotal_ex_tax;
	public static $discount_total;
	public static $shipping_total;
	public static $shipping_tax_total;
	public static $applied_coupons;
	public static $cart_contents;

	private static $price_per_tax_class_ex_tax;
	private static $instance;

	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function reset()
	{
		self::$instance = null;
	}

	public function __clone()
	{
		trigger_error("Cloning Singleton's is not allowed.", E_USER_ERROR);
	}

	public function __wakeup()
	{
		trigger_error("Unserializing Singleton's is not allowed.", E_USER_ERROR);
	}

	protected function __construct()
	{
		self::$applied_coupons = array();
		self::calculate_cart_total();
	}

	public static function get_cart_from_session()
	{
//		$cart = Integration::getCart();
		// TODO: Any idea of how to keep cart_contents on par with Cart?
//		self::$cart_contents = (array)jigoshop_session::instance()->cart;
	}

	private static function calculate_cart_total()
	{
		// New cart keeps all values synchronized all the time
		$cart = Integration::getCart();

		self::$total = $cart->getTotal();
		self::$shipping_total = $cart->getShippingPrice();
		self::$shipping_tax_total = array_sum($cart->getShippingTax());
		self::$discount_total = $cart->getDiscount();
		self::$subtotal = $cart->getSubtotal();
		self::$subtotal_ex_tax = $cart->getSubtotal();
		self::$applied_coupons = $cart->getCoupons();
		self::$cart_contents_total_ex_tax = array_sum(array_map(
			function($item){
				/** @var $item \Jigoshop\Entity\Order\Item */
				return $item->getCost();
			},
			$cart->getItems()
		));
		self::$cart_contents_total = self::$cart_contents_total_ex_tax + $cart->getTotalTax();
		self::$cart_contents_count = array_sum(array_map(
			function($item){
				/** @var $item \Jigoshop\Entity\Order\Item */
				return $item->getQuantity();
			},
			$cart->getItems()
		));
		self::$cart_contents_weight = array_sum(array_map(
			function($item){
				/** @var $item \Jigoshop\Entity\Order\Item */
				return $item->getQuantity() * $item->getProduct()->getSize()->getWeight();
			},
			$cart->getItems()
		));
		self::$cart_dl_count = array_sum(array_map(
			function($item){
				/** @var $item \Jigoshop\Entity\Order\Item */
				return $item->getQuantity();
			},
			array_filter(
				$cart->getItems(),
				function($item){
					/** @var $item \Jigoshop\Entity\Order\Item */
					return $item->getType() == \Jigoshop\Entity\Product\Downloadable::TYPE;
				}
			)
		));
		self::$cart_contents_total_ex_dl = array_sum(array_map(
			function($item){
				/** @var $item \Jigoshop\Entity\Order\Item */
				return $item->getCost();
			},
			array_filter(
				$cart->getItems(),
				function($item){
					/** @var $item \Jigoshop\Entity\Order\Item */
					return $item->getType() != \Jigoshop\Entity\Product\Downloadable::TYPE;
				}
			)
		));
	}

	private static function reset_totals()
	{
		self::$total = 0;
		self::$cart_contents_total = 0;
		self::$cart_contents_total_ex_tax = 0;
		self::$cart_contents_weight = 0;
		self::$cart_contents_count = 0;
		self::$shipping_tax_total = 0;
		self::$subtotal = 0;
		self::$subtotal_ex_tax = 0;
		self::$discount_total = 0;
		self::$shipping_total = 0;
		self::$cart_dl_count = 0;
		self::$cart_contents_total_ex_dl = 0; /* for table rate shipping */
		self::$price_per_tax_class_ex_tax = array(); /* currently used with norway */
	}

	public static function empty_cart()
	{
		$cart = Integration::getCart();
		$cart->removeItems();
		$cart->removeShippingMethod();
		$cart->removeAllCouponsExcept(array());

		self::$cart_contents = array();
		self::$applied_coupons = array();
		self::reset_totals();
	}

	public static function is_empty()
	{
		return Integration::getCart()->isEmpty();
	}

	public static function has_coupons()
	{
		return Integration::getCart()->hasCoupons();
	}

	public static function add_to_cart($product_id, $quantity = 1, $variation_id = '', $variation = array())
	{
		if ($quantity < 0) {
			return false;
		}

		$productService = Integration::getProductService();
		$product = $productService->find($product_id);
		$_POST['variation_id'] = $variation_id;
		$_POST['attributes'] = $variation;
		$item = apply_filters('jigoshop\cart\add', null, $product);

		if ($item !== null) {
			Integration::getCart()->addItem($item);
			return true;
		}

		return false;
	}

	/**
	 * Generate a unique ID for the cart item being added
	 *
	 * @param int $product_id - id of the product the key is being generated for
	 * @param int|string $variation_id of the product the key is being generated for
	 * @param array|string $variation data for the cart item
	 * @param array|string $cart_item_data other cart item data passed which affects this items uniqueness in the cart
	 * @return string cart item key
	 */
	public static function generate_cart_id($product_id, $variation_id = '', $variation = '', $cart_item_data = '')
	{
		$productService = Integration::getProductService();
		$product = $productService->find($product_id);
		$_POST['variation_id'] = $variation_id;
		$_POST['attributes'] = $variation;
		$item = apply_filters('jigoshop\cart\add', null, $product);

		return $productService->generateItemKey($item);
	}

	/**
	 * Check if product is in the cart and return cart item key
	 * Cart item key will be unique based on the item and its properties, such as variations
	 *
	 * @param mixed $cart_id ID of product to find in the cart
	 * @return string cart item key
	 */
	public static function find_product_in_cart($cart_id = false)
	{
		if ($cart_id !== false) {
			$cart = Integration::getCart();

			if (in_array($cart_id, array_keys($cart->getItems()))) {
				return $cart_id;
			}
		}

		return false;
	}

	/**
	 * Set the quantity for an item in the cart
	 * Remove the item from the cart if no quantity
	 * Also remove any product discounts if applied
	 *
	 * @param $cart_item string contains the id of the cart item
	 * @param int $quantity string contains the quantity of the item
	 * @internal
	 */
	public static function set_quantity($cart_item, $quantity = 1)
	{
		$cart = Integration::getCart();
		if ($quantity == 0 || $quantity < 0) {
			$cart->removeItem($cart_item);
		} else {
			$cart->updateQuantity($cart_item, $quantity, Integration::getTaxService());
		}

		self::set_session();
	}

	public static function is_valid_coupon($coupon)
	{
		// Let's assume coupon is valid - Jigoshop checks it's status later
		return true;
	}

	public static function has_valid_products_for_coupon($coupon)
	{
		// Let's assume cart has valid products for coupon - Jigoshop checks them later
		return true;
	}

	public static function set_session()
	{
		// TODO: Do we need to re-synchronize data?

		self::calculate_totals();
	}

	public static function calculate_totals()
	{
		self::calculate_cart_total();
	}

	public static function needs_shipping()
	{
		return Integration::getCart()->isShippingRequired();
	}

	public static function get_applied_tax_classes()
	{
		return array_keys(Integration::getCart()->getTax());
	}

	public static function is_not_compounded_tax($tax_class)
	{
		// TODO: Implement with compound tax
//		return self::$tax->is_tax_non_compounded($tax_class);
	}

	/**
	 * Returns a calculated subtotal.
	 *
	 * @param boolean $for_display Just the price itself or with currency symbol + price with optional "(ex. tax)" / "(inc. tax)".
	 * @param boolean $apply_discount_and_shipping Subtotal with discount and shipping prices applied.
	 * @param boolean $order_exclude_tax Subtotal without taxes no matter settings used by Orders
	 * @return mixed|string|void
	 */
	public static function get_cart_subtotal($for_display = true, $apply_discount_and_shipping = false, $order_exclude_tax = false)
	{
		do_action('jigoshop_calculate_totals');

		/* Just some initialization. */
//		$discount = self::$discount_total;
		$subtotal = self::$subtotal;
		$tax_label = 0; // use with jigoshop_price. 0 for no label, 1 for ex. tax, 2 for inc. tax

		/**
		 * Tax calculation turned ON.
		 */
			// for final Orders in the Admin we always need tax out
		$options = Integration::getOptions();
			if ($order_exclude_tax) {
				$subtotal = $options->get('tax.included') ? self::$subtotal_ex_tax : $subtotal;
				if($options->get('tax.price_tax') == 'with_tax') {
					$tax_label = 1; //ex. tax
				}
			} else {
				if ($options->get('tax.included')) {
					$tax_label = 2; //inc. tax
				} else {
					$subtotal = self::$subtotal_ex_tax;
					if($options->get('tax.price_tax') == 'with_tax') {
						$tax_label = 1; //ex. tax
					}
				}
			}

		// Don't show the discount bit in the subtotal because discount will be calculated after taxes
		// thus in the grand total (not the subtotal). */
		// TODO: Is coupons after tax legal?
//		if (self::get_options()->get('jigoshop_tax_after_coupon') == 'yes') {
//			$discount = 0;
//		}

		/* Display totals with discount & shipping applied? */
		// This is only 'true' with the 'Retail Price' displays on Cart, Checkout, View Order
		// Someone should explain why this is used instead of 'Subtotal'
//		if ($apply_discount_and_shipping) {
//			$subtotal = $subtotal + jigoshop_cart::get_cart_shipping_total(false);
//			$subtotal = ($discount > $subtotal) ? $subtotal : $subtotal - $discount;
//		}

		/* Return a pretty number or just the float. */
		if ($for_display) {
			$return = Product::formatPrice($subtotal);
			switch ($tax_label) {
				case 1:
					$return .= __('(inc. tax)', 'jigoshop');
					break;
				case 2:
					$return .= __('(ex. tax)', 'jigoshop');
					break;
			}
		} else {
			$return = Product::formatNumericPrice($subtotal);
		}

		return $return;
	}

	public static function get_subtotal()
	{
		do_action('jigoshop_calculate_totals');
		return Integration::getCart()->getSubtotal();
	}

	public static function get_discount_subtotal()
	{
		$cart = Integration::getCart();
		return $cart->getSubtotal() - $cart->getDiscount();
	}

	public static function get_cart_shipping_total($for_display = true, $order_exclude_tax = false)
	{
		$cart = Integration::getCart();
		if ($cart->getShippingMethod() === null) {
			return false;
		}

		/* Shipping price is 0.00. */
		if ($cart->getShippingPrice() <= 0) {
			return ($for_display ? __('Free!', 'jigoshop') : 0);
		}

		$value = $cart->getShippingPrice() + array_reduce($cart->getShippingTax(), 'sum');

		if ($for_display) {
			$return = Product::formatPrice($value);
			if (Integration::getOptions()->get('tax.included') && !$order_exclude_tax) {
				$return .= __('(inc. tax)', 'jigoshop');
			} else {
				$return .= __('(ex. tax)', 'jigoshop');
			}
		} else {
			$return = Product::formatNumericPrice($value);
		}

		return $return;
	}

	public static function get_shipping_total()
	{
		return Integration::getCart()->getShippingPrice();
	}

	public static function get_shipping_tax()
	{
		return array_reduce(Integration::getCart()->getShippingTax(), 'tax');
	}

	public static function get_cart()
	{
		if (empty(self::$cart_contents)) {
			self::get_cart_from_session();
		}

		return self::$cart_contents;
	}

	public static function get_cart_url()
	{
		$url = get_permalink(Integration::getOptions()->getPageId(\Jigoshop\Core\Pages::CART));
		return apply_filters('jigoshop_get_cart_url', $url);
	}

	public static function get_checkout_url()
	{
		$options = Integration::getOptions();
		$url = get_permalink($options->getPageId(\Jigoshop\Core\Pages::CHECKOUT));
		$url = apply_filters('jigoshop_get_checkout_url', $url);

		if ($options->get('advanced.force_ssl')) {
			$url = str_replace('http:', 'https:', $url);
		}

		return $url;
	}

	public static function get_shop_url()
	{
		$url = get_permalink(Integration::getOptions()->getPageId(\Jigoshop\Core\Pages::SHOP));
		return apply_filters('jigoshop_get_shop_page_id', $url);
	}

	/** gets the url to remove an item from the cart
	 *
	 * @param $cart_item_key
	 * @return mixed|string|void
	 */
	public static function get_remove_url($cart_item_key)
	{
		return  \Jigoshop\Helper\Order::getRemoveLink($cart_item_key);
	}

	public static function ship_to_billing_address_only()
	{
		return Integration::getOptions()->get('shipping.only_to_billing');
	}

	public static function needs_payment()
	{
		return Integration::getCart()->getTotal() > 0;
	}

	public static function check_cart_item_stock()
	{
		foreach (Integration::getCart()->getItems() as $item) {
			/** @var $item \Jigoshop\Entity\Order\Item */
			$product = $item->getProduct();

			if ($product->getStock()->getStatus() != StockStatus::IN_STOCK || !$product->getStock()->getStock() < $item->getQuantity()) {
				if (Integration::getOptions()->get('products.show_stock')) {
					$error = sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. We only have %d available at this time. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $product->getName(), $product->getStock()->getStock());
				} else {
					$error = sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $product->getName());
				}

				Integration::getMessages()->addError($error);
				return false;
			}
		}

		return true;
	}

	public static function get_price_per_tax_class_ex_tax()
	{
		return self::$price_per_tax_class_ex_tax;
	}

	public static function get_cart_contents_total_excluding_tax()
	{
		return self::$cart_contents_total_ex_tax;
	}

	public static function get_total($for_display = true)
	{
		$cart = Integration::getCart();
		if ($for_display) {
			return Product::formatPrice($cart->getTotal());
		} else {
			return Product::formatNumericPrice($cart->getTotal());
		}
	}

	public static function get_cart_total()
	{
		return Product::formatPrice(self::$cart_contents_total);
	}

	public static function get_total_cart_tax_without_shipping_tax()
	{
		return Integration::getCart()->getTotalTax();
	}

	public static function get_tax_for_display($tax_class)
	{
		$return = false;
		$cart = Integration::getCart();
		$taxes = $cart->getTax();

		if (isset($taxes[$tax_class]) && $taxes[$tax_class] > 0) {
			$service = Integration::getTaxService();
			$return = $service->getLabel($tax_class, $cart->getCustomer());

			if (!Integration::getOptions()->get('shipping.calculator') && Integration::getPages()->isCart()) {
				$return .= '<small>'.sprintf(__('estimated for: %s', 'jigoshop'), \Jigoshop\Helper\Country::getName($cart->getCustomer()->getTaxAddress()->getCountry())).'</small>';
			}
		}

		return $return;
	}

	public static function get_tax_amount($tax_class, $with_price = true)
	{
		$value = 0.0;
		$cart = Integration::getCart();
		$taxes = $cart->getTax();

		if (isset($tax_class)) {
			$value = $taxes[$tax_class];
		}

		return $with_price ? Product::formatPrice($value) : Product::formatNumericPrice($value);
	}

	/**
	 * @return array List of coupons applied to the cart.
	 */
	public static function get_coupons()
	{
		return array_map(function($coupon){
			/** @var $coupon \Jigoshop\Entity\Coupon */
			return $coupon->getCode();
		}, Integration::getCart()->getCoupons());
	}

	public static function get_tax_rate($tax_class)
	{
		return Integration::getTaxService()->getRate($tax_class, Integration::getCart()->getCustomer());
	}

	public static function show_retail_price()
	{
		// TODO: Implement after compound taxes introduction
//		if (self::get_options()->get('jigoshop_calc_taxes') != 'yes') {
//			return false;
//		}
//
//		return (jigoshop_cart::has_compound_tax() || jigoshop_cart::tax_after_coupon());
		return false;
	}

	public static function has_compound_tax()
	{
		// TODO: Implement after compound taxes introduction
//		return self::$tax->is_compound_tax();
		return false;
	}

	public static function tax_after_coupon()
	{
		// TODO: Taxes after coupon illegal?
		return false;
//		if (self::get_options()->get('jigoshop_calc_taxes') != 'yes' || !jigoshop_cart::get_total_discount()) {
//			return false;
//		}
//
//		return (self::get_options()->get('jigoshop_tax_after_coupon') == 'yes');
	}

	public static function get_total_discount($with_price = true)
	{
		$value = Integration::getCart()->getDiscount();
		return $with_price ? Product::formatPrice($value) : Product::formatNumericPrice($value);
	}

	/**
	 * Shipping total after calculation.
	 */
	public static function get_total_tax_rate()
	{
		// TODO: What is this? :E
//		return self::$tax->get_total_tax_rate(self::$subtotal);
	}

	public static function get_taxes_as_array($taxes_as_string)
	{
		// TODO: What to do here?
//		return self::$tax->get_taxes_as_array($taxes_as_string, 100);
	}

	public static function get_taxes_as_string()
	{
		// TODO: What to do here?
//		return self::$tax->get_taxes_as_string();
	}

	public static function get_tax_divisor()
	{
		// TODO: What to do here?
//		return self::$tax->get_tax_divisor();
	}

	public static function get_cart_shipping_title()
	{
		// Shipping method is provided differently now
		return '';
	}

	/**
	 * Applies a coupon code
	 *
	 * @param string $coupon_code The code to apply
	 * @return bool True if the coupon is applied, false if it does not exist or cannot be applied
	 */
	public static function add_discount($coupon_code)
	{
		try {
			$cart = Integration::getCart();
			$coupons = Integration::getCouponService()->getByCodes(array($coupon_code));
			foreach ($coupons as $coupon) {
				/** @var $coupon \Jigoshop\Entity\Coupon */
				$cart->addCoupon($coupon);
			}
			Integration::getMessages()->addError(__('Discount coupon applied successfully.', 'jigoshop'));

			return true;
		} catch (\Jigoshop\Exception $e) {
			Integration::getMessages()->addError($e->getMessage());

			return false;
		}
	}

	public static function has_discount($code)
	{
		return in_array($code, self::get_coupons());
	}

	public static function has_free_shipping_coupon()
	{
		foreach (Integration::getCart()->getCoupons() as $coupon) {
			/** @var $coupon \Jigoshop\Entity\Coupon */
			if ($coupon->isFreeShipping()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets and formats a list of cart item data + variations for display on the frontend
	 *
	 * @param $cart_item
	 * @param bool $flat
	 * @return string
	 */
	public static function get_item_data($cart_item, $flat = false)
	{
		$return = '';
		$item = Integration::getCart()->getItem($cart_item['__key']);

		$product = $item->getProduct();
		if (!$flat) {
			if ($product instanceof \Jigoshop\Entity\Product\Variable) {
				$variation = $product->getVariation($item->getMeta('variation_id')->getValue());
				$return .= Product::getVariation($variation, $item);
			}

			$return .= '<dl>';
		}

		// Other data - returned as array with name/value values
		$other_data = apply_filters('jigoshop_get_item_data', array(), $cart_item);
		if (is_array($other_data) && sizeof($other_data) > 0) {
			$data_list = array();
			foreach ($other_data as $data) {
				$display_value = (!empty($data['display']))
					? $data['display']
					: $data['value'];

				$data_list[] = $flat
					? sprintf('%s: %s<br />', $data['name'], $display_value)
					: sprintf('<dt>%s:</dt> <dd>%s</dd><br />', $data['name'], $display_value);
			}

			$return .= $flat ? implode(', ', $data_list) : implode('', $data_list);
		}

		if (!$flat) {
			$return .= '</dl>';
		}

		return $return;
	}
}
