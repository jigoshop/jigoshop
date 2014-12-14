<?php
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Country;

/**
 * @property int id
 * @property string order_date
 * @property string modified_date
 * @property string customer_note
 * @property string order_key
 * @property string user_id
 * @property string items
 * @property string billing_first_name
 * @property string billing_last_name
 * @property string billing_company
 * @property string billing_euvatno
 * @property string billing_address_1
 * @property string billing_address_2
 * @property string billing_city
 * @property string billing_postcode
 * @property string billing_country
 * @property string billing_state
 * @property string billing_email
 * @property string billing_phone
 * @property string shipping_first_name
 * @property string shipping_last_name
 * @property string shipping_company
 * @property string shipping_address_1
 * @property string shipping_address_2
 * @property string shipping_city
 * @property string shipping_postcode
 * @property string shipping_country
 * @property string shipping_state
 * @property string shipping_method
 * @property string shipping_service
 * @property string payment_method
 * @property string payment_method_title
 * @property string order_subtotal
 * @property string order_discount_subtotal
 * @property string order_shipping
 * @property string order_discount
 * @property string order_discount_coupons
 * @property string order_tax
 * @property string order_shipping_tax
 * @property string order_total
 * @property string order_total_prices_per_tax_class_ex_tax
 * @property string formatted_billing_address
 * @property string formatted_shipping_address
 * @property string status
 */
class jigoshop_order extends Jigoshop_Base
{
	public $_data = array();

	private static $_statusTransformations = array(
		'new' => Status::PENDING,
		'pending' => Status::PENDING,
		'on-hold' => Status::ON_HOLD,
		'processing' => Status::PROCESSING,
		'completed' => Status::COMPLETED,
		'cancelled' => Status::CANCELLED,
		'refunded' => Status::REFUNDED,
	);

	private $order_data;
	/** @var \Jigoshop\Entity\Order */
	private $_order;
	private $_formattedBillingAddress;
	private $_formattedShippingAddress;
	private $_items = array();

	public function __construct($id = '')
	{
		if ($id > 0) {
			apply_filters('jigoshop_get_order', $this->get_order($id), $id, $this);
		}

		do_action('customized_emails_init'); /* load plugins for customized emails */
	}

	public function get_order($id = 0)
	{
		if (!$id) {
			return false;
		}

		$service = \Integration::getOrderService();
		$this->_order = $service->find($id);

		if ($this->_order->getId() !== null) {
			return true;
		}

		return false;
	}

	public function __get($variable)
	{
		switch ($variable) {
			case 'id':
				return $this->_order->getId();
			case 'order_date':
				return $this->_order->getCreatedAt()->format('Y-m-d H:i:s');
			case 'modified_date':
				return $this->_order->getUpdatedAt()->format('Y-m-d H:i:s');
			case 'customer_note':
				return $this->_order->getCustomerNote();
			case 'order_key':
				return $this->_order->getKey();
			case 'user_id':
				return $this->_order->getCustomer()->getId();
			case 'items':
				// TODO: Properly return old fashioned items
				// TODO: Need to have __key - item key
				// TODO: Store as $_items array with keys as IDs
				return $this->_order->getItems();
			case 'billing_first_name':
				return $this->_order->getCustomer()->getBillingAddress()->getFirstName();
			case 'billing_last_name':
				return $this->_order->getCustomer()->getBillingAddress()->getLastName();
			case 'billing_company':
				$address = $this->_order->getCustomer()->getBillingAddress();
				return $address instanceof CompanyAddress ? $address->getCompany() : '';
			case 'billing_euvatno':
				$address = $this->_order->getCustomer()->getBillingAddress();
				return $address instanceof CompanyAddress ? $address->getVatNumber() : '';
			case 'billing_address_1':
				return $this->_order->getCustomer()->getBillingAddress()->getAddress();
			case 'billing_address_2':
				return '';
			case 'billing_city':
				return $this->_order->getCustomer()->getBillingAddress()->getCity();
			case 'billing_postcode':
				return $this->_order->getCustomer()->getBillingAddress()->getPostcode();
			case 'billing_country':
				return $this->_order->getCustomer()->getBillingAddress()->getCountry();
			case 'billing_state':
				return $this->_order->getCustomer()->getBillingAddress()->getState();
			case 'billing_email':
				return $this->_order->getCustomer()->getBillingAddress()->getEmail();
			case 'billing_phone':
				return $this->_order->getCustomer()->getBillingAddress()->getPhone();
			case 'shipping_first_name':
				return $this->_order->getCustomer()->getShippingAddress()->getFirstName();
			case 'shipping_last_name':
				return $this->_order->getCustomer()->getShippingAddress()->getLastName();
			case 'shipping_company':
				$address = $this->_order->getCustomer()->getShippingAddress();
				return $address instanceof CompanyAddress ? $address->getCompany() : '';
			case 'shipping_address_1':
				return $this->_order->getCustomer()->getShippingAddress()->getAddress();
			case 'shipping_address_2':
				return '';
			case 'shipping_city':
				return $this->_order->getCustomer()->getShippingAddress()->getCity();
			case 'shipping_postcode':
				return $this->_order->getCustomer()->getShippingAddress()->getPostcode();
			case 'shipping_country':
				return $this->_order->getCustomer()->getShippingAddress()->getCountry();
			case 'shipping_state':
				return $this->_order->getCustomer()->getShippingAddress()->getState();
			case 'shipping_method':
				return $this->_order->getShippingMethod() ? $this->_order->getShippingMethod()->getId() : '';
			case 'payment_method':
				return $this->_order->getPaymentMethod() ? $this->_order->getPaymentMethod()->getId() : '';
			case 'payment_method_title':
				return $this->_order->getPaymentMethod() ? $this->_order->getPaymentMethod()->getName() : '';
			case 'order_subtotal':
				return $this->_order->getSubtotal();
			case 'order_discount_subtotal':
				// TODO: What is this?
				return $this->_order->getKey();
			case 'order_shipping':
				return $this->_order->getShippingPrice();
			case 'order_discount':
				return $this->_order->getDiscount();
			case 'order_discount_coupons':
				// TODO: Save coupons used for order
				return array();
			case 'order_tax':
				// TODO: Probably we need old fashioned tax array
				return $this->_order->getTax();
			case 'order_shipping_tax':
				// TODO: Probably we need old fashioned tax array
				return $this->_order->getShippingTax();
			case 'order_total':
				return $this->_order->getTotal();
			case 'formatted_billing_address':
				if ($this->_formattedBillingAddress === null) {
					$country = $this->_order->getCustomer()->getBillingAddress()->getCountry();
					if (Country::exists($country)) {
						$country = Country::getName($country);
					}

					$address = array_map('trim', array(
						$this->_order->getCustomer()->getBillingAddress()->getAddress(),
						$this->_order->getCustomer()->getBillingAddress()->getCity(),
						$this->_order->getCustomer()->getBillingAddress()->getState(),
						$country,
						$this->_order->getCustomer()->getBillingAddress()->getPostcode(),
					));
					$this->_formattedBillingAddress = implode(', ', array_filter($address));
				}

				return $this->_formattedBillingAddress;
			case 'formatted_shipping_address':
				if ($this->_formattedShippingAddress === null) {
					$country = $this->_order->getCustomer()->getShippingAddress()->getCountry();
					if (Country::exists($country)) {
						$country = Country::getName($country);
					}

					$address = array_map('trim', array(
						$this->_order->getCustomer()->getShippingAddress()->getAddress(),
						$this->_order->getCustomer()->getShippingAddress()->getCity(),
						$this->_order->getCustomer()->getShippingAddress()->getState(),
						$country,
						$this->_order->getCustomer()->getShippingAddress()->getPostcode(),
					));
					$this->_formattedShippingAddress = implode(', ', array_filter($address));
				}

				return $this->_formattedShippingAddress;
			case 'status':
				return $this->_order->getStatus();
			default:
				return isset($this->_data[$variable]) ? $this->_data[$variable] : null;
		}
	}

	public function __set($variable, $value)
	{
		switch ($variable) {
			case 'id':
				$this->_order->setId($value);
				break;
			case 'order_date':
				$this->_order->getCreatedAt()->setTimestamp(strtotime($value));
				break;
			case 'modified_date':
				$this->_order->getUpdatedAt()->setTimestamp(strtotime($value));
				break;
			case 'customer_note':
				$this->_order->setCustomerNote($value);
				break;
			case 'order_key':
				$this->_order->setKey($value);
				break;
			case 'user_id':
				$this->_order->getCustomer()->getId();
				break;
			case 'items':
				// TODO: Properly reformat old fashioned items
				// TODO: Need to have __key - item key
				break;
			case 'billing_first_name':
				$this->_order->getCustomer()->getBillingAddress()->setFirstName($value);
				break;
			case 'billing_last_name':
				$this->_order->getCustomer()->getBillingAddress()->setLastName($value);
				break;
			case 'billing_company':
				$address = $this->_order->getCustomer()->getBillingAddress();
				if ($address instanceof CompanyAddress) {
					$address->setCompany($value);
				}
				break;
			case 'billing_euvatno':
				$address = $this->_order->getCustomer()->getBillingAddress();
				if ($address instanceof CompanyAddress) {
					$address->setVatNumber($value);
				}
				break;
			case 'billing_address_1':
				$this->_order->getCustomer()->getBillingAddress()->setAddress($value);
				break;
			case 'billing_address_2':
				break;
			case 'billing_city':
				$this->_order->getCustomer()->getBillingAddress()->setCity($value);
				break;
			case 'billing_postcode':
				$this->_order->getCustomer()->getBillingAddress()->setPostcode($value);
				break;
			case 'billing_country':
				$this->_order->getCustomer()->getBillingAddress()->setCountry($value);
				break;
			case 'billing_state':
				$this->_order->getCustomer()->getBillingAddress()->setState($value);
				break;
			case 'billing_email':
				$this->_order->getCustomer()->getBillingAddress()->setEmail($value);
				break;
			case 'billing_phone':
				$this->_order->getCustomer()->getBillingAddress()->setPhone($value);
				break;
			case 'shipping_first_name':
				$this->_order->getCustomer()->getShippingAddress()->setFirstName($value);
				break;
			case 'shipping_last_name':
				$this->_order->getCustomer()->getShippingAddress()->setLastName($value);
				break;
			case 'shipping_company':
				$address = $this->_order->getCustomer()->getShippingAddress();
				if ($address instanceof CompanyAddress) {
					$address->setCompany($value);
				}
				break;
			case 'shipping_address_1':
				$this->_order->getCustomer()->getShippingAddress()->setAddress($value);
				break;
			case 'shipping_address_2':
				break;
			case 'shipping_city':
				$this->_order->getCustomer()->getShippingAddress()->setCity($value);
				break;
			case 'shipping_postcode':
				$this->_order->getCustomer()->getShippingAddress()->setPostcode($value);
				break;
			case 'shipping_country':
				$this->_order->getCustomer()->getShippingAddress()->setCountry($value);
				break;
			case 'shipping_state':
				$this->_order->getCustomer()->getShippingAddress()->setState($value);
				break;
			case 'shipping_method':
				try {
					$method = Integration::getShippingService()->get($value);
					$this->_order->setShippingMethod($method, Integration::getTaxService());
				} catch (\Jigoshop\Exception $e) {
					Integration::getMessages()->addError(__('Invalid shipping method.', 'jigoshop'));
				}
				break;
			case 'payment_method':
				try {
					$method = Integration::getPaymentService()->get($value);
					$this->_order->setPaymentMethod($method);
				} catch (\Jigoshop\Exception $e) {
					Integration::getMessages()->addError(__('Invalid payment method.', 'jigoshop'));
				}
				break;
			case 'payment_method_title':
				break;
			case 'order_subtotal':
				$this->_order->setSubtotal($value);
				break;
			case 'order_discount_subtotal':
				// TODO: What is this?
				break;
			case 'order_shipping':
				// TODO: No option to set shipping price - throw an exception?
				break;
			case 'order_discount':
				$this->_order->setDiscount($value);
				break;
			case 'order_discount_coupons':
				// TODO: Coupons used for order
				break;
			case 'order_tax':
				// TODO: Transform old tax array into new one
				$this->_order->setTax($value);
				break;
			case 'order_shipping_tax':
				// TODO: Transform old tax array into new one
				$this->_order->setShippingTax($value);
				break;
			case 'order_total':
				$this->_order->setTotal($value);
				break;
			case 'formatted_billing_address':
				$this->_formattedBillingAddress = $value;
				break;
			case 'formatted_shipping_address':
				$this->_formattedShippingAddress = $value;
				break;
			case 'status':
				$this->_order->setStatus($value);
				break;
			default:
				$this->_data[$variable] = $value;
		}
	}

	/**
	 * @param $result \WP_Post
	 */
	public function populate($result)
	{
		// Standard post data
		$this->modified_date = $result->post_modified;
		$this->order_data = (array)maybe_unserialize(get_post_meta($this->id, 'order_data', true));
		$this->shipping_service = (string)$this->_fetch('shipping_service');
		// array
		$this->order_total_prices_per_tax_class_ex_tax = $this->_fetch('order_total_prices_per_tax_class_ex_tax');
	}

	/**
	 * This function is for internal uses only!
	 *
	 * @internal
	 * @param $key
	 * @return string
	 */
	public function _fetch($key)
	{
		foreach ((array)$key as $item) {
			if (isset($this->order_data[$item])) {
				return $this->order_data[$item];
			}
		}

		return '';
	}

	public static function get_order_statuses_and_names()
	{
		$statuses = Status::getStatuses();
		$result = array();
		foreach (self::$_statusTransformations as $old => $new) {
			$result[$old] = $statuses[$new];
		}

		return apply_filters('jigoshop_filter_order_status_names', $result);
	}

	/**
	 * Returns the order number for display purposes.
	 *
	 * @access public
	 * @return string Order number.
	 */
	public function get_order_number()
	{
		return apply_filters('jigoshop_order_number', _x('#', 'hash before order number', 'jigoshop').$this->_order->getNumber(), $this);
	}

	public function has_compound_tax()
	{
		// TODO: Improve when compound taxes are introduced
		return false;
//		$ret = false;
//		if ($this->get_tax_classes() && is_array($this->get_tax_classes())) :
//
//			foreach ($this->get_tax_classes() as $tax_class) :
//				if ($this->order_tax[$tax_class]['compound'] == 'yes') :
//					$ret = true;
//					break;
//				endif;
//			endforeach;
//
//		endif;
//
//		return $ret;
	}

	public function get_tax_classes()
	{
		$classes = $this->order_tax;
		return ($classes && is_array($classes) ? array_keys($classes) : array());
	}

	public function get_total_tax($with_currency = false, $with_price_options = true)
	{
		$order_tax = $this->_order->getTotalTax();

		if ($with_price_options) {
			if ($with_currency) {
				return \Jigoshop\Helper\Product::formatPrice($order_tax);
			} else {
				return \Jigoshop\Helper\Product::formatNumericPrice($order_tax);
			}
		} else {
			return number_format((double)$order_tax, 2); // no formatting for pricing options for separators, use defaults
		}
	}

	public function tax_class_is_not_compound($tax_class)
	{
		// TODO: Support on compound taxes introduction
		return true;
//		return !$this->order_tax[$tax_class]['compound'];
	}

	public function get_tax_class_for_display($tax_class)
	{
		$service = Integration::getTaxService();
		return $service->getLabel($tax_class, $this->_order->getCustomer());
	}

	public function show_tax_entry($tax_class)
	{
		$tax = $this->_order->getTax();
		return isset($tax[$tax_class]) && $tax[$tax_class] > 0;
	}

	public function get_tax_amount($tax_class, $has_price = true)
	{
		$tax = $this->_order->getTax();
		$amount = isset($tax[$tax_class]) ? $tax[$tax_class] : 0.0;
		$shippingTax = $this->_order->getShippingTax();
		$amount += isset($shippingTax[$tax_class]) ? $shippingTax[$tax_class] : 0.0;

		return ($has_price ? \Jigoshop\Helper\Product::formatPrice($amount) : $amount);
	}

	public function get_tax_rate($tax_class)
	{
		$service = Integration::getTaxService();
		return $service->getRate($tax_class, $this->_order->getCustomer());
	}

	public function get_price_ex_tax_for_tax_class($tax_class)
	{
		// TODO: Implement
		return (isset($this->order_total_prices_per_tax_class_ex_tax[$tax_class]) ? jigoshop_price($this->order_total_prices_per_tax_class_ex_tax[$tax_class]) : jigoshop_price(0));
	}

	public function get_subtotal_to_display()
	{
		$subtotal = \Jigoshop\Helper\Product::formatPrice($this->_order->getSubtotal());
		if ($this->_order->getTotalTax() > 0) {
			$subtotal .= __(' <small>(ex. tax)</small>', 'jigoshop');
		}

		return $subtotal;
	}

	public function get_shipping_to_display($inc_tax = false)
	{
		$price = $this->_order->getShippingPrice();
		if ($price > 0) {
			$shipping = \Jigoshop\Helper\Product::formatPrice($price);
			$shippingTax = array_reduce($this->_order->getShippingTax(), 'sum');

			if ($shippingTax > 0) { //tax applied to shipping
				// inc tax used with norway emails
				$shipping = ($inc_tax ? \Jigoshop\Helper\Product::formatPrice($price + $shippingTax) : $shipping);
				$tax_tag = ($inc_tax ? __('(inc. tax)', 'jigoshop') : __('(ex. tax)', 'jigoshop'));
				$shipping .= sprintf(__(' <small>%s %s</small>', 'jigoshop'), $tax_tag, ucwords($this->shipping_service));
			} else { // when no tax applied to shipping
				$shipping .= sprintf(__(' <small>%s</small>', 'jigoshop'), ucwords($this->shipping_service));
			}
		} else {
			$shipping = __('Free!', 'jigoshop');
		}

		return $shipping;
	}

	public function email_order_items_list($show_download_links = false, $show_sku = false, $price_inc_tax = false)
	{
		$emails = Integration::getEmails();
		// TODO: Any idea how to use all parameters?
		return $emails->__formatItems($this->_order);
	}

	public function get_product_from_item($item)
	{
		try {
			return $this->_order->getItem($item['__key']);
		} catch(\Jigoshop\Exception $e) {
			return null;
		}
	}

	public function get_downloadable_file_url($item_id)
	{
		return \Jigoshop\Helper\Api::getEndpointUrl('download-file', $this->_order->getKey().'.'.$this->_order->getId().'.'.$this->_items[$item_id]['__key']);
	}

	public function get_checkout_payment_url()
	{
		return \Jigoshop\Helper\Order::getPayLink($this->_order);
	}

	public function get_cancel_order_url()
	{
		return \Jigoshop\Helper\Order::getCancelLink($this->_order);
	}

	public function cancel_order($note = '')
	{
		$this->_order->setStatus(Status::CANCELLED, $note);
	}

	public function update_status($new_status_slug, $note = '')
	{
		$status = self::$_statusTransformations[$new_status_slug];
		$this->_order->setStatus($status, $note);
	}

	public function add_order_note($note, $private = 1)
	{
		return Integration::getOrderService()->addNote($this->_order, $note, $private == 1);
	}

	public function add_sale()
	{
		// Sales are added on stock reduction automatically
	}

	public function payment_complete()
	{
		$this->_order->setStatus(Status::PROCESSING);
		do_action('jigoshop_payment_complete', $this->_order->getId());
	}

	public function reduce_order_stock()
	{
		foreach ($this->_order->getItems() as $item) {
			/** @var \Jigoshop\Entity\Order\Item $item */
			do_action('jigoshop\product\sold', $item->getProduct(), $item->getQuantity(), $item);
		}

		Integration::getOrderService()->addNote($this->_order, __('Order item stock reduced successfully.', 'jigoshop'));
	}
}
