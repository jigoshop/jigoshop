<?php

namespace Jigoshop\Core;

use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Service\Email;
use Jigoshop\Shipping\LocalPickup;
use WPAL\Wordpress;

class Emails {
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Email */
	private $emailService;

	public function __construct(Wordpress $wp, Options $options, Email $emailService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->emailService = $emailService;

		$wp->addAction('init', array($this, 'registerMails'), 100);
		$wp->addAction('order_status_pending_to_processing', array($this, 'orderPendingToProcessing'));
		$wp->addAction('order_status_pending_to_completed', array($this, 'orderPendingToCompleted'));
		$wp->addAction('order_status_pending_to_on-hold', array($this, 'orderPendingToOnHold'));
		$wp->addAction('order_status_on-hold_to_processing', array($this, 'orderOnHoldToProcessing'));
		$wp->addAction('order_status_completed', array($this, 'orderCompleted'));
		$wp->addAction('order_status_refunded', array($this, 'orderRefunded'));
		$wp->addAction('jigoshop_low_stock_notification', array($this, 'productLowStock'));
		$wp->addAction('jigoshop_no_stock_notification', array($this, 'productOutOfStock'));
		$wp->addAction('jigoshop_product_on_backorders_notification', array($this, 'productBackorders'));

		$this->addOrderActions();
		$this->addProductActions();
	}

	public function registerMails()
	{
		$orderArguments = $this->getOrderEmailArgumentsDescription();
		$stockArguments = $this->getStockEmailArgumentsDescription();

		$this->emailService->register('admin_order_status_pending_to_processing', __('Order Pending to Processing for admin'), $orderArguments);
		$this->emailService->register('admin_order_status_pending_to_completed', __('Order Pending to Completed for admin'), $orderArguments);
		$this->emailService->register('admin_order_status_pending_to_on_hold', __('Order Pending to On-Hold for admin'), $orderArguments);
		$this->emailService->register('customer_order_status_pending_to_on_hold', __('Order Pending to On-Hold for customer'), $orderArguments);
		$this->emailService->register('customer_order_status_pending_to_processing', __('Order Pending to Processing for customer'), $orderArguments);
		$this->emailService->register('customer_order_status_on_hold_to_processing', __('Order On-Hold to Processing for customer'), $orderArguments);
		$this->emailService->register('customer_order_status_completed', __('Order Completed for customer'), $orderArguments);
		$this->emailService->register('customer_order_status_refunded', __('Order Refunded for customer'), $orderArguments);
		$this->emailService->register('low_stock_notification', __('Low Stock Notification'), $stockArguments);
		$this->emailService->register('no_stock_notification', __('No Stock Notification'), $stockArguments);
		$this->emailService->register('product_on_backorders_notification', __('Backorders Notification'), array_merge(
			$stockArguments, $orderArguments, array('amount' => __('Amount', 'jigoshop'))
		));
		$this->emailService->register('send_customer_invoice', __('Send Customer Invoice'), $orderArguments);
	}

	/**
	 * @param $order Order
	 */
	public function orderPendingToProcessing($order){
		$arguments = $this->getOrderEmailArguments($order);
		$this->emailService->send('admin_order_status_pending_to_processing', $arguments, $this->options->get('general.email'));
		$this->emailService->send('customer_order_status_pending_to_processing', $arguments, $order->getCustomer()->getBillingAddress()->getEmail());
	}

	/**
	 * @param $order Order
	 */
	public function orderPendingToCompleted($order){
		$this->emailService->send('admin_order_status_pending_to_completed', $this->getOrderEmailArguments($order), $this->options->get('general.email'));
	}

	/**
	 * @param $order Order
	 */
	public function orderPendingToOnHold($order){
		$arguments = $this->getOrderEmailArguments($order);
		$this->emailService->send('admin_order_status_pending_to_on_hold', $arguments, $this->options->get('general.email'));
		$this->emailService->send('customer_order_status_pending_to_on_hold', $arguments, $order->getCustomer()->getBillingAddress()->getEmail());
	}

	/**
	 * @param $order Order
	 */
	public function orderOnHoldToProcessing($order){
		$this->emailService->send('customer_order_status_on_hold_to_processing', $this->getOrderEmailArguments($order), $order->getCustomer()->getBillingAddress()->getEmail());
	}

	/**
	 * @param $order Order
	 */
	public function orderCompleted($order){
		$this->emailService->send('customer_order_status_completed', $this->getOrderEmailArguments($order), $order->getCustomer()->getBillingAddress()->getEmail());
	}

	/**
	 * @param $order Order
	 */
	public function orderRefunded($order){
		$this->emailService->send('customer_order_status_refunded', $this->getOrderEmailArguments($order), $order->getCustomer()->getBillingAddress()->getEmail());
	}

	/**
	 * @param $product Product
	 */
	public function productLowStock($product){
		$this->emailService->send('low_stock_notification', $this->getStockEmailArguments($product), $this->options->get('general.email'));
	}

	/**
	 * @param $product Product
	 */
	public function productOutOfStock($product){
		$this->emailService->send('no_stock_notification', $this->getStockEmailArguments($product), $this->options->get('general.email'));
	}

	/**
	 * @param $order Order
	 * @param $product Product
	 * @param $amount int
	 */
	public function productBackorders($order, $product, $amount){
		$arguments = array_merge(
			$this->getOrderEmailArguments($order),
			$this->getStockEmailArguments($product),
			array('amount' => $amount)
		);

		if ($product instanceof Product\Purchasable) {
			$this->emailService->send('product_on_backorders_notification', $arguments, $this->options->get('general.email'));
			if ($product->getStock()->getAllowBackorders() == Product\Attributes\StockStatus::BACKORDERS_NOTIFY) {
				$this->emailService->send('product_on_backorders_notification', $arguments, $order->getCustomer()->getBillingAddress()->getEmail());
			}
		}
	}

	/**
	 * @param $order Order The order.
	 * @return array Available arguments with proper values.
	 */
	private function getOrderEmailArguments($order)
	{
		$billingAddress = $order->getCustomer()->getBillingAddress();
		$shippingAddress = $order->getCustomer()->getShippingAddress();

		return apply_filters('jigoshop_order_email_variables', array(
			'order_number' => $order->getNumber(),
			'order_date' => date_i18n(get_option('date_format')), // TODO: Replace with WPAL calls
			'shop_name' => $this->options->get('general.company_name'),
			'shop_address_1' => $this->options->get('general.company_address_1'),
			'shop_address_2' => $this->options->get('general.company_address_2'),
			'shop_tax_number' => $this->options->get('general.company_tax_number'),
			'shop_phone' => $this->options->get('general.company_phone'),
			'shop_email' => $this->options->get('general.company_email'),
			'customer_note' => $order->getCustomerNote(),
			'order_items' => $this->formatItems($order),
			'subtotal' => ProductHelper::formatPrice($order->getSubtotal()),
			'shipping' => ProductHelper::formatPrice($order->getShippingPrice()),
			'shipping_cost' => $order->getShippingPrice(),
			'shipping_method' => $order->getShippingMethod(),
			'discount' => '',//ProductHelper::formatPrice($order->order_discount), // TODO: Properly add discounts after coupons
			'total_tax' => ProductHelper::formatPrice($order->getTotalTax()),
			'total' => ProductHelper::formatPrice($order->getTotal()),
			'is_local_pickup' => $order->getShippingMethod() && $order->getShippingMethod()->getId() == LocalPickup::NAME ? true : null,
			'checkout_url' => null, //$order->getStatus() == Order\Status::PENDING ? $order->get_checkout_payment_url() : null, // TODO: Get payment URL
			'payment_method' => $order->getPaymentMethod()->getName(),
			'billing_first_name' => $billingAddress->getFirstName(),
			'billing_last_name' => $billingAddress->getLastName(),
			'billing_company' => $billingAddress instanceof CompanyAddress ? $billingAddress->getCompany() : '',
			'billing_address_1' => $billingAddress->getAddress(),
			'billing_address_2' => '', // TODO: Remove address_2
			'billing_postcode' => $billingAddress->getPostcode(),
			'billing_city' => $billingAddress->getCity(),
			'billing_country' => Country::getName($billingAddress->getCountry()),
			'billing_state' => Country::hasStates($billingAddress->getCountry()) ? Country::getStateName($billingAddress->getCountry(), $billingAddress->getState()) : $billingAddress->getState(),
			'billing_email' => $billingAddress->getEmail(),
			'billing_phone' => $billingAddress->getPhone(),
			'shipping_first_name' => $shippingAddress->getFirstName(),
			'shipping_last_name' => $shippingAddress->getLastName(),
			'shipping_company' => $shippingAddress instanceof CompanyAddress ? $shippingAddress->getCompany() : '',
			'shipping_address_1' => $shippingAddress->getAddress(),
			'shipping_address_2' => '', // TODO: Remove address_2
			'shipping_postcode' => $shippingAddress->getPostcode(),
			'shipping_city' => $shippingAddress->getCity(),
			'shipping_country' => Country::getName($shippingAddress->getCountry()),
			'shipping_state' => Country::hasStates($shippingAddress->getCountry()) ? Country::getStateName($shippingAddress->getCountry(), $shippingAddress->getState()) : $shippingAddress->getState(),
		),$order);
	}

	/**
	 * @param $order Order The order.
	 * @return string Items formatted for email.
	 */
	private function formatItems($order)
	{
//		$inc_tax = $this->options->get('tax.included');

		$result = '';
		// validate if any item has cost less than 0. If that's the case, we can't use price including tax
		// TODO: Support for "price includes tax"
//		$use_inc_tax = $inc_tax;
//		if ($inc_tax) {
//			foreach ($this->items as $item) {
//				$use_inc_tax = ($item['cost_inc_tax'] >= 0);
//				if (!$use_inc_tax) {
//					break;
//				}
//			}
//		}

		foreach ($order->getItems() as $item) {
			/** @var $item Order\Item */
			$itemResult = '';
			$product = $item->getProduct();
			$itemResult .= $item->getQuantity().' x '.html_entity_decode($this->wp->applyFilters('jigoshop_order_product_title', $item->getName(), $product, $item), ENT_QUOTES, 'UTF-8');

			if ($product->getSku()) {
				$itemResult .= ' (#'.$product->getSku().')';
			}
			// TODO: Support for "price includes tax"
//			if ($use_inc_tax && $item['cost_inc_tax'] >= 0) {
//				$return .= ' - '.html_entity_decode(strip_tags(jigoshop_price($item['cost_inc_tax'] * $item['qty'], array('ex_tax_label' => 0))), ENT_COMPAT, 'UTF-8');
//			} else {
			$itemResult .= ' - '.ProductHelper::formatPrice($item->getCost());
//			}

			if ($product instanceof Product\Variable) {
				$variation = $product->getVariation($item->getMeta('variation_id')->getValue());
				$itemResult .= PHP_EOL;

				foreach ($variation->getAttributes() as $attribute) {
					/** @var $attribute \Jigoshop\Entity\Product\Variable\Attribute */
					$itemResult .= $attribute->getAttribute()->getLabel().': '.$attribute->printValue($item).', ';
				}

				$itemResult = rtrim($itemResult, ',');
			}

			$itemResult = $this->wp->applyFilters('jigoshop\emails\order_item', $itemResult, $item);

			// TODO: Add download links in downloadable type
//			if ($product->is_type('downloadable')) {
//				if ((bool)$item['variation_id']) {
//					$product_id = $product->variation_id;
//				} else {
//					$product_id = $product->ID;
//				}
//				if ($this->get_downloadable_file_url($product_id)) {
//					$result .= PHP_EOL.__('Your download link for this file is:', 'jigoshop');
//					$result .= PHP_EOL.' - '.$this->get_downloadable_file_url($product_id).'';
//				}
//			}

			$result .= $itemResult.PHP_EOL;
		}

		return $result;
	}

	private function getOrderEmailArgumentsDescription()
	{
		return apply_filters('jigoshop_order_email_variables_description', array(
			'order_number' => __('Order Number', 'jigoshop'),
			'order_date' => __('Order Date', 'jigoshop'),
			'shop_name' => __('Shop Name', 'jigoshop'),
			'shop_address_1' => __('Shop Address part 1', 'jigoshop'),
			'shop_address_2' => __('Shop Address part 2', 'jigoshop'),
			'shop_tax_number' => __('Shop TaxNumber', 'jigoshop'),
			'shop_phone' => __('Shop_Phone', 'jigoshop'),
			'shop_email' => __('Shop Email', 'jigoshop'),
			'customer_note' => __('Customer Note', 'jigoshop'),
			'order_items' => __('Ordered Items', 'jigoshop'),
			'subtotal' => __('Subtotal', 'jigoshop'),
			'shipping' => __('Shipping Price and Method', 'jigoshop'),
			'shipping_cost' => __('Shipping Cost', 'jigoshop'),
			'shipping_method' => __('Shipping Method', 'jigoshop'),
			'discount' => __('Discount Price', 'jigoshop'),
			'total_tax' => __('Total Tax', 'jigoshop'),
			'total' => __('Total Price', 'jigoshop'),
			'payment_method' => __('Payment Method Title', 'jigoshop'),
			'is_local_pickup' => __('Is Local Pickup?', 'jigoshop'),
			'checkout_url' => __('If order is pending, show checkout url', 'jigoshop'),
			'billing_first_name' => __('Billing First Name', 'jigoshop'),
			'billing_last_name' => __('Billing Last Name', 'jigoshop'),
			'billing_company' => __('Billing Company', 'jigoshop'),
			'billing_address_1' => __('Billing Address part 1', 'jigoshop'),
			'billing_address_2' => __('Billing Address part 2', 'jigoshop'),
			'billing_postcode' => __('Billing Postcode', 'jigoshop'),
			'billing_city' => __('Billing City', 'jigoshop'),
			'billing_country' => __('Billing Country', 'jigoshop'),
			'billing_state' => __('Billing State', 'jigoshop'),
			'billing_email' => __('Billing Email', 'jigoshop'),
			'billing_phone' => __('Billing Phone    ', 'jigoshop'),
			'shipping_first_name' => __('Shipping First Name', 'jigoshop'),
			'shipping_last_name' => __('Shipping Last Name', 'jigoshop'),
			'shipping_company' => __('Shipping Company', 'jigoshop'),
			'shipping_address_1' => __('Shipping Address part 1', 'jigoshop'),
			'shipping_address_2' => __('Shipping_Address part 2', 'jigoshop'),
			'shipping_postcode' => __('Shipping Postcode', 'jigoshop'),
			'shipping_city' => __('Shipping City', 'jigoshop'),
			'shipping_country' => __('Shipping Country', 'jigoshop'),
			'shipping_state' => __('Shipping State', 'jigoshop'),
		));
	}

	/**
	 * @param $product Product
	 * @return array
	 */
	private function getStockEmailArguments($product)
	{
		return array(
			'shop_name' => $this->options->get('general.company_name'),
			'shop_address_1' => $this->options->get('general.company_address_1'),
			'shop_address_2' => $this->options->get('general.company_address_2'),
			'shop_tax_number' => $this->options->get('general.company_tax_number'),
			'shop_phone' => $this->options->get('general.company_phone'),
			'shop_email' => $this->options->get('general.company_email'),
			'product_id' => $product->getId(),
			'product_name' => $product->getName(),
			'sku' => $product->getSku(),
		);
	}

	private function getStockEmailArgumentsDescription()
	{
		return array(
			'shop_name' => __('Shop Name', 'jigoshop'),
			'shop_address_1' => __('Shop Address part 1', 'jigoshop'),
			'shop_address_2' => __('Shop Address part 2', 'jigoshop'),
			'shop_tax_number' => __('Shop TaxNumber', 'jigoshop'),
			'shop_phone' => __('Shop_Phone', 'jigoshop'),
			'shop_email' => __('Shop Email', 'jigoshop'),
			'product_id' => __('Product ID', 'jigoshop'),
			'product_name' => __('Product Name', 'jigoshop'),
			'sku' => __('SKU', 'jigoshop'),
		);
	}

	/**
	 * @param $order Order
	 */
	public function sendCustomerInvoice($order)
	{
		$this->emailService->send('send_customer_invoice', $this->getOrderEmailArguments($order), $order->getCustomer()->getBillingAddress()->getEmail());
	}

	private function addOrderActions()
	{
		$this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::PENDING, Order\Status::PROCESSING), array($this, 'orderPendingToProcessing'));
		$this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::PENDING, Order\Status::COMPLETED), array($this, 'orderPendingToCompleted'));
		$this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::PENDING, Order\Status::ON_HOLD), array($this, 'orderPendingToOnHold'));
		$this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::ON_HOLD, Order\Status::PROCESSING), array($this, 'orderOnHoldToProcessing'));
		$this->wp->addAction(sprintf('jigoshop\order\%s_to_%s', Order\Status::PENDING, Order\Status::PROCESSING), array($this, 'orderPendingToProcessing'));
		$this->wp->addAction(sprintf('jigoshop\order\%s', Order\Status::COMPLETED), array($this, 'orderCompleted'));
		$this->wp->addAction(sprintf('jigoshop\order\%s', Order\Status::REFUNDED), array($this, 'orderRefunded'));
	}

	private function addProductActions()
	{
		$this->wp->addAction('jigoshop\product\low_stock', array($this, 'productLowStock'));
		$this->wp->addAction('jigoshop\product\out_of_stock', array($this, 'productOutOfStock'));
		$this->wp->addAction('jigoshop\product\backorders', array($this, 'productBackorders'));
	}
}

