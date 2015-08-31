<?php

/**
 * Cart Class
 * The JigoShop cart class stores cart data and active coupons as well as handling customer sessions and some cart related urls.
 * The cart class also has a price calculation function which calls upon other classes to calcualte totals.
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */
class jigoshop_cart extends Jigoshop_Singleton
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
	private static $tax;

	/** constructor */
	protected function __construct()
	{
		self::get_cart_from_session();
		self::$applied_coupons = array();

		if (isset(jigoshop_session::instance()->coupons)) {
			self::$applied_coupons = jigoshop_session::instance()->coupons;
		}

		self::$tax = new jigoshop_tax(100); //initialize tax on the cart with divisor of 100
		self::calculate_cart_total();
	}

	/** Gets the cart data from the PHP session */
	public static function get_cart_from_session()
	{
		self::$cart_contents = (array)jigoshop_session::instance()->cart;
	}

	private static function calculate_cart_total()
	{
		self::reset_totals();

		/* No items so nothing to calculate. Make sure applied coupons and session data are reset. */
		if (empty(self::$cart_contents)) {
			self::empty_cart();

			return;
		}

		// Loop through each product in the cart
		if (!empty(self::$cart_contents)) {
			foreach (self::$cart_contents as $key => $values) {
				/** @var jigoshop_product $_product */
				$_product = $values['data'];
				if (!($_product instanceof jigoshop_product)) {
					unset(self::$cart_contents[$key]);
					continue;
				}

				self::$cart_contents_count += $values['quantity'];

				// get actual applied discounts from coupons for this product
				$current_product_discount = self::calculate_product_discounts_total($_product, $values);
				self::$discount_total += $current_product_discount;

				// this should never be less that 0.00 due to 'calculate_product_discounts_total()'
				$discounted_item_price = $_product->get_price_excluding_tax() * $values['quantity'] - $current_product_discount;
				$total_item_price = $_product->get_price() * $values['quantity'] * 100;

				if (self::get_options()->get('jigoshop_calc_taxes') == 'yes') {
					if ($_product->is_taxable()) {
						$shippable = jigoshop_shipping::is_enabled() && $_product->requires_shipping();

						self::$tax->set_is_shipable($shippable);

						$price_includes_tax =
							self::get_options()->get('jigoshop_tax_after_coupon') == 'yes'
							&& $current_product_discount > 0
								? false
								: self::get_options()->get('jigoshop_prices_include_tax') == 'yes';

						$product_discounted_price = self::get_options()->get('jigoshop_tax_after_coupon') == 'yes'
						&& $current_product_discount > 0
							? $discounted_item_price * 100
							: $total_item_price;

						$tax_classes_applied = self::$tax->calculate_tax_amounts(
							$product_discounted_price,
							$_product->get_tax_classes(),
							$price_includes_tax
						);

						// reason we cannot use get_applied_tax_classes is because we may not have applied all tax classes for this product.
						// get_applied_tax_classes will return all of the tax classes that have been applied on all products
						$item_tax = 0.0;
						foreach ($tax_classes_applied as $tax_class) {
							$price_ex_tax = $_product->get_price_excluding_tax() * $values['quantity'];
							$item_tax += self::$tax->calc_tax($price_ex_tax, self::$tax->get_rate($tax_class), false);

							if (isset(self::$price_per_tax_class_ex_tax[$tax_class])) {
								self::$price_per_tax_class_ex_tax[$tax_class] += $price_ex_tax;
							} else {
								self::$price_per_tax_class_ex_tax[$tax_class] = $price_ex_tax;
							}
						}

						if (self::get_options()->get('jigoshop_prices_include_tax') == 'yes') {
							if (self::get_options()->get('jigoshop_tax_after_coupon') == 'yes' && $discounted_item_price >= 0) {
								$total_item_price += $item_tax;
							}
						}
					}
				}

				$total_item_price = $total_item_price / 100;

				/* Apply weight only to non-downloadable products. */
				if ($_product->product_type != 'downloadable') {
					self::$cart_contents_weight = self::$cart_contents_weight + ($_product->get_weight() * $values['quantity']);
					self::$cart_contents_total_ex_dl = self::$cart_contents_total_ex_dl + $total_item_price;
				} else {
					self::$cart_dl_count = self::$cart_dl_count + $values['quantity'];
				}

				self::$cart_contents_total += $total_item_price;
				self::$cart_contents_total_ex_tax += $_product->get_price_excluding_tax() * $values['quantity'];
			}
		}
	}

	/** reset all Cart totals */
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
		self::$tax->init_tax();
		self::$price_per_tax_class_ex_tax = array(); /* currently used with norway */
		jigoshop_shipping::reset_shipping();
	}

	/** Empty the cart */
	public static function empty_cart()
	{
		self::$cart_contents = array();
		self::$applied_coupons = array();
		self::reset_totals();
		unset(jigoshop_session::instance()->cart);
		unset(jigoshop_session::instance()->coupons);
		unset(jigoshop_session::instance()->chosen_shipping_method_id);
		unset(jigoshop_session::instance()->selected_rate_id);
	}

	/**
	 * Calculate total 'product fixed' and 'product percentage' discounts
	 *
	 * @param  jigoshop_product $_product the product we are working with
	 * @param  array $values the cart values for this product
	 * @return float|int|mixed|void $current_product_discount
	 */
	private static function calculate_product_discounts_total($_product, $values)
	{
		$current_product_discount = 0;

		if (!empty(self::$applied_coupons)) {
			foreach (self::$applied_coupons as $code) {
				$coupon_discount = 0;
				$coupon = JS_Coupons::get_coupon($code);

				if (!JS_Coupons::is_valid_coupon_for_product($code, $values)) {
					continue;
				}

				$price = self::get_options()->get('jigoshop_tax_after_coupon') == 'yes'
					? $_product->get_price_excluding_tax()
					: $_product->get_price_with_tax();

				switch ($coupon['type']) {
					case 'fixed_product' :
						$coupon_discount = apply_filters('jigoshop_coupon_product_fixed_amount', $coupon['amount'], $coupon) * $values['quantity'];
						if ($coupon_discount > $price * $values['quantity']) {
							$coupon_discount = $price * $values['quantity'];
						}
						break;
					case 'percent_product' :
						$coupon_discount = ($price * $values['quantity'] / 100) * $coupon['amount'];
						break;
				}

				$current_product_discount += $coupon_discount;
			}
		}

		return $current_product_discount;
	}

	/**
	 * @return bool Is the cart empty?
	 */
	public static function is_empty()
	{
		return empty(self::$cart_contents);
	}

	/**
	 * @return bool Customer has applied any discount coupons.
	 */
	public static function has_coupons()
	{
		return !empty(jigoshop_cart::$applied_coupons);
	}

	/**
	 * Add a product to the cart
	 *
	 * @param string $product_id contains the id of the product to add to the cart
	 * @param int|string $quantity contains the quantity of the item to add
	 * @param int|string $variation_id
	 * @param array $variation attribute values
	 * @return bool
	 */
	public static function add_to_cart($product_id, $quantity = 1, $variation_id = '', $variation = array())
	{
		if ($quantity < 0) {
			$quantity = 0;
		}

		// Load cart item data - may be added by other plugins
		$cart_item_data = (array)apply_filters('jigoshop_add_cart_item_data', array(), $product_id);

		$cart_id = self::generate_cart_id($product_id, $variation_id, $variation, $cart_item_data);
		$cart_item_key = self::find_product_in_cart($cart_id);

		//  prevents adding non-valid products to the cart
		$this_post = get_post($product_id);
		if ($this_post->post_type != 'product') {
			jigoshop::add_error(__('You cannot add this item to your Cart as it does not appear to be a valid Product.', 'jigoshop'));

			return false;
		}

		//  create a product record to work from
		if (empty($variation_id)) {
			$product = new jigoshop_product($product_id);
		} else {
			$product = new jigoshop_product_variation($variation_id);
		}

		//  product with a given ID doesn't exists
		if (empty($product)) {
			return false;
		}

		//  prevents adding products with no price to the cart
		if ($product->get_price() === '') {
			jigoshop::add_error(__('You cannot add this product to your cart because its price is not yet announced', 'jigoshop'));

			return false;
		}

		//  products newly added to the Cart will not have a $cart_item_key, use 0 quantity
		$in_cart_qty = !empty($cart_item_key) ? self::$cart_contents[$cart_item_key]['quantity'] : 0;

		//  prevents adding products to the cart without enough quantity on hand
		if (!$product->has_enough_stock($quantity + $in_cart_qty)) {
			jigoshop::add_error(sprintf(__('We are sorry. We do not have enough "%s" to fill your request.', 'jigoshop'), $product->get_title()));

			if (self::get_options()->get('jigoshop_show_stock') == 'yes') {
				if ($in_cart_qty > 0) {
					jigoshop::add_error(sprintf(__('You have %d of them in your Cart and we have %d available at this time.', 'jigoshop'), $in_cart_qty, $product->get_stock()));
				} else {
					jigoshop::add_error(sprintf(__('There are only %d left in stock.', 'jigoshop'), $product->get_stock()));
				}
			}

			return false;
		}

		//  if product is already in the cart change its quantity
		if ($cart_item_key) {
			$quantity = (int)$quantity + self::$cart_contents[$cart_item_key]['quantity'];
			self::set_quantity($cart_item_key, apply_filters('jigoshop_cart_item_quantity', $quantity, $product, $cart_item_key));
		} else {
			// otherwise add new item to the cart
			self::$cart_contents[$cart_id] = apply_filters('jigoshop_add_cart_item', array(
				'data' => $product,
				'product_id' => $product_id,
				'variation' => $variation,
				'variation_id' => $variation_id,
				'quantity' => (int)$quantity,
				'unit_price' => 0,
				'tax' => 0,
				'discount' => 0,
				'price_includes_tax' => self::get_options()->get('jigoshop_prices_include_tax')
			), $cart_item_data);
		}

		self::set_session();

		return true;
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
		$id_parts = array($product_id);

		if ($variation_id) {
			$id_parts[] = $variation_id;
		}

		if (is_array($variation)) {
			$variation_key = '';
			foreach ($variation as $key => $value) {
				$variation_key .= trim($key).trim($value);
			}
			$id_parts[] = $variation_key;
		}

		if (is_array($cart_item_data)) {
			$cart_item_data_key = '';
			foreach ($cart_item_data as $key => $value) {
				if (is_array($value)) {
					$value = http_build_query($value);
				}
				$cart_item_data_key .= trim($key).trim($value);
			}
			$id_parts[] = $cart_item_data_key;
		}

		return md5(implode('_', $id_parts));
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
			foreach (self::$cart_contents as $cart_item_key => $cart_item) {
				if ($cart_item_key == $cart_id) {
					return $cart_item_key;
				}
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
		if ($quantity == 0 || $quantity < 0) {
			$_product = self::$cart_contents[$cart_item];
			/** @var jigoshop_product $product */
			$product = $_product['data'];
			if (!empty(self::$applied_coupons)) {
				foreach (self::$applied_coupons as $key => $code) {
					$coupon = JS_Coupons::get_coupon($code);
					if (JS_Coupons::is_valid_coupon_for_product($code, $_product)) {
						if ($coupon['type'] == 'fixed_product') {
							self::$discount_total = self::$discount_total - (apply_filters('jigoshop_coupon_product_fixed_amount', $coupon['amount'], $coupon) * $_product['quantity']);
							unset(self::$applied_coupons[$key]);
						} else if ($coupon['type'] == 'percent_product') {
							self::$discount_total = self::$discount_total - (($product->get_price() * $_product['quantity'] / 100) * $coupon['amount']);
							unset(self::$applied_coupons[$key]);
						}
					}
				}
			}
			unset(self::$cart_contents[$cart_item]);
		} else {
			if (!empty(self::$applied_coupons)) {
				foreach (self::$applied_coupons as $key => $code) {
					if (!self::is_valid_coupon($code)) {
						unset(self::$applied_coupons[$key]);
					}
				}
			}
			self::$cart_contents[$cart_item]['quantity'] = $quantity;
		}

		self::set_session();
	}

	/**
	 * @param $coupon string Coupon code to check.
	 * @return bool Whether specified code is valid for current cart.
	 */
	public static function is_valid_coupon($coupon)
	{
		$coupon = JS_Coupons::get_coupon($coupon);
		if (!$coupon) {
			jigoshop::add_error(__('Coupon does not exist or is no longer valid!', 'jigoshop'));

			return false;
		}

		$payment_method = !empty($_POST['payment_method']) ? $_POST['payment_method'] : '';
		$pay_methods = (array)$coupon['pay_methods'];

		/* Whether the order has a valid payment method which the coupon requires. */
		if (!empty($pay_methods) && !empty($payment_method) && !in_array($payment_method, $pay_methods)) {
			jigoshop::add_error(sprintf(__("The coupon '%s' is invalid with that payment method!", 'jigoshop'), $coupon['code']));

			return false;
		}

		/* Subtotal minimum or maximum. */
		if (!empty($coupon['order_total_min']) || !empty($coupon['order_total_max'])) {
			/* Can't use the jigoshop_cart::get_cart_subtotal() method as it's not ready at this point yet. */
			$subtotal = self::$cart_contents_total;

			$order_total_max = apply_filters('jigoshop_coupon_order_total_max', $coupon['order_total_max'], $coupon);
			if (!empty($coupon['order_total_max']) && $subtotal > $order_total_max) {
				jigoshop::add_error(sprintf(__('Your subtotal does not match the <strong>maximum</strong> order total requirements of %.2f for coupon "%s" and it has been removed.', 'jigoshop'), $order_total_max, $coupon['code']));

				return false;
			}

			$order_total_min = apply_filters('jigoshop_coupon_order_total_min', $coupon['order_total_min'], $coupon);
			if (!empty($coupon['order_total_min']) && $subtotal < $order_total_min) {
				jigoshop::add_error(sprintf(__('Your subtotal does not match the <strong>minimum</strong> order total requirements of %.2f for coupon "%s" and it has been removed.', 'jigoshop'), $order_total_min, $coupon['code']));

				return false;
			}
		}

		// Check if coupon products are in cart
		if (!jigoshop_cart::has_valid_products_for_coupon($coupon)) {
			jigoshop::add_error(__('No products in your cart match that coupon!', 'jigoshop'));

			return false;
		}

		return true;
	}

	/**
	 * @param $coupon array Coupon object to validate.
	 * @return bool Whether cart contains at least one product valid for specified coupon.
	 */
	public static function has_valid_products_for_coupon($coupon)
	{
		/* Look through each product in the cart for a valid coupon. */
		foreach (self::$cart_contents as $product) {
			if (JS_Coupons::is_valid_coupon_for_product($coupon['code'], $product)) {
				return true;
			}
		}

		return false;
	}

	/** sets the php session data for the cart and coupon */
	public static function set_session()
	{
		// we get here from cart additions, quantity adjustments, and coupon additions
		// reset any chosen shipping methods as these adjustments can effect shipping (free shipping)
		unset(jigoshop_session::instance()->chosen_shipping_method_id);
		unset(jigoshop_session::instance()->selected_rate_id); // calculable shipping

		jigoshop_session::instance()->cart = apply_filters('jigoshop_cart_set_session', self::$cart_contents);
		jigoshop_session::instance()->coupons = self::$applied_coupons;

		self::calculate_totals();
	}

	/** calculate totals for the items in the cart */
	public static function calculate_totals()
	{
		// extracted cart totals so that the constructor can call it, rather than
		// this full method. Cart totals are needed for cart widget and themes.
		// Don't want to call shipping api's multiple times per page load
		self::calculate_cart_total();

		// Cart Shipping
		if (self::needs_shipping()) {
			jigoshop_shipping::calculate_shipping(self::$tax);
		} else {
			jigoshop_shipping::reset_shipping();
		}

		self::$shipping_total = jigoshop_shipping::get_total();
		$shipping_method = jigoshop_shipping::get_chosen_method();

		if (self::get_options()->get('jigoshop_calc_taxes') == 'yes') {
			self::$tax->calculate_shipping_tax(self::$shipping_total, $shipping_method);
			self::$shipping_tax_total = self::$tax->get_total_shipping_tax_amount();
		}

		// Subtotal
		self::$subtotal_ex_tax = self::$cart_contents_total_ex_tax;
		self::$subtotal = self::$cart_contents_total;

		if (self::get_options()->get('jigoshop_calc_taxes') == 'yes' && !self::$tax->get_total_shipping_tax_amount()) {
			foreach (self::get_applied_tax_classes() as $tax_class) {
				if (!self::is_not_compounded_tax($tax_class)) { //tax compounded
					$discount = (self::get_options()->get('jigoshop_tax_after_coupon') == 'yes' ? self::$discount_total : 0);
					self::$tax->update_tax_amount($tax_class, (self::$subtotal_ex_tax - $discount + self::$tax->get_non_compounded_tax_amount() + self::$shipping_total) * 100);
				}
			}
		}

		self::$total = self::get_cart_subtotal(false) + self::get_cart_shipping_total(false);
		if (self::get_options()->get('jigoshop_calc_taxes') == 'yes'
			&& self::get_options()->get('jigoshop_prices_include_tax') == 'no'
			&& self::get_options()->get('jigoshop_tax_after_coupon') == 'no'
		) {
			self::$total += self::$tax->get_non_compounded_tax_amount() + self::$tax->get_compound_tax_amount();
		}

		// calculate any cart wide discounts from coupons
		$total_product_discounts = self::$discount_total;
		self::$discount_total = $total_cart_discounts = $temp = 0;

		if (self::get_options()->get('jigoshop_tax_after_coupon') == 'yes') {
			// we need products and shipping with tax out
			$total_cart_discounts = round(self::calculate_cart_discounts_total(self::$cart_contents_total_ex_tax + self::get_cart_shipping_total(false, true)), 2);

			if ($total_cart_discounts > 0) {
				$total_to_use = self::$cart_contents_total_ex_tax + self::$shipping_total;
				if ($total_cart_discounts > $total_to_use) {
					$total_cart_discounts = $total_to_use - $total_product_discounts;
				}

				$total_tax = self::$tax->get_non_compounded_tax_amount() + self::$tax->get_compound_tax_amount() - self::$tax->get_total_shipping_tax_amount();
				if($total_tax > 0){
					foreach (self::get_applied_tax_classes() as $tax_class) {
						$rate = self::$tax->get_rate($tax_class);
						$tax = self::$tax->calc_tax(self::$price_per_tax_class_ex_tax[$tax_class], $rate, false);

						$discounts = 0.0;
						$total_tax_part = $tax / $total_tax;

						// Lower tax by coupon amounts
						foreach (jigoshop_cart::get_coupons() as $code) {
							$coupon = JS_Coupons::get_coupon($code);
							switch ($coupon['type']) {
								case 'fixed_cart':
									$discounts += self::$tax->calc_tax($coupon['amount']*$total_tax_part, $rate, false);
									break;
								case 'percent':
									$discounts += self::$tax->calc_tax($coupon['amount'] * self::$price_per_tax_class_ex_tax[$tax_class]/($total_tax_part*100), $rate, false);
									$discounts += $coupon['amount'] * self::$tax->get_shipping_tax($tax_class) / 100;
									break;
							}
						}

						$tax -= $discounts;
						self::$tax->update_tax_amount($tax_class, $tax * 100, false, true);
					}
				}

			// check again in case tax calcs are disabled
				$total_discounts = $total_cart_discounts + $total_product_discounts;
				if ($total_discounts > $total_to_use) {
					$total_cart_discounts = $total_to_use - $total_product_discounts;
				}
			}

			foreach (self::get_applied_tax_classes() as $tax_class) {
				$temp += self::$tax->get_tax_amount($tax_class);
			}

			if (self::get_options()->get('jigoshop_prices_include_tax') == 'no') {
				self::$total += $temp;
			} else {
				self::$total = self::$cart_contents_total_ex_tax + self::$shipping_total + $temp;
			}
		} else { //  Taxes are applied before coupons, 'jigoshop_tax_after_coupon' == 'no'
			if (self::get_options()->get('jigoshop_prices_include_tax') == 'no') {
				$total_cart_discounts = self::calculate_cart_discounts_total(self::$total);
				if ($total_cart_discounts > self::$total) {
					$total_cart_discounts = self::$total - $total_product_discounts;
				}
			} else {
				$total_cart_discounts = self::calculate_cart_discounts_total(self::$cart_contents_total_ex_tax + self::$shipping_total);
				if ($total_cart_discounts > 0) {
					// with an initial discount, recalc taxes and get a proper discount
					foreach (self::get_applied_tax_classes() as $tax_class) {
						$rate = self::$tax->get_rate($tax_class);
						$tax = self::$tax->calc_tax(self::$cart_contents_total_ex_tax, $rate, false);
						self::$tax->update_tax_amount($tax_class, $tax * 100, false, true);
						$temp += self::$tax->get_tax_amount($tax_class);
					}

					$total_to_use = self::$cart_contents_total_ex_tax + self::$shipping_total + $temp;
					$total_cart_discounts = self::calculate_cart_discounts_total($total_to_use);

					if ($total_cart_discounts > $total_to_use) {
						$total_cart_discounts = $total_to_use - $total_product_discounts;
					}
				}
			}
		}

		// set the final discount
		self::$discount_total = $total_cart_discounts + $total_product_discounts;

		// adjust the grand total after all discounts
		self::$total -= self::$discount_total;
		if (self::$total < 0) {
			self::$total = 0;
		}

		// with everything calculated, check that coupons depending on cart totals are still valid
		// if they are not, remove them and recursively re-calculate everything all over again.
		$recalc = false;
		if (!empty(self::$applied_coupons)) {
			foreach (self::$applied_coupons as $key => $code) {
				if (!self::is_valid_coupon($code)) {
					unset(self::$applied_coupons[$key]);
					jigoshop_session::instance()->coupons = self::$applied_coupons;
					$recalc = true;
				}
			}
		}
		if ($recalc) {
			self::calculate_totals();
		}
	}

	/** looks through the cart to see if shipping is actually required */
	public static function needs_shipping()
	{
		if (!jigoshop_shipping::is_enabled() || !is_array(self::$cart_contents)) {
			return false;
		}

		foreach (self::$cart_contents as $values) {
			/** @var jigoshop_product $_product */
			$_product = $values['data'];
			if ($_product->requires_shipping()) {
				return true;
			}
		}

		return false;
	}

	public static function get_applied_tax_classes()
	{
		// Do not display taxes if customer country is not set
		if (jigoshop_tax::get_customer_country() == '') {
			return array();
		}

		return self::$tax->get_applied_tax_classes();
	}

	public static function is_not_compounded_tax($tax_class)
	{
		return self::$tax->is_tax_non_compounded($tax_class);
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
		$discount = self::$discount_total;
		$subtotal = self::$subtotal;
		$tax_label = 0; // use with jigoshop_price. 0 for no label, 1 for ex. tax, 2 for inc. tax

		/**
		 * Tax calculation turned ON.
		 */
		if (self::get_options()->get('jigoshop_calc_taxes') == 'yes') {
			// for final Orders in the Admin we always need tax out
			if ($order_exclude_tax) {
				$subtotal = self::get_options()->get('jigoshop_prices_include_tax') == 'yes' ? self::$subtotal_ex_tax : $subtotal;
				if(self::get_options()->get('jigoshop_show_prices_with_tax') == 'yes') {
					$tax_label = 1; //ex. tax
				}
			} else {
				if (self::get_options()->get('jigoshop_prices_include_tax') == 'yes') {
					$tax_label = 2; //inc. tax
				} else {
					$subtotal = self::$subtotal_ex_tax;
					if(self::get_options()->get('jigoshop_show_prices_with_tax') == 'yes') {
						$tax_label = 1; //inc. tax
					}
				}
			}
		}

		// Don't show the discount bit in the subtotal because discount will be calculated after taxes
		// thus in the grand total (not the subtotal). */
		if (self::get_options()->get('jigoshop_tax_after_coupon') == 'yes') {
			$discount = 0;
		}

		/* Display totals with discount & shipping applied? */
		// This is only 'true' with the 'Retail Price' displays on Cart, Checkout, View Order
		// Someone should explain why this is used instead of 'Subtotal'
		if ($apply_discount_and_shipping) {
			$subtotal = $subtotal + jigoshop_cart::get_cart_shipping_total(false);
			$subtotal = ($discount > $subtotal) ? $subtotal : $subtotal - $discount;
		}

		/* Return a pretty number or just the float. */
		$return = $for_display ? jigoshop_price($subtotal, array('ex_tax_label' => $tax_label)) : number_format($subtotal, 2, '.', '');

		return $return;
	}

	public static function get_cart_shipping_total($for_display = true, $order_exclude_tax = false)
	{
		/* Quit early if there is no shipping label. */
		if (!jigoshop_shipping::get_label()) {
			return false;
		}

		// Do not display taxes if shipping country is not set
		if (jigoshop_customer::get_shipping_country() == '') {
			return false;
		}

		/* Shipping price is 0.00. */
		if (jigoshop_shipping::get_total() <= 0) {
			return ($for_display ? __('Free!', 'jigoshop') : 0);
		}

		/* Not calculating taxes. */
		if (self::get_options()->get('jigoshop_calc_taxes') == 'no') {
			return ($for_display ? jigoshop_price(self::$shipping_total) : number_format(self::$shipping_total, 2, '.', ''));
		}

		if (self::get_options()->get('jigoshop_prices_include_tax') == 'no' || $order_exclude_tax) {
			$return = ($for_display ? jigoshop_price(self::$shipping_total) : number_format(self::$shipping_total, 2, '.', ''));

			if (self::$shipping_tax_total > 0 && $for_display) {
				$return .= ' <small>'.__('(ex. tax)', 'jigoshop').'</small>';
			}
		} else {
			$return = ($for_display ? jigoshop_price(self::$shipping_total + self::$shipping_tax_total) : number_format(self::$shipping_total + self::$shipping_tax_total, 2, '.', ''));
			if (self::$shipping_tax_total > 0 && $for_display) {
				$return .= ' <small>'.__('(inc. tax)', 'jigoshop').'</small>';
			}
		}

		return $return;
	}

	/**
	 * Calculate total 'cart fixed' and 'cart percentage' discounts
	 *
	 * @param  float $total_to_use the cart total price to base discounts on, tax in or out usually
	 * @return float|int|mixed|void $cart_discount  a total monetary amount from the applied cart discount coupons
	 */
	private static function calculate_cart_discounts_total($total_to_use)
	{
		$cart_discount = 0;
		if (!empty(self::$applied_coupons)) {
			foreach (self::$applied_coupons as $code) {
				if ($coupon = JS_Coupons::get_coupon($code)) {

					switch ($coupon['type']) {
						case 'fixed_cart' :
							$cart_discount += apply_filters('jigoshop_coupon_cart_fixed_amount', $coupon['amount'], $coupon);
							break;
						case 'percent' :
							$cart_discount += ($total_to_use / 100) * $coupon['amount'];
							break;
					}
				}
			}
		}

		return $cart_discount;
	}

	/**
	 * @return array List of coupons applied to the cart.
	 */
	public static function get_coupons()
	{
		if (!is_array(self::$applied_coupons)) {
			self::$applied_coupons = array();
		}

		return self::$applied_coupons;
	}

	public static function get_discount_subtotal()
	{
		return self::get_subtotal() + self::get_shipping_total() - self::$discount_total;
	}

	public static function get_subtotal()
	{
		do_action('jigoshop_calculate_totals');
		return self::get_options()->get('jigoshop_prices_include_tax') == 'yes' ? self::$subtotal_ex_tax : self::$subtotal;
	}

	public static function get_shipping_total()
	{
		return self::$shipping_total;
	}

	public static function get_shipping_tax()
	{
		return self::$shipping_tax_total;
	}

	/**
	 * @deprecated Use jigoshop_cart::is_valid_coupon()
	 * @param $coupon_code
	 * @return bool
	 */
	public static function valid_coupon($coupon_code)
	{
		return self::is_valid_coupon($coupon_code);
	}

	/**
	 * @deprecated Use jigoshop_cart::has_valid_products_for_coupon()
	 * @param $coupon
	 * @return bool
	 */
	public static function has_valid_coupon_for_products($coupon)
	{
		return self::has_valid_products_for_coupon($coupon);
	}

	/**
	 * Returns the contents of the cart
	 *
	 * @return   array  cart_contents
	 */
	public static function get_cart()
	{
		if (empty(self::$cart_contents)) {
			self::get_cart_from_session();
		}

		return self::$cart_contents;
	}

	/** gets the url to the cart page */
	public static function get_cart_url()
	{
		$cart_page_id = jigoshop_get_page_id('cart');
		if ($cart_page_id) {
			return apply_filters('jigoshop_get_cart_url', get_permalink($cart_page_id));
		}

		return '';
	}

	// will return an empty array if taxes are not calculated

	/** gets the url to the checkout page */
	public static function get_checkout_url()
	{
		$checkout_page_id = jigoshop_get_page_id('checkout');
		if ($checkout_page_id) {
			$url = get_permalink($checkout_page_id);
			if (is_ssl()) {
				$url = str_replace('http:', 'https:', $url);
			}

			return apply_filters('jigoshop_get_checkout_url', $url);
		}

		return '';
	}

	/** gets the url to the shop page */
	public static function get_shop_url()
	{
		$shop_page_id = jigoshop_get_page_id('shop_redirect');
		if ($shop_page_id) {
			return apply_filters('jigoshop_get_shop_page_id', get_permalink($shop_page_id));
		}

		return '';
	}

	/** gets the url to remove an item from the cart
	 *
	 * @param $cart_item_key
	 * @return mixed|string|void
	 */
	public static function get_remove_url($cart_item_key)
	{
		$cart_page_id = jigoshop_get_page_id('cart');
		if ($cart_page_id) {
			return apply_filters('jigoshop_get_remove_url', jigoshop::nonce_url('cart', add_query_arg('remove_item', $cart_item_key, get_permalink($cart_page_id))));
		}

		return '';
	}

	/** Sees if we need a shipping address */
	public static function ship_to_billing_address_only()
	{
		return (self::get_options()->get('jigoshop_ship_to_billing_address_only') == 'yes');
	}

	/** looks at the totals to see if payment is actually required */
	public static function needs_payment()
	{
		if (self::$total > 0) {
			return true;
		}

		return false;
	}

	public static function check_cart_item_stock()
	{
		foreach (self::$cart_contents as $values) {
			/** @var jigoshop_product $_product */
			$_product = $values['data'];

			if (!$_product->is_in_stock() || ($_product->managing_stock() && !$_product->has_enough_stock($values['quantity']))) {
				$error = new WP_Error();
				$errormsg = self::get_options()->get('jigoshop_show_stock') == 'yes'
					? sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. We only have %d available at this time. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $_product->get_title(), $_product->get_stock())
					: sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order. Please edit your cart and try again. We apologize for any inconvenience caused.', 'jigoshop'), $_product->get_title());

				$error->add('out-of-stock', $errormsg);

				return $error;
			}
		}

		return true;
	}

	public static function get_price_per_tax_class_ex_tax()
	{
		return self::$price_per_tax_class_ex_tax;
	}

	/** gets cart contents total excluding tax. Shipping methods use this, and the contents total are calculated ahead of shipping */
	public static function get_cart_contents_total_excluding_tax()
	{
		return self::$cart_contents_total_ex_tax;
	}

	/** gets the total (after calculation)
	 *
	 * @param bool $for_display
	 * @return mixed|string|void
	 */
	public static function get_total($for_display = true)
	{
		return ($for_display ? jigoshop_price(self::$total) : number_format(self::$total, 2, '.', ''));
	}

	/** gets the cart contents total (after calculation) */
	public static function get_cart_total()
	{
		return jigoshop_price(self::$cart_contents_total);
	}

	// after calculation. Used with admin pages only

	public static function get_total_cart_tax_without_shipping_tax()
	{
		return self::$tax->get_non_compounded_tax_amount() + self::$tax->get_compound_tax_amount() - self::$shipping_tax_total;
	}

	public static function get_tax_for_display($tax_class)
	{
		$return = false;

		if ((jigoshop_cart::get_tax_amount($tax_class, false) > 0 && jigoshop_cart::get_tax_rate($tax_class) > 0)
			|| jigoshop_cart::get_tax_rate($tax_class) !== false
		) {
			$return = self::$tax->get_tax_class_for_display($tax_class).' ('.(float)jigoshop_cart::get_tax_rate($tax_class).'%) ';

			// only show estimated tag when customer is on the cart page and no shipping calculator is enabled to be able to change country
			if (!jigoshop_shipping::show_shipping_calculator() && is_cart()) {
				$return .= '<small>'.sprintf(__('estimated for: %s', 'jigoshop'), jigoshop_countries::get_country(jigoshop_tax::get_customer_country())).'</small>';
			}
		}

		return $return;
	}

	public static function get_tax_amount($tax_class, $with_price = true)
	{
		$tax = self::$tax->get_tax_amount($tax_class);

		return ($with_price ? jigoshop_price($tax) : number_format($tax, 2, '.', ''));
	}

	public static function get_tax_rate($tax_class)
	{
		return self::$tax->get_tax_rate($tax_class);
	}

	public static function show_retail_price()
	{
		if (self::get_options()->get('jigoshop_calc_taxes') != 'yes') {
			return false;
		}

		return (jigoshop_cart::has_compound_tax() || jigoshop_cart::tax_after_coupon());
	}

	public static function has_compound_tax()
	{
		return self::$tax->is_compound_tax();
	}

	public static function tax_after_coupon()
	{
		if (self::get_options()->get('jigoshop_calc_taxes') != 'yes' || !jigoshop_cart::get_total_discount()) {
			return false;
		}

		return (self::get_options()->get('jigoshop_tax_after_coupon') == 'yes');
	}

	/** Returns the total discount amount.
	 *
	 * @param bool $with_price
	 * @return bool|mixed|void
	 */
	public static function get_total_discount($with_price = true)
	{
		if (empty(self::$discount_total)) {
			return false;
		}

		return $with_price ? jigoshop_price(self::$discount_total) : self::$discount_total;
	}

	/**
	 * Shipping total after calculation.
	 */
	public static function get_total_tax_rate()
	{
		return self::$tax->get_total_tax_rate(self::$subtotal);
	}

	/**
	 * Title of the chosen shipping method.
	 *
	 * @param $taxes_as_string
	 * @return array
	 */
	public static function get_taxes_as_array($taxes_as_string)
	{
		return self::$tax->get_taxes_as_array($taxes_as_string, 100);
	}

	public static function get_taxes_as_string()
	{
		return self::$tax->get_taxes_as_string();
	}

	public static function get_tax_divisor()
	{
		return self::$tax->get_tax_divisor();
	}

	public static function get_cart_shipping_title()
	{
		// in this instance we want the title of the shipping method only. If no title is provided, use the label.
		$title = jigoshop_shipping::get_chosen_method_title();
		$label = ($title ? $title : jigoshop_shipping::get_label());
		if (!$label) {
			return false;
		}

		return sprintf(__('via %s', 'jigoshop'), $label);
	}

	/**
	 * Applies a coupon code
	 *
	 * @param string $coupon_code The code to apply
	 * @return bool True if the coupon is applied, false if it does not exist or cannot be applied
	 */
	public static function add_discount($coupon_code)
	{
		if (!self::is_valid_coupon($coupon_code)) {
			return false;
		}

		// Check for other individual_use coupons before adding this coupon.
		foreach (self::get_coupons() as $code) {
			$current = JS_Coupons::get_coupon($code);
			if ($current['individual_use']) {
				jigoshop::add_error(__("There is already an 'individual use' coupon on the Cart.  No other coupons can be added until it is removed.", 'jigoshop'));

				return false;
			}
		}

		$coupon = JS_Coupons::get_coupon($coupon_code);

		// Remove other coupons if this one is individual_use.
		if ($coupon['individual_use']) {
			if (!empty(self::$applied_coupons)) {
				jigoshop::add_error(__("This is an 'individual use' coupon.  All other discount coupons have been removed.", 'jigoshop'));
				self::$applied_coupons = array();
			}
		}

		// check if coupon is already applied and only add a new coupon
		if (!self::has_discount($coupon_code) && !empty($_POST['coupon_code'])) {
			self::$applied_coupons[] = $coupon_code;
		}


		// select free shipping method
		if($coupon['free_shipping']) {
			if(Jigoshop_Base::get_options()->get('jigoshop_select_free_shipping_method') == 'yes') {
				jigoshop_session::instance()->chosen_shipping_method_id = 'free_shipping';
			}
		}


		jigoshop_session::instance()->coupons = self::$applied_coupons;
		jigoshop::add_message(__('Discount coupon applied successfully.', 'jigoshop'));

		return true;
	}

	/** returns whether or not a discount has been applied
	 *
	 * @param $code
	 * @return bool
	 */
	public static function has_discount($code)
	{
		return in_array($code, self::$applied_coupons);
	}

	/** returns whether or not a free shipping coupon has been applied */
	public static function has_free_shipping_coupon()
	{
		if (!empty(self::$applied_coupons)) {
			foreach (self::$applied_coupons as $code) {
				if (($coupon = JS_Coupons::get_coupon($code)) && $coupon['free_shipping'] && self::has_valid_products_for_coupon($coupon)) {
					return true;
				}
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
		$has_data = false;
		$return = '';

		if (!$flat) {
			$return .= '<dl class="variation">';
		}

		// Variation data
		if ($cart_item['data'] instanceof jigoshop_product_variation && is_array($cart_item['variation'])) {
			$variation_list = array();

			foreach ($cart_item['variation'] as $name => $value) {
				$name = str_replace('tax_', '', $name);
				if (taxonomy_exists('pa_'.$name)) {
					$terms = get_terms('pa_'.$name, array('orderby' => 'slug', 'hide_empty' => '0'));

					foreach ($terms as $term) {
						if ($term->slug == $value) {
							$value = $term->name;
						}
					}

					$name = get_taxonomy('pa_'.$name)->labels->name;
					$name = $cart_item['data']->attribute_label('pa_'.$name);
				} else {
					$name = $cart_item['data']->attribute_label('pa_'.$name);
					$value = apply_filters('jigoshop_product_attribute_value_custom', $value, 'pa_'.$name);
				}

				$variation_list[] = $flat
					? sprintf('%s: %s<br />', $cart_item['data']->attribute_label($name), $value)
					: sprintf('<dt>%s:</dt> <dd>%s</dd><br />', $cart_item['data']->attribute_label($name), $value);
			}

			$return .= $flat ? implode(', ', $variation_list) : implode('', $variation_list);
			$has_data = true;
		}

		// Other data - returned as array with name/value values
		$other_data = apply_filters('jigoshop_get_item_data', array(), $cart_item);

		if ($other_data && is_array($other_data) && sizeof($other_data) > 0) {
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
			$has_data = true;
		}

		if (!$flat) {
			$return .= '</dl>';
		}

		if ($has_data) {
			return $return;
		}

		return '';
	}
}
