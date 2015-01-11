<?php

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Integration;

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
	private $__product;

	/** @var \Jigoshop\Service\ProductServiceInterface */
	private static $__productService;
	/** @var \Jigoshop\Service\TaxServiceInterface */
	private static $__taxService;

	public function __construct($product)
	{
		if (!($product instanceof Product)) {
			$product = self::$__productService->find($product);
		}

		/** @var $product Product */
		$this->__product = $product;
		$this->ID = $this->id = $product->getId();

		// TODO: What about meta values?
		if (!$this->meta) {
			$this->meta = get_post_custom($this->ID);
		}

		$this->exists = true;
		$this->product_type = $product->getType();

		// Define data
		if ($product instanceof Product\Purchasable) {
			/** @var $product Product\Purchasable */
			$this->regular_price = $product->getRegularPrice();

			$this->manage_stock = $product->getStock()->getManage() ? 'yes' : 'no';
			$this->stock_status = $product->getStock()->getStatus() == StockStatus::IN_STOCK ? 'instock' : 'outofstock';
			$this->backorders = $product->getStock()->getAllowBackorders();
			$this->stock = $product->getStock()->getStock();
			$this->quantity_sold = $product->getStock()->getSoldQuantity();
			$this->stock_sold = 0;//isset($meta['quantity_sold'][0]) ? $meta['quantity_sold'][0] : null; // TODO: What is this?
		}

		if ($product instanceof Product\Saleable) {
			/** @var $product Product\Saleable */
			$this->sale_price = $product->getSales()->getPrice();
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
		$this->featured = $product->isFeatured();

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

	public static function __setProductService($service)
	{
		self::$__productService = $service;
	}

	public static function __setTaxService($service)
	{
		self::$__taxService = $service;
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
		return array();

		// TODO: What to do here? :/
//		global $wpdb;
//
//		if (self::$attribute_taxonomies === null) {
//			self::$attribute_taxonomies = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."jigoshop_attribute_taxonomies;");
//		}
//
//		return self::$attribute_taxonomies;
	}

	public static function get_product_ids_on_sale()
	{
		$time = time();
		$query = new \WP_Query(array(
			'post_type' => array(\Jigoshop\Core\Types::PRODUCT),
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => 'sales_price',
					'value' => '',
					'compare' => '!=',
					'type' => '',
				),
				array(
					'key' => 'sales_enabled',
					'value' => 1,
					'compare' => '=',
				),
				array(
					'key' => 'sales_from',
					'value' => $time,
					'compare' => '>=',
				),
				array(
					'key' => 'sales_to',
					'value' => $time,
					'compare' => '<=',
				),
			),
			'fields' => 'ids',
		));

		// TODO: Check if this will work
		return $query->get_posts();
//		$products = self::$__productService->findByQuery($query);
	}

	public function get_image($size = 'shop_thumbnail')
	{
		switch ($size) {
			case 'shop_tiny':
				$size = \Jigoshop\Core\Options::IMAGE_TINY;
				break;
			default:
			case 'shop_thumbnail':
				$size = \Jigoshop\Core\Options::IMAGE_THUMBNAIL;
				break;
			case 'shop_small':
				$size = \Jigoshop\Core\Options::IMAGE_SMALL;
				break;
			case 'shop_large':
				$size = \Jigoshop\Core\Options::IMAGE_LARGE;
				break;
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
		if (!Integration::getOptions()->get('products.manage_stock')) {
			return false;
		}

		if ($this->__product instanceof Product\Purchasable) {
			return $this->__product->getStock()->getManage();
		}

		return false;
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
		return $this->backorders_allowed() || !$this->managing_stock() || $this->stock_status == 'instock' || $this->get_stock() >= $quantity;
	}

	public function backorders_allowed()
	{
		if ($this->__product instanceof Product\Purchasable) {
			return in_array($this->__product->getStock()->getAllowBackorders(), array(StockStatus::BACKORDERS_ALLOW, StockStatus::BACKORDERS_NOTIFY));
		}

		return false;
	}

	public function backorders_require_notification()
	{
		if ($this->__product instanceof Product\Purchasable) {
			return $this->__product->getStock()->getAllowBackorders() == StockStatus::BACKORDERS_NOTIFY;
		}

		return false;
	}

	public function get_stock()
	{
		if ($this->__product instanceof Product\Purchasable) {
			return $this->__product->getStock()->getStock();
		}

		return 0;
	}

	/**
	 * Returns a string representing the availability of the product
	 *
	 * @return  string
	 */
	public function get_availability()
	{
		if (!($this->__product instanceof Product\Purchasable)) {
			return false;
		}

		if (!$this->__product->getStock()->getManage()) {
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
			} else if (Integration::getOptions()->get('products.show_stock') && $this->stock > 0) {
				$notice['availability'] .= ': '.$this->stock.' '.__(' available', 'jigoshop');
			}
		} else {
			$notice['availability'] = __('Out of Stock', 'jigoshop');
			$notice['class'] = 'out-of-stock';
		}

		return apply_filters('jigoshop_product_availability', $notice, $this);
	}

	public function is_in_stock()
	{
		if ($this->__product instanceof Product\Purchasable) {
			return (
				$this->__product->getStock()->getManage() && (
					$this->__product->getStock()->getStock() > 0
					|| in_array($this->__product->getStock()->getAllowBackorders(), array(StockStatus::BACKORDERS_NOTIFY, StockStatus::BACKORDERS_ALLOW))
				)
			)
			|| $this->__product->getStock()->getStatus() == StockStatus::IN_STOCK;
		}

		// TODO: Variable support?

		return false;
	}

	public function get_child($id)
	{
		if ($this->is_type('variable')) {
			// TODO: Return properly variation based on product's one.
			return null;//new jigoshop_product_variation($id);
		}

		// TODO: Requires grouped product support
		return null;//new jigoshop_product($id);
	}

	public function is_featured()
	{
		return $this->__product->isFeatured();
	}

	public function is_visible()
	{
		return $this->__product->getVisibility() > Product::VISIBILITY_NONE;
	}

	public function get_defined_price($quantity = 1)
	{
		if (Integration::getOptions()->get('tax.price_tax') == 'with_tax') {
			return $this->get_price_with_tax($quantity);
		} else {
			return $this->get_price_excluding_tax($quantity);
		}
	}

	public function get_price_with_tax($quantity = 1)
	{
		// TODO: Support for price includes tax
//		if (\Jigoshop\Integration::getOptions()->get('tax.included')) {
//			return $this->get_price();
//		}
		if (!($this->__product instanceof Product\Purchasable)) {
			return 0.0;
		}

		$price = $this->__product->getPrice();

		if (self::$__taxService !== null) {
			$customer = Integration::getCustomerService()->getCurrent();
			$definitions = self::$__taxService->getDefinitions($customer->getTaxAddress());
			$price += self::$__taxService->calculate($price, $this->__product->getTaxClasses(), $definitions);
		}

		return $price * $quantity;
	}

	public function get_price()
	{
		if ($this->__product instanceof Product\Purchasable) {
			return $this->__product->getPrice();
		}

		return '';
	}

	public function is_on_sale()
	{
		if ($this->__product instanceof Product\Saleable) {
			return $this->__product->getSales()->isEnabled();
		}

		return false;
	}

	public function get_price_excluding_tax($quantity = 1)
	{
		// TODO: Support for price includes tax
//		if (\Jigoshop\Integration::getOptions()->get('tax.included')) {
//			return $this->get_price();
//		}

		return $this->get_price() * $quantity;
	}

	public function get_tax_base_rate()
	{
		$rates = array();

		if (self::$__taxService !== null && $this->__product->isTaxable()) {
			$options = Integration::getOptions();
			$address = new \Jigoshop\Entity\Customer\Address();
			$address->setCountry($options->get('general.country'));
			$address->setState($options->get('general.state'));
			$definitions = self::$__taxService->getDefinitions($address);

			foreach ($definitions as $definition) {
				$rates[$definition['class']] = array(
					'rate' => $definition['rate'],
					'is_not_compound_tax' => !$definition['is_compound'],
				);
			}
		}

		return $rates;
	}

	public function get_tax_classes()
	{
		return $this->__product->getTaxClasses();
	}

	/**
	 * Returns the destination Country and State tax rate
	 */
	public function get_tax_destination_rate()
	{
		$rates = array();

		if (self::$__taxService !== null && $this->__product->isTaxable()) {
			$customer = Integration::getCustomerService()->getCurrent();
			$definitions = self::$__taxService->getDefinitions($customer->getTaxAddress());

			foreach ($definitions as $definition) {
				$rates[$definition['class']] = array(
					'rate' => $definition['rate'],
					'is_not_compound_tax' => !$definition['is_compound'],
				);
			}
		}

		return $rates;
	}

	public function get_percentage_sale()
	{
		if ($this->__product instanceof Product\Purchasable && $this->is_on_sale()) {
			$percentage = 100 - ($this->__product->getPrice() / $this->__product->getRegularPrice() * 100);

			// Round & return
			return round($percentage).'%';
		}

		return '';
	}

	/**
	 * Returns the products regular price
	 *
	 * @return  float
	 */
	public function get_regular_price()
	{
		if (!($this->__product instanceof Product\Purchasable)) {
			return 0.0;
		}

		return $this->__product->getRegularPrice();
	}

	/**
	 * Adjust the products price during runtime
	 *
	 * @param mixed
	 */
	public function adjust_price()
	{
		_deprecated_function('adjust_price', '2.0');
	}

	public function variations_priced_the_same()
	{
		// TODO: Variations support
		return false;
	}

	/**
	 * Returns the price in html format
	 *
	 * @return string HTML price of product
	 */
	public function get_price_html()
	{
		return apply_filters('jigoshop_product_get_price_html', \Jigoshop\Helper\Product::getPriceHtml($this->__product), $this, $this->get_price());
	}

	/**
	 * Returns the products sale value, either with or without a percentage
	 *
	 * @return string HTML price of product (with sales)
	 */
	public function get_calculated_sale_price_html()
	{
		return '';
	}

	/**
	 * Returns the upsell product ids
	 *
	 * @return mixed
	 */
	public function get_upsells()
	{
		// TODO: Isn't this in a plugin?
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
		// TODO: Isn't this in a plugin?
		$ids = get_post_meta($this->id, 'crosssell_ids');
		if (!empty($ids)) {
			return $ids[0];
		} else {
			return array();
		}
	}

	public function get_categories($sep = ', ', $before = '', $after = '')
	{
		return get_the_term_list($this->ID, \Jigoshop\Core\Types::PRODUCT_CATEGORY, $before, $sep, $after);
	}

	public function get_tags($sep = ', ', $before = '', $after = '')
	{
		return get_the_term_list($this->ID, \Jigoshop\Core\Types::PRODUCT_TAG, $before, $sep, $after);
	}

	public function get_rating_html($location = '')
	{
		return \Jigoshop\Helper\Product::getRatingHtml(\Jigoshop\Helper\Product::getRating($this->__product), $location);
	}

	public function get_related($limit = 5)
	{
		return \Jigoshop\Helper\Product::getRelated($this->__product, $limit);
	}

	public function get_attribute($key)
	{
		return $this->__product->getAttribute($key)->getValue();
	}

	public function get_attributes()
	{
		// TODO: Properly map to old API
		return $this->__product->getAttributes();
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

		$options = Integration::getOptions();
		// Start the html output
		$html = '<table class="shop_attributes">';

		// Output weight if we have it
		if ($this->get_weight()) {
			$html .= '<tr><th>'.__('Weight', 'jigoshop').'</th><td>'.$this->get_weight().$options->get('products.weight_unit').'</td></tr>';
		}

		// Output dimensions if we have it
		if ($this->get_length()) {
			$html .= '<tr><th>'.__('Length', 'jigoshop').'</th><td>'.$this->get_length().$options->get('products.dimensions_unit').'</td></tr>';
		}
		if ($this->get_width()) {
			$html .= '<tr><th>'.__('Width', 'jigoshop').'</th><td>'.$this->get_width().$options->get('products.dimensions_unit').'</td></tr>';
		}
		if ($this->get_height()) {
			$html .= '<tr><th>'.__('Height', 'jigoshop').'</th><td>'.$this->get_height().$options->get('products.dimensions_unit').'</td></tr>';
		}

		foreach ($this->__product->getAttributes() as $attr) {
			/** @var $attr Product\Attribute */
			if (!$attr->isVisible()) {
				continue;
			}

			// Get Title & Value from attribute array
			$name = $attr->getLabel();
			$value = null;

			if ($attr->hasOptions()) {
				$terms = array();

				foreach ($attr->getOptions() as $option) {
					/** @var $option Product\Attribute\Option */
					$terms[] = '<span class="val_'.$option->getId().'">'.$option->getLabel().'</span>';
				}

				$value = apply_filters('jigoshop_product_attribute_value_taxonomy', implode(', ', $terms), $terms, $attr);
			} else {
				$value = apply_filters('jigoshop_product_attribute_value_custom', wptexturize($attr->getValue()), $attr);
			}

			// Generate the remaining html
			$html .= "
			<tr class=\"attr_".$attr->getSlug()."\">
				<th>{$name}</th>
				<td>{$value}</td>
			</tr>";
		}

		$html .= '</table>';

		return $html;
	}

	public function has_attributes()
	{
		$attributes = $this->__product->getAttributes();

		if (empty($attributes)) {
			return false;
		}

		foreach ($attributes as $attribute) {
			/** @var $attribute Product\Attribute */
			if ($attribute->isVisible()) {
				return true;
			}
		}

		return false;
	}

	public function has_dimensions($all_dimensions = false)
	{
		return ($all_dimensions ? ($this->get_length() && $this->get_width() && $this->get_height()) : ($this->get_length() || $this->get_width() || $this->get_height()));
	}

	public function get_length()
	{
		return $this->__product->getSize()->getLength();
	}

	public function get_width()
	{
		return $this->__product->getSize()->getWidth();
	}

	public function get_height()
	{
		return $this->__product->getSize()->getHeight();
	}

	public function has_weight()
	{
		return $this->__product->getSize()->getWeight() > 0;
	}

	public function get_weight()
	{
		return $this->__product->getSize()->getWeight();
	}

	public function attribute_label($name)
	{
		$attributes = $this->__product->getAttributes();
		foreach ($attributes as $attribute) {
			/** @var $attribute Product\Attribute */
			if ($attribute->getSlug() == $name) {
				return apply_filters('jigoshop_attribute_label', $attribute->getLabel());
			}
		}

		return apply_filters('jigoshop_attribute_label', ucfirst($name));
	}

	function get_available_attributes_variations()
	{
		if (!($this->__product instanceof Product\Variable)) {
			return array();
		}

		$result = array();
		$attributes = $this->__product->getVariableAttributes();

		foreach ($attributes as $attribute) {
			/** @var $attribute Product\Attribute */
			$result[$attribute->getLabel()] = array();

			if ($attribute->hasValue()) {
				$result[$attribute->getLabel()][] = $attribute->getOption($attribute->getValue())->getValue();
			} else {
				foreach ($attribute->getOptions() as $option) {
					/** @var $option Product\Attribute\Option */
					$result[$attribute->getLabel()][] = $option->getValue();
				}
			}
		}

		return $result;
	}

	function get_default_attributes()
	{
		return array();
	}
}
