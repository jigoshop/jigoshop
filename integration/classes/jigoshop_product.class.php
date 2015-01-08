<?php

use Jigoshop\Entity\Product;

class jigoshop_product extends Jigoshop_Base
{
	public $id;           // : jigoshop_template_functions.php on line 99 // This is just an alias for $this->ID
	public $ID;
	public $exists;       // : jigoshop_cart.class.php on line 66
	public $product_type; // : jigoshop_template_functions.php on line 271
	public $sku;          // : jigoshop_template_functions.php on line 246
	public $brand;
	public $gtin;
	public $mpn;

	public $data;         // jigoshop_tax.class.php on line 186
	public $post;         // for get_title()

	public $meta;         // for get_child()
	public $visibility = 'visible';
	public $stock;
	public $children = array();
	protected $regular_price;
	protected $sale_price;
	protected $sale_price_dates_from;
	protected $sale_price_dates_to;
	protected $stock_sold;
	protected $jigoshop_options;
	private $weight;
	private $length; // : admin/jigoshop-admin-post-types.php on line 168
	private $width;
	private $height;
	private $tax_status = 'taxable';
	private $tax_class;
	private $featured = false;         // : admin/jigoshop-admin-post-types.php on line 180
	private $manage_stock = false;    // for managed stock only
	private $stock_status = 'instock'; // all sales whether managed stock or not
	private $backorders;
	private $quantity_sold; // : jigoshop_template_functions.php on line 328
	private $attributes = array(); // : jigoshop_product_variation.php uses as well
	private $__product;

	/** @var \Jigoshop\Service\ProductServiceInterface */
	private static $__productService;

	public function __construct($product)
	{
		if (!($product instanceof Product)) {
			$product = self::$__productService->find($product);
		}

		$this->__product = $product;
		$this->ID = $this->id = $product->getId();

		// TODO: What about meta values?
		if (!$this->meta) {
			$this->meta = get_post_custom($this->ID);
		}

		$meta = $this->meta;
		$this->exists = true;
		$this->product_type = $product->getType();

		// Define data
		if ($product instanceof Product\Purchasable) {
			// TODO: Do we need to update to getPrice() or add getRegularPrice()?
			$this->regular_price = $product->getRegularPrice();

			$this->manage_stock = $product->getStock()->getManage() ? 'yes' : 'no';
			$this->stock_status = $product->getStock()->getStatus() == Product\Attributes\StockStatus::IN_STOCK ? 'instock' : 'outofstock';
			$this->backorders = $product->getStock()->getAllowBackorders();
			$this->stock = $product->getStock()->getStock();
			$this->quantity_sold = $product->getStock()->getSoldQuantity();
			//$this->stock_sold = isset($meta['quantity_sold'][0]) ? $meta['quantity_sold'][0] : null; // TODO: What is this?
		}

		if ($product instanceof Product\Saleable) {
			$this->sale_price = $product->getSales()->getPrice();
			// TODO: Proper sale dates
			$this->sale_price_dates_from = $product->getSales()->getFrom()->getTimestamp();
			$this->sale_price_dates_to = $product->getSales()->getTo()->getTimestamp();
		}

		$this->weight = $product->getSize()->getWeight();
		$this->length = $product->getSize()->getLength();
		$this->width = $product->getSize()->getWidth();
		$this->height = $product->getSize()->getHeight();

		$this->tax_status = $product->isTaxable() ? 'taxable' : 'none';
		$this->tax_class = $product->getTaxClasses();

		$this->sku = $product->getSku();
		$this->brand = $product->getBrand();
		$this->gtin = $product->getGtin();
		$this->mpn = $product->getMpn();
		$this->featured = isset($meta['featured'][0]) ? $meta['featured'][0] : null;
		switch ($product->getVisibility()) {
			case Product::VISIBILITY_CATALOG:
				$this->visibility = 'catalog';
				break;
			case Product::VISIBILITY_PUBLIC:
				$this->visibility = 'visible';
				break;
			case Product::VISIBILITY_SEARCH:
				$this->visibility = 'search';
				break;
			default:
				$this->visibility = 'none';
		}

		// filter for Paid Memberships Pro plugin courtesy @strangerstudios
		$this->sale_price = apply_filters('jigoshop_sale_price', $this->sale_price, $this);

		return $this;
	}

	public static function get_product_tax_rate($tax_class, $product_tax_rates)
	{
		// TODO: Check
		if ($tax_class && $product_tax_rates && is_array($product_tax_rates)) {
			return $product_tax_rates[$tax_class]['rate'];
		}

		return (double)0;
	}

	public static function get_non_compounded_tax($tax_class, $product_tax_rates)
	{
		// TODO: Check
		if ($tax_class && $product_tax_rates && is_array($product_tax_rates)) {
			return $product_tax_rates[$tax_class]['is_not_compound_tax'];
		}

		return true;
	}

	public static function getAttributeTaxonomies()
	{
		// TODO: What to do here? :/
		global $wpdb;

		if (self::$attribute_taxonomies === null) {
			self::$attribute_taxonomies = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."jigoshop_attribute_taxonomies;");
		}

		return self::$attribute_taxonomies;
	}

	public static function get_product_ids_on_sale()
	{
		// TODO: Properly use ProductService and current data structures
		$on_sale = get_posts(array(
			'post_type' => array('product', 'product_variation'),
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => 'sale_price',
					'value' => 0,
					'compare' => '>=',
					'type' => 'DECIMAL',
				),
				array(
					'key' => 'sale_price',
					'value' => '',
					'compare' => '!=',
					'type' => '',
				)
			),
			'fields' => 'id=>parent',
		));

		// filter out duplicates and 0 id's leaving only actual parent product ID's for variations
		$parent_ids = array_filter(array_unique(array_values($on_sale)));

		// Check if parents are still variable
		foreach ($parent_ids as $key => $id) {
			$terms = get_the_terms($id, 'product_type');
			if ($terms[0]->slug != 'variable') {
				unset($parent_ids[$key]);
			}
		}

		// remove the variable products from the originals ID's
		foreach ($on_sale as $id => $parent) {
			if ($parent <> 0) {
				unset($on_sale[$id]);
			}
		}
		// these are non-variable products
		$all_ids = array_keys($on_sale);
		// merge the variable parents and other products together
		$product_ids = array_unique(array_merge($all_ids, $parent_ids));

		// now check the sale date fields on the main products
		foreach ($product_ids as $key => $id) {
			$sale_from = get_post_meta($id, 'sale_price_dates_from', true);
			if (!empty($sale_from)) {
				if ($sale_from > current_time('timestamp')) {
					unset($product_ids[$key]);
					continue;
				}
			}
			$sale_to = get_post_meta($id, 'sale_price_dates_to', true);
			if (!empty($sale_to)) {
				if ($sale_to < current_time('timestamp')) {
					unset($product_ids[$key]);
				}
			}
		}

		$product_ids = array_values($product_ids);

		return $product_ids;
	}

	public function get_image($size = 'shop_thumbnail')
	{
		switch ($size) {
			case 'shop_thumbnail':
				$size = \Jigoshop\Core\Options::IMAGE_THUMBNAIL;
				break;
			case '':
				// TODO: Finish other cases
		}

		return \Jigoshop\Helper\Product::getFeaturedImage($this->__product, $size);
	}

	public function get_sku()
	{
		return $this->__product->getSku();
	}

	public function reduce_stock($by = -1)
	{
		return $this->modify_stock(-$by);
	}

	public function modify_stock($by)
	{
		if ($this->__product instanceof Product\Purchasable) {
			if (!$this->__product->getStock()->getManage()) {
				return false;
			}

			$this->__product->getStock()->modifyStock($by);
			$this->stock = $this->__product->getStock()->getStock();

			return $this->stock;
		}

		return false;
	}

	public function managing_stock()
	{
		if (!\Jigoshop\Integration::getOptions()->get('products.manage_stock')) {
			return false;
		}

		return $this->__product->getStock()->getManage();
	}

	public function increase_stock($by = 1)
	{
		return $this->modify_stock($by);
	}

	public function requires_shipping()
	{
		// If it's virtual or downloadable don't require shipping, same for subscriptions
		return apply_filters('jigoshop_requires_shipping', $this->__product instanceof Product\Shippable && $this->__product->isShippable(), $this->id);
	}

	public function is_type($type)
	{
		if (is_array($type) && in_array($this->product_type, $type)) {
			return true;
		}

		if ($this->product_type == $type) {
			return true;
		}

		return false;
	}

	public function exists()
	{
		return $this->exists;
	}

	public function is_shipping_taxable()
	{
		return ($this->is_taxable() || $this->tax_status == 'shipping');
	}

	public function is_taxable()
	{
		return $this->__product->isTaxable();
	}

	public function get_title()
	{
		return apply_filters('jigoshop_product_title', $this->__product->getName(), $this);
	}

	public function get_link()
	{
		return apply_filters('jigoshop_product_link', $this->__product->getLink(), $this);
	}

	public function add_to_cart_url()
	{
		// TODO: Check
		if ($this->has_child()) {
			$url = add_query_arg('add-to-cart', 'group');
			$url = add_query_arg('product', $this->ID, $url);

			if ($this->is_type('variable')) {
				$url = add_query_arg('add-to-cart', 'variation');
			}
		} else {
			$url = add_query_arg('add-to-cart', $this->ID);
		}

		$url = jigoshop::nonce_url('add_to_cart', $url);

		return $url;
	}

	public function has_child()
	{
		// TODO: This needs Group product implementation
		return false;
	}

	public function get_children()
	{
		// TODO: This needs Group product implementation
		return array();
	}

	public function has_enough_stock($quantity)
	{
		// TODO: Ended here
		return $this->backorders_allowed() || !$this->managing_stock() || $this->stock_status == 'instock' || $this->get_stock() >= $quantity;
	}

	/**
	 * Returns whether or not the product can be backordered
	 *
	 * @return  bool
	 */
	public function backorders_allowed()
	{

		if ($this->backorders == 'yes' || $this->backorders_require_notification()) {
			return true;
		}

		return false;
	}

	/**
	 * Returns whether or not the product needs to notify the customer on backorder
	 *
	 * @return  bool
	 */
	public function backorders_require_notification()
	{

		return ($this->backorders == 'notify');
	}

	/**
	 * Returns number of items available for sale.
	 * NOTE: This method always loads stock quantity from database to be perfectly up-to-date.
	 *
	 * @return int
	 */
	public function get_stock()
	{
		return (int)get_post_meta($this->id, 'stock', true);
	}

	/**
	 * Returns a string representing the availability of the product
	 *
	 * @return  string
	 */
	public function get_availability()
	{
		// Do not display initial availability if we aren't managing stock or if variable or grouped
		if (self::get_options()->get('jigoshop_manage_stock') != 'yes' || $this->is_type(array('grouped', 'variable'))) {
			return false;
		}

		// Start as in stock
		$notice = array(
			'availability' => __('In Stock', 'jigoshop'),
			'class' => null,
		);

		// If stock is being managed & has stock
		if ($this->is_in_stock()) {
			// Check if we allow backorders
			if ($this->stock <= 0 && $this->backorders_allowed()) {
				$notice['availability'] = __('Available for order', 'jigoshop');
			} else if (self::get_options()->get('jigoshop_show_stock') == 'yes' && !$this->has_child() && $this->stock > 0) {
				// Check if we want user to get how many items is available
				$notice['availability'] .= ': '.$this->stock.' '.__(' available', 'jigoshop');
			}
		} else {
			$notice['availability'] = __('Out of Stock', 'jigoshop');
			$notice['class'] = 'out-of-stock';
		}

		return apply_filters('jigoshop_product_availability', $notice, $this);
	}

	/**
	 * Returns whether or not the product is in stock
	 *
	 * @param bool $below_stock_threshold Whether to compare against the global setting for no stock threshold
	 * @return bool
	 */
	public function is_in_stock($below_stock_threshold = false)
	{

		// Always return in stock if product is in stock
		if (self::get_options()->get('jigoshop_manage_stock') != 'yes') {
			return true;
		}

		if ($this->is_type(array('grouped', 'variable'))) {
			foreach ($this->get_children() as $child_ID) {

				// Get the children
				$child = $this->get_child($child_ID);

				// If one of our children is in stock then return true
				if ($child->is_in_stock()) {
					return true;
				}
			}
		}

		// If we arent managing stock then it should always be in stock
		if (!$this->managing_stock() && $this->stock_status == 'instock') {
			return true;
		}

		// Check if we allow backorders
		if ($this->managing_stock() && $this->backorders_allowed()) {
			return true;
		}

		// Check if we have stock
		if ($this->stock == '-9999999') {
			$_parent = new jigoshop_product($this->ID);
			$this->stock = $_parent->stock;
		}
		if ($this->managing_stock() && ($below_stock_threshold ? $this->stock > self::get_options()->get('jigoshop_notify_no_stock_amount') : $this->stock > 0)) {
			return true;
		}

		return false;
	}

	/**
	 * Return an instance of a child
	 *
	 * @param   int               Child Product ID
	 * @return  jigoshop_product
	 */
	public function get_child($child_ID)
	{

		if ($this->is_type('variable')) {
			return new jigoshop_product_variation($child_ID);
		}

		return new jigoshop_product($child_ID);
	}

	/**
	 * Returns whether or not the product is featured
	 *
	 * @return  bool
	 */
	public function is_featured()
	{
		return (bool)$this->featured;
	}

	/**
	 * Checks if the product is visibile
	 *
	 * @return  bool
	 */
	public function is_visible()
	{

		// Disabled due to incorrect stock handling -Rob
		//if( (bool) $this->stock )
		//	return false;

		switch ($this->visibility) {
			case 'hidden':
				return false;
				break;
			case 'search':
				return is_search();
				break;
			case 'catalog':
				return !is_search(); // don't display in search results
				break;
			default:
				return true; // By default always display a product
		}
	}

	/**
	 * Get the product total price excluding or with tax
	 *
	 * @param int $quantity
	 * @return float the total price of the product times the quantity.
	 */
	public function get_defined_price($quantity = 1)
	{
		if (self::get_options()->get('jigoshop_show_prices_with_tax') == 'yes') {
			return $this->get_price_with_tax($quantity);
		} else {
			return $this->get_price_excluding_tax($quantity);
		}
	}

	/**
	 * Get the product total price including tax
	 *
	 * @param int $quantity
	 * @return float the total price of the product times the quantity and destination tax included
	 */
	public function get_price_with_tax($quantity = 1)
	{
		if (self::get_options()->get('jigoshop_calc_taxes') !== 'yes' || self::get_options()->get('jigoshop_prices_include_tax') === 'yes') {
			return $this->get_price();
		}

		// to avoid rounding errors multiply by 100
		$price = $this->get_price_excluding_tax() * 100;
		$rates = (array)$this->get_tax_destination_rate();
		$tax_totals = 0;

		if (count($rates) > 0) {
			// rates array sorted so that taxes applied to retail value come first. To reverse taxes, need to reverse this array
			$new_rates = array_reverse($rates, true);
			$_tax = new jigoshop_tax(100);

			foreach ($new_rates as $key => $value) {
				if ($value['is_not_compound_tax']) {
					$tax_totals += $_tax->calc_tax($price * $quantity, $value['rate'], false);
				} else {
					$tax_amount[$key] = $_tax->calc_tax($price * $quantity, $value['rate'], false);
					$tax_totals += $tax_amount[$key];
				}
				$tax_totals = round($tax_totals, 1); // without this we were getting rounding errors
			}
		}

		return round(($price * $quantity + $tax_totals) / 100, 4);
	}

	/**
	 * Returns the products current price, either regular or sale
	 *
	 * @return  int
	 */
	public function get_price()
	{

		$price = null;
		if ($this->is_on_sale()) {
			if (strstr($this->sale_price, '%')) {
				$price = round($this->regular_price * ((100 - str_replace('%', '', $this->sale_price)) / 100), 4);
			} else if ($this->sale_price) {
				$price = $this->sale_price;
			}
		} else {
			$price = apply_filters('jigoshop_product_get_regular_price', $this->regular_price, $this->ID);
		}

		return apply_filters('jigoshop_product_get_price', $price, $this->ID);

	}

	/**
	 * Returns whether or not the product is on sale.
	 * If one of the child products is on sale, product is considered to be on sale
	 *
	 * @return  bool
	 */
	public function is_on_sale()
	{

		// Check child products for items on sale
		if ($this->is_type(array('grouped', 'variable'))) {

			foreach ($this->get_children() as $child_ID) {
				$child = $this->get_child($child_ID);
				if ($child->is_on_sale()) {
					return true;
				}
			}
		}

		$time = current_time('timestamp');

		// Check if the sale is still in range (if we have a range)
		if ($this->sale_price_dates_from <= $time &&
			$this->sale_price_dates_to >= $time &&
			$this->sale_price
		) {

			return true;
		}
		// Otherwise if we have a sale price
		if (!$this->sale_price_dates_to && $this->sale_price) {
			return true;
		}

		// Just incase return false
		return false;
	}

	/**
	 * Get the product total price excluding tax
	 *
	 * @param int $quantity
	 * @return float the total price of the product times the quantity without any tax included
	 */
	public function get_price_excluding_tax($quantity = 1)
	{
		// to avoid rounding errors multiply by 100
		$price = $this->get_price() * 100;

		if (self::get_options()->get('jigoshop_prices_include_tax') == 'yes') {
			$rates = (array)$this->get_tax_base_rate();

			if (count($rates) > 0) {
				// rates array sorted so that taxes applied to retail value come first. To reverse taxes, need to reverse this array
				$new_rates = array_reverse($rates, true);
				$tax_totals = 0;
				$_tax = new jigoshop_tax(100);

				foreach ($new_rates as $key => $value) {
					if ($value['is_not_compound_tax']) {
						$tax_totals += $_tax->calc_tax($price * $quantity, $value['rate'], true);
					} else {
						$tax_amount[$key] = $_tax->calc_tax($price * $quantity, $value['rate'], true);
						$tax_totals += $tax_amount[$key];
					}
				}

				return round(($price * $quantity - $tax_totals) / 100, 4);
			}
		}

		return round($price * $quantity / 100, 4);
	}

	/**
	 * Returns the base Country and State tax rate
	 */
	public function get_tax_base_rate()
	{
		$rate = array();

		if ($this->is_taxable() && self::get_options()->get('jigoshop_calc_taxes') == 'yes') {
			$_tax = new jigoshop_tax();
			$tax_classes = $this->get_tax_classes();

			foreach ($_tax->get_tax_classes_for_customer() as $tax_class) {
				if (!in_array($tax_class, $tax_classes)) {
					continue;
				}

				$my_rate = $_tax->get_rate($tax_class);

				if ($my_rate > 0) {
					$rate[$tax_class] = array('rate' => $my_rate, 'is_not_compound_tax' => !$_tax->is_compound_tax());
				}
			}
		}

		return $rate;
	}

	/**
	 * Returns the tax classes
	 *
	 * @return array the tax classes on the product
	 */
	public function get_tax_classes()
	{
		return (array)get_post_meta($this->ID, 'tax_classes', true);
	}

	/**
	 * Returns the destination Country and State tax rate
	 */
	public function get_tax_destination_rate()
	{
		$rates = array();

		if ($this->is_taxable() && self::get_options()->get('jigoshop_calc_taxes') == 'yes') {
			$tax = new jigoshop_tax();

			foreach ($tax->get_tax_classes_for_customer() as $tax_class) {
				if (!in_array($tax_class, $this->get_tax_classes())) {
					continue;
				}

				$rate = $tax->get_rate($tax_class);

				if ($rate > 0) {
					$rates[$tax_class] = array('rate' => $rate, 'is_not_compound_tax' => !$tax->is_compound_tax());
				}
			}
		}

		return $rates;
	}

	/**
	 * Returns the percentage saved on sale products
	 *
	 * @note was called get_percentage()
	 * @return  string
	 */
	public function get_percentage_sale()
	{

		if ($this->is_on_sale()) {
			// 100% - sale price percentage over regular price
			$percentage = 100 - (($this->sale_price / $this->regular_price) * 100);

			// Round & return
			return round($percentage).'%';
		}
	}

	/**
	 * Returns the products regular price
	 *
	 * @return  float
	 */
	public function get_regular_price()
	{
		return $this->regular_price;
	}

	/**
	 * Adjust the products price during runtime
	 *
	 * @param mixed
	 */
	public function adjust_price($new_price)
	{

		// Only adjust sale price if we are on sale
		if ($this->is_on_sale()) {
			$this->sale_price = $this->get_price() + $new_price;
		} else {
			$this->regular_price += $new_price;
		}
	}

	public function variations_priced_the_same()
	{
		$variations_priced_the_same = true;
		if ($this->is_type('variable')) {
			$children = $this->get_children();
			$array = array();
			$onsale = false;
			$sameprice = true;
			foreach ($children as $child_ID) {
				$child = $this->get_child($child_ID);
				if ($child->is_in_stock()) {
					if ($child->is_on_sale()) {
						$onsale = true;
					} // signal at least one child is on sale
					$array[$child_ID] = $child->get_price();
				}
			}
			asort($array);  // cheapest price first
			if (count($array) >= 2 && reset($array) != end($array)) {
				$sameprice = false;
			}
			if (!$sameprice) {
				$variations_priced_the_same = false;
			} elseif ($onsale) { // prices may be the same, but we could be on sale
				$variations_priced_the_same = false;
			} else {  // prices are the same
				$variations_priced_the_same = true;
			}
		}

		return $variations_priced_the_same;
	}

	/**
	 * Returns the price in html format
	 *
	 * @return string HTML price of product
	 */
	public function get_price_html()
	{

		$html = null;

		// First check if the product has child products
		if ($this->is_type('variable') || $this->is_type('grouped')) {

			if (!($children = $this->get_children())) {
				return __('Unavailable', 'jigoshop');
			}

			$array = array();
			$onsale = false;
			foreach ($children as $child_ID) {
				$child = $this->get_child($child_ID);

				// Only get prices that are in stock
				if ($child->is_in_stock()) {
					if ($child->is_on_sale()) {
						$onsale = true;
					} // signal at least one child is on sale
					// store product id for later, get regular or sale price if available
					if ($child->get_price() != null) {
						$array[$child_ID] = $child->get_price();
					}
				}
			}
			asort($array);  // cheapest price first

			// only display 'From' if prices differ among them
			$sameprice = true;
			if (count($array) >= 2 && reset($array) != end($array)) {
				$sameprice = false;
			}
			if (!$sameprice) :
				$html = '<span class="from">'._x('From:', 'price', 'jigoshop').'</span> ';
				reset($array);
				$id = key($array);
				$child = $this->get_child($id);
				if ($child->is_on_sale()) {
					$html .= $child->get_calculated_sale_price_html();
				} else {
					$html .= jigoshop_price($child->get_price());
				}
			elseif ($onsale) : // prices may be the same, but we could be on sale and need the 'From'
				$html = '<span class="from">'._x('From:', 'price', 'jigoshop').'</span> ';
				reset($array);
				$id = key($array);
				$child = $this->get_child($id);
				if ($child->is_on_sale()) {
					$html .= $child->get_calculated_sale_price_html();
				} else {
					$html .= jigoshop_price($child->get_price());
				}
			else :  // prices are the same
				$html = jigoshop_price(reset($array));
			endif;

			$html = empty($array) ? __('Price Not Announced', 'jigoshop') : $html;

			return apply_filters('jigoshop_product_get_price_html', $html, $this, $this->regular_price);
		}

		// For standard products

		if ($this->is_on_sale()) {
			$html = $this->get_calculated_sale_price_html();
		} else {
			$html = jigoshop_price($this->get_price());
		}

		if ($this->get_price() == 0) {
			$html = __('Free', 'jigoshop');
		}

		if ($this->regular_price == '') {
			$html = __('Price Not Announced', 'jigoshop');
		}

		return apply_filters('jigoshop_product_get_price_html', $html, $this, $this->regular_price);
	}

	/**
	 * Returns the products sale value, either with or without a percentage
	 *
	 * @return string HTML price of product (with sales)
	 */
	public function get_calculated_sale_price_html()
	{

		if ($this->is_on_sale()) :
			if (strstr($this->sale_price, '%')) {
				return '
					<del>'.jigoshop_price($this->regular_price).'</del>'.jigoshop_price($this->get_price()).'
					<br><ins>'.sprintf(__('%s off!', 'jigoshop'), $this->sale_price).'</ins>';
			} else {
				return '
					<del>'.jigoshop_price($this->regular_price).'</del>
					<ins>'.jigoshop_price($this->sale_price).'</ins>';
			}

		endif;

		return '';
	}

	/**
	 * Returns the upsell product ids
	 *
	 * @return mixed
	 */
	public function get_upsells()
	{
		$ids = get_post_meta($this->id, 'upsell_ids');
		if (!empty($ids)) {
			return $ids[0];
		} else {
			return array();
		}
	}

	/**
	 * Returns the cross_sells product ids
	 *
	 * @return mixed
	 */
	public function get_cross_sells()
	{
		$ids = get_post_meta($this->id, 'crosssell_ids');
		if (!empty($ids)) {
			return $ids[0];
		} else {
			return array();
		}
	}

	/**
	 * Returns the product categories
	 *
	 * @param string $sep Separator.
	 * @param string $before Content before list.
	 * @param string $after Content after list.
	 * @return string HTML code of categories list.
	 */
	public function get_categories($sep = ', ', $before = '', $after = '')
	{
		return get_the_term_list($this->ID, 'product_cat', $before, $sep, $after);
	}

	/**
	 * Returns the product tags
	 *
	 * @param string $sep Separator.
	 * @param string $before Content before list.
	 * @param string $after Content after list.
	 * @return string HTML code of tags list.
	 */
	public function get_tags($sep = ', ', $before = '', $after = '')
	{
		return get_the_term_list($this->ID, 'product_tag', $before, $sep, $after);
	}

	public function get_rating_html($location = '')
	{

		if ($location) {
			$location = '_'.$location;
		}
		$star_size = apply_filters('jigoshop_star_rating_size'.$location, 16);

		global $wpdb;

		// Do we really need this? -Rob
		$count = $wpdb->get_var($wpdb->prepare("
			SELECT COUNT(meta_value) FROM $wpdb->commentmeta
			LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
			WHERE meta_key = 'rating'
			AND comment_post_ID = %d
			AND comment_approved = '1'
			AND meta_value > 0
		", $this->id));

		$ratings = $wpdb->get_var($wpdb->prepare("
			SELECT SUM(meta_value) FROM $wpdb->commentmeta
			LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
			WHERE meta_key = 'rating'
			AND comment_post_ID = %d
			AND comment_approved = '1'
		", $this->id));

		// If we don't have any posts
		if (!(bool)$count) {
			return false;
		}

		// Figure out the average rating
		$average_rating = number_format($ratings / $count, 2);

		// If we don't have an average rating
		if (!(bool)$average_rating) {
			return false;
		}

		// If all goes well echo out the html
		return '<div class="star-rating" title="'.sprintf(__('Rated %s out of 5', 'jigoshop'), $average_rating).'"><span style="width:'.($average_rating * $star_size).'px"><span class="rating">'.$average_rating.'</span> '.__('out of 5', 'jigoshop').'</span></div>';
	}

	/**
	 * Gets all products which have a common category or tag
	 * TODO: Add stock check?
	 *
	 * @param int $limit
	 * @return array
	 */
	public function get_related($limit = 5)
	{

		// Get the tags & categories
		$tags = wp_get_post_terms($this->ID, 'product_tag', array('fields' => 'ids'));
		$cats = wp_get_post_terms($this->ID, 'product_cat', array('fields' => 'ids'));

		// No queries if we don't have any tags -and- categories (one -or- the other should be queried)
		if (empty($cats) && empty($tags)) {
			return array();
		}

		// Only get related posts that are in stock & visible
		$query = array(
			'posts_per_page' => $limit,
			'post__not_in' => array($this->ID),
			'post_type' => 'product',
			'fields' => 'ids',
			'orderby' => 'rand',
			'meta_query' => array(
				array(
					'key' => 'visibility',
					'value' => array('catalog', 'visible'),
					'compare' => 'IN',
				),
			),
			'tax_query' => array(
				'relation' => 'OR',
			),
		);

		if (!empty($cats)) {
			$query['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field' => 'id',
				'terms' => $cats
			);
		}
		if (!empty($tags)) {
			$query['tax_query'][] = array(
				'taxonomy' => 'product_tag',
				'field' => 'id',
				'terms' => $tags
			);
		}

		// Run the query
		$q = get_posts($query);

		wp_reset_postdata();

		return $q;
	}

	// Returns the product rating in html format
	// TODO: optimize this code

	/**
	 * Gets a single product attribute
	 *
	 * @param $key string Attribute key.
	 * @return string|array
	 */
	public function get_attribute($key)
	{

		// Get the attribute in question & sanitize just incase
		$attributes = $this->get_attributes();
		$attr = $attributes[sanitize_title($key)];

		// If its a taxonomy return that
		if ($attr['is_taxonomy']) {
			return get_the_terms($this->ID, 'pa_'.sanitize_title($attr['name']));
		}

		return $attr['value'];
	}

	/**
	 * Gets the attached product attributes
	 *
	 * @return  array
	 */
	public function get_attributes()
	{
		// Get the attributes
		if (!$this->attributes) {
			if (isset($this->meta['product_attributes'])) {
				$this->attributes = maybe_unserialize($this->meta['product_attributes'][0]);
				if (!is_array($this->attributes)) {
					$this->attributes = array();
				}
			}
		}

		return $this->attributes;
	}

	/**
	 * Lists attributes in a html table
	 *
	 * @return string HTML code with attributes list.
	 **/
	public function list_attributes()
	{


		// Check that we have some attributes that are visible
		if (!($this->has_attributes() || $this->has_dimensions() || $this->has_weight())) {
			return false;
		}

		// Start the html output
		$html = '<table class="shop_attributes">';

		// Output weight if we have it
		if (self::get_options()->get('jigoshop_enable_weight') == 'yes' && $this->get_weight()) {
			$html .= '<tr><th>'.__('Weight', 'jigoshop').'</th><td>'.$this->get_weight().self::get_options()->get('jigoshop_weight_unit').'</td></tr>';
		}

		// Output dimensions if we have it
		if (self::get_options()->get('jigoshop_enable_dimensions') == 'yes') {
			if ($this->get_length()) {
				$html .= '<tr><th>'.__('Length', 'jigoshop').'</th><td>'.$this->get_length().self::get_options()->get('jigoshop_dimension_unit').'</td></tr>';
			}
			if ($this->get_width()) {
				$html .= '<tr><th>'.__('Width', 'jigoshop').'</th><td>'.$this->get_width().self::get_options()->get('jigoshop_dimension_unit').'</td></tr>';
			}
			if ($this->get_height()) {
				$html .= '<tr><th>'.__('Height', 'jigoshop').'</th><td>'.$this->get_height().self::get_options()->get('jigoshop_dimension_unit').'</td></tr>';
			}
		}

		$attributes = $this->get_attributes();
		foreach ($attributes as $attr) {

			// If attribute is invisible skip
			if (empty($attr['visible'])) {
				continue;
			}

			// Get Title & Value from attribute array
			$name = $this->attribute_label('pa_'.$attr['name']);
			$value = null;

			if ((bool)$attr['is_taxonomy']) {

				// Get the taxonomy terms
				$product_terms = wp_get_object_terms($this->ID, 'pa_'.sanitize_title($attr['name']), array('orderby' => 'slug'));

				if (is_wp_error($product_terms)) {
					jigoshop_log("product::list_attributes() - Attribute for invalid taxonomy = ".$attr['name']);
					continue;
				}

				// Convert them into a array to be imploded
				$terms = array();

				foreach ($product_terms as $term) {
					$terms[] = '<span class="val_'.$term->slug.'">'.$term->name.'</span>';
				}

				$value = apply_filters('jigoshop_product_attribute_value_taxonomy', implode(', ', $terms), $terms, $attr);
			} else {
				$value = apply_filters('jigoshop_product_attribute_value_custom', wptexturize($attr['value']), $attr);
			}

			// Generate the remaining html
			$html .= "
			<tr class=\"attr_".$attr['name']."\">
				<th>$name</th>
				<td>$value</td>
			</tr>";
		}

		$html .= '</table>';

		return $html;
	}

	/**
	 * Checks for any visible attributes attached to the product
	 *
	 * @return  boolean
	 */
	public function has_attributes()
	{
		$attributes = $this->get_attributes();

		// Quit early if there aren't any attributes
		if (empty($attributes)) {
			return false;
		}

		// If we have attributes that are visible return true
		foreach ($attributes as $attribute) {
			if (!empty($attribute['visible'])) {
				return true;
			}
		}

		// By default we don't have any attributes
		return false;
	}

	/**
	 * Checks if the product has dimensions
	 *
	 * @param boolean $all_dimensions if true, then all dimensions have to be set
	 * in order for has_dimensions to return true, otherwise if false, then just 1
	 * of the dimensions has to be set for the function to return true.
	 * @return  bool
	 */
	public function has_dimensions($all_dimensions = false)
	{

		if (self::get_options()->get('jigoshop_enable_dimensions') != 'yes') {
			return false;
		}

		return ($all_dimensions ? ($this->get_length() && $this->get_width() && $this->get_height()) : ($this->get_length() || $this->get_width() || $this->get_height()));
	}

	/**
	 * Returns the product's length
	 *
	 * @return mixed length
	 */
	public function get_length()
	{
		return $this->length;
	}

	/**
	 * Returns the product's width
	 *
	 * @return  mixed   width
	 */
	public function get_width()
	{
		return $this->width;
	}

	/**
	 * Returns the product's height
	 *
	 * @return  mixed   height
	 */
	public function get_height()
	{
		return $this->height;
	}

	/**
	 * Checks if the product has weight
	 *
	 * @return  bool
	 */
	public function has_weight()
	{

		if (self::get_options()->get('jigoshop_enable_weight') != 'yes') {
			return false;
		}

		return (bool)$this->get_weight();
	}

	/**
	 * Returns the product's weight
	 *
	 * @return  mixed   weight
	 */
	public function get_weight()
	{
		return $this->weight;
	}

	/**
	 * Get a product attributes label
	 */
	public function attribute_label($name)
	{
		global $wpdb;

		if (strstr($name, 'pa_')) {
			$name = str_replace('pa_', '', sanitize_text_field($name));
			$label = $wpdb->get_var($wpdb->prepare("SELECT attribute_label FROM ".$wpdb->prefix."jigoshop_attribute_taxonomies WHERE attribute_name = %s;", $name));

			if (!$label) {
				$label = ucfirst($name);
			}
		} else {  // taxonomies aren't created for custom text attributes, get name from the attribute instead

			// Discovered in Jigoshop 1.7, this function can be incorrectly called from
			// 'jigoshop_get_formatted_variation' as a static class method
			// make sure we have an instance to work with here for custom text attributes before calling $this
			if ($this instanceof jigoshop_product) {
				$label = $name;
				$attributes = $this->get_attributes();
				foreach ($attributes as $key => $attr) {
					if (!$attr['is_taxonomy'] && $key == $name) {
						$label = $attr['name'];
						break;
					}
				}
			} else {
				$name = str_replace('pa_', '', sanitize_text_field($name));
				$label = ucfirst($name);
			}
		}

		return apply_filters('jigoshop_attribute_label', $label);
	}

	/**
	 * Returns an array of available values for attributes used in product variations
	 * TODO: Note that this is 'variable product' specific, and should be moved to separate class
	 * with all 'variable product' logic form other methods in this class.
	 *
	 * @return array Two dimensional array of attributes and their available values
	 */
	function get_available_attributes_variations()
	{

		if (!$this->is_type('variable') || !$this->has_child()) {
			return array();
		}

		$attributes = $this->get_attributes();

		if (!is_array($attributes)) {
			return array();
		}

		$available_attributes = array();
		$children = $this->get_children();


		foreach ($attributes as $attribute) {

			// If we don't have any variations
			if (!$attribute['variation']) {
				continue;
			}

			$values = array();

			$attr_name = 'tax_'.sanitize_title($attribute['name']);

			foreach ($children as $child) {

				// Check if variation is disabled
				if (get_post_status($child) != 'publish') {
					continue;
				}

				// Get the variation & all attributes associated
				$child = $this->get_child($child);
				$options = $child->get_variation_attributes();

				if (is_array($options)) {
					foreach ($options as $key => $value) {
						if ($key == $attr_name) {
							$values[] = $value;
						}
					}
				}
			}

			//empty value indicates that all options for given attribute are available
			if (in_array('', $values)) {

				if ($attribute['is_taxonomy']) {
					$options = array();
					$terms = wp_get_object_terms($this->ID, 'pa_'.sanitize_title($attribute['name']), array('orderby' => 'slug'));

					foreach ($terms as $term) {
						$options[] = $term->slug;
					}
				} else {
					$options = explode(',', $attribute['value']);
				}

				$options = array_map('trim', $options);
				$values = array_unique($options);

			} else {

				if (!$attribute['is_taxonomy']) {
					$options = explode(',', $attribute['value']);
					$options = array_map('trim', $options);
					$values = array_intersect($options, $values);
				}

				$values = array_unique($values);
			}

			$values = array_unique($values);
			asort($values);
			$available_attributes[$attribute['name']] = $values;
		}

		return $available_attributes;
	}

	/**
	 * Gets the default attributes for a variable product.
	 *
	 * @return array
	 */
	function get_default_attributes()
	{

		$default = isset($this->meta['_default_attributes'][0]) ? $this->meta['_default_attributes'][0] : '';

		return apply_filters('jigoshop_product_default_attributes', (array)maybe_unserialize($default), $this);
	}

}
