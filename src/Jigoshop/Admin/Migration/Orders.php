<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Core\Messages;
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Entity\Product;
use Jigoshop\Exception;
use Jigoshop\Factory\Product\Variable;
use Jigoshop\Helper\Render;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\PaymentServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Service\ShippingServiceInterface;
use WPAL\Wordpress;

class Orders implements Tool
{
	const ID = 'jigoshop_orders_migration';

	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages */
	private $messages;
	/** @var OrderServiceInterface */
	private $orderService;
	/** @var ShippingServiceInterface */
	private $shippingService;
	/** @var PaymentServiceInterface */
	private $paymentService;
	/** @var ProductServiceInterface */
	private $productService;

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options, Messages $messages, OrderServiceInterface $orderService, ShippingServiceInterface $shippingService,
		PaymentServiceInterface $paymentService, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->orderService = $orderService;
		$this->shippingService = $shippingService;
		$this->paymentService = $paymentService;
		$this->productService = $productService;
	}

	/**
	 * @return string Tool ID.
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * Shows migration tool in Migration tab.
	 */
	public function display()
	{
		Render::output('admin/migration/orders', array());
	}

	/**
	 * Migrates data from old format to new one.
	 */
	public function migrate()
	{
		$wpdb = $this->wp->getWPDB();

		// Register order status taxonomy to fetch old statuses
		$this->wp->registerTaxonomy('shop_order_status',
			array('shop_order'),
			array(
				'hierarchical' => true,
				'update_count_callback' => '_update_post_term_count',
				'labels' => array(
					'name' => __('Order statuses', 'jigoshop'),
					'singular_name' => __('Order status', 'jigoshop'),
					'search_items' => __('Search Order statuses', 'jigoshop'),
					'all_items' => __('All  Order statuses', 'jigoshop'),
					'parent_item' => __('Parent Order status', 'jigoshop'),
					'parent_item_colon' => __('Parent Order status:', 'jigoshop'),
					'edit_item' => __('Edit Order status', 'jigoshop'),
					'update_item' => __('Update Order status', 'jigoshop'),
					'add_new_item' => __('Add New Order status', 'jigoshop'),
					'new_item_name' => __('New Order status Name', 'jigoshop')
				),
				'public' => false,
				'show_ui' => false,
				'show_in_nav_menus' => false,
				'query_var' => true,
				'rewrite' => false,
			)
		);

		$query = $wpdb->prepare("
			SELECT DISTINCT p.ID, pm.* FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE p.post_type = %s AND p.post_status <> %s",
			array('shop_order', 'auto-draft'));
		$orders = $wpdb->get_results($query);

		for ($i = 0, $endI = count($orders); $i < $endI;) {
			$order = $orders[$i];

			// Update central order data
			$status = $this->wp->getTheTerms($order->ID, 'shop_order_status');

			if (!empty($status)) {
				$status = $this->_transformStatus($status[0]->slug);
				$query = $wpdb->prepare("UPDATE {$wpdb->posts} SET post_status = %s WHERE ID = %d", array($status, $order->ID));
				$wpdb->query($query);
			}

			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($order->ID, 'number', $order->ID)));
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($order->ID, 'updated_at', time())));

			// Update columns
			do {
				switch ($orders[$i]->meta_key) {
					case '_js_completed_date':
						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_key = %s, meta_value = %d WHERE meta_id = %d", array('completed_at', strtotime($orders[$i]->meta_value), $orders[$i]->meta_id)));
						break;
					case 'order_key':
						$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_id = %d", array('key', $orders[$i]->meta_id)));
						break;
					case 'order_data':
						$data = unserialize($orders[$i]->meta_value);

						// Migrate customer
						$customer = $this->wp->getPostMeta($order->ID, 'customer', true);
						$customer = $this->_migrateCustomer($customer, $data);
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($order->ID, 'customer', serialize(serialize($customer)))));

						// Migrate coupons
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($order->ID, 'coupons', $data['order_discount_coupons'])));

						// Migrate shipping method
						try {
							$method = $this->shippingService->get($data['shipping_method']);
							$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($order->ID, 'shipping',	serialize(array(
								'method' => $method->getState(),
								'price' => $data['order_shipping'],
								'rate' => '', // Rates are stored nowhere - so no rate here
							)))));
						} catch (Exception $e) {
							$this->messages->addWarning(sprintf(__('Shipping method "%s" not found. Order with ID "%d" has no shipping method now.'), $data['shipping_method'], $order->ID));
						}

						// Migrate payment method
						try {
							$method = $this->paymentService->get($data['payment_method']);
							$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($order->ID, 'payment',	$method->getId())));
						} catch (Exception $e) {
							$this->messages->addWarning(sprintf(__('Payment method "%s" not found. Order with ID "%d" has no payment method now.'), $data['payment_method'], $order->ID));
						}

						// Migrate order totals
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($order->ID, 'subtotal', $data['order_subtotal'])));
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($order->ID, 'discount', $data['order_discount'])));
						$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d, %s, %s)", array($order->ID, 'total', $data['order_total'])));
						break;
					case 'customer_user':
						$customer = $this->wp->getPostMeta($order->ID, 'customer', true);

						if ($customer !== false) {
							/** @var Customer $customer */
							$customer = unserialize(unserialize($customer));
							if (!$customer) {
								$customer = new Customer();
							}

							/** @var \WP_User $user */
							$user = $this->wp->getUserBy('id', $orders[$i]->meta_value);
							$customer->setId($user->ID);
							$customer->setLogin($user->get('login'));
							$customer->setEmail($user->get('user_email'));
							$customer->setName($user->get('display_name'));
							$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %d WHERE post_id = %d AND meta_key = %s", array(serialize(serialize($customer)), $orders[$i]->meta_id, 'customer')));
						}
						break;
					case 'order_items':
						$data = unserialize($orders[$i]->meta_value);
						$globalTaxRate = 0.0;

						foreach ($data as $itemData) {
							/** @var Product $product */
							$product = $this->productService->find($itemData['id']);

							$tax = 0.0;
							$taxRate = 0;
							$price = $itemData['cost']/$itemData['qty'];
							if (!empty($itemData['taxrate']) && $itemData['taxrate'] > 0) {
								$tax = $price * $itemData['taxrate'] / 100;
								$taxRate = $itemData['taxrate'];
							} else if ($itemData['cost'] < $itemData['cost_inc_tax']) {
								$tax = ($itemData['cost_inc_tax'] - $itemData['cost'])/$itemData['qty'];
								$taxRate = $tax / $itemData['cost'];
							}

							$globalTaxRate += $taxRate;

							$wpdb->insert($wpdb->prefix.'jigoshop_order_item', array(
								'order_id' => $order->ID,
								'product_id' => $product->getId(),
								'product_type' => $product->getType(),
								'title' => $itemData['name'],
								'price' => $price,
								'tax' => $tax,
								'quantity' => $itemData['qty'],
								'cost' => $itemData['cost'],
							));
							$itemId = $wpdb->insert_id;

							if (!empty($itemData['variation_id']) && $product instanceof Variable) {
								$wpdb->query($wpdb->prepare(
									"INSERT INTO {$wpdb->prefix}jigoshop_order_item_meta (item_id, meta_key, meta_value) VALUES (%d, %s, %s)",
									array($itemId, 'variation_id', $itemData['variation_id'])
								));

								/** @var Product\Variable\Variation $variationProduct */
								/** @var Product\Variable $product */
								$variationProduct = $product->getVariation($itemData['variation_id']);
								foreach ($itemData['variation'] as $variation => $variationValue) {
									$variation = str_replace('tax_', '', $variation);
									$attribute = $this->getAttribute($variationProduct, $variation);

									if ($attribute === null) {
										$this->messages->addWarning(sprintf(__('Attribute "%s" not found for variation ID "%d".', 'jigoshop'), $variation, $variationProduct->getId()));
										continue;
									}

									$option = $this->getAttributeOption($attribute, $variationValue);

									if ($option === null) {
										$this->messages->addWarning(sprintf(__('Attribute "%s" option "%s" not found for variation ID "%d".', 'jigoshop'), $variation, $variationValue, $variationProduct->getId()));
										continue;
									}

									$wpdb->query($wpdb->prepare(
										"INSERT INTO {$wpdb->prefix}jigoshop_order_item_meta (item_id, meta_key, meta_value) VALUES (%d, %s, %s)",
										array($itemId, $attribute->getAttribute()->getId(), $option->getId())
									));
								}
							}
						}

						$wpdb->query($wpdb->prepare(
							"INSERT INTO {$wpdb->prefix}jigoshop_order_tax (order_id, tax_class, rate, is_compound) VALUES (%d, %s, %d, %d)",
							array($order->ID, 'standard', $globalTaxRate/count($data), false)
						));
						break;
				}

				$i++;
			} while ($i < $endI && $orders[$i]->ID == $order->ID);
		}
	}

	private function _transformStatus($status)
	{
		switch ($status) {
			case 'pending':
				return Status::PENDING;
			case 'processing':
				return Status::PROCESSING;
			case 'completed':
				return Status::COMPLETED;
			case 'cancelled':
				return Status::CANCELLED;
			case 'refunded':
				return Status::REFUNDED;
			case 'on-hold':
			default:
				return Status::ON_HOLD;
		}
	}

	private function _migrateCustomer($customer, $data)
	{
		if (!$customer) {
			$customer = new Customer();
		} else {
			$customer = unserialize(unserialize($customer));
		}

		if (!($customer instanceof Customer)) {
			$customer = new Customer();
		}

		if (!empty($data['billing_company'])) {
			$address = new Customer\CompanyAddress();
			$address->setCompany($data['billing_company']);
			$address->setVatNumber($data['billing_euvatno']);
		} else {
			$address = new Customer\Address();
		}

		$address->setFirstName($data['billing_first_name']);
		$address->setLastName($data['billing_last_name']);
		$address->setAddress($data['billing_address_1'].' '.$data['billing_address_2']);
		$address->setCountry($data['billing_country']);
		$address->setState($data['billing_state']);
		$address->setPostcode($data['billing_postcode']);
		$address->setPhone($data['billing_phone']);
		$address->setEmail($data['billing_email']);
		$customer->setBillingAddress($address);

		if (!empty($data['shipping_company'])) {
			$address = new Customer\CompanyAddress();
			$address->setCompany($data['shipping_company']);
		} else {
			$address = new Customer\Address();
		}
		$address->setFirstName($data['shipping_first_name']);
		$address->setLastName($data['shipping_last_name']);
		$address->setAddress($data['shipping_address_1'].' '.$data['shipping_address_2']);
		$address->setCountry($data['shipping_country']);
		$address->setState($data['shipping_state']);
		$address->setPostcode($data['shipping_postcode']);

		$customer->setShippingAddress($address);

		return $customer;
	}

	/**
	 * @param $variationProduct Product\Variable\Variation Variation to search.
	 * @param $variation string Attribute slug to find.
	 * @return Product\Variable\Attribute|null Attribute found.
	 */
	private function getAttribute($variationProduct, $variation)
	{
		foreach ($variationProduct->getAttributes() as $attribute) {
			/** @var $attribute Product\Variable\Attribute */
			if ($attribute->getAttribute()->getSlug() == $variation) {
				return $attribute;
			}
		}

		return null;
	}

	/**
	 * @param $attribute Product\Variable\Attribute Attribute to search.
	 * @param $value string Option to find.
	 * @return \Jigoshop\Entity\Product\Attribute\Option|null Option found.
	 */
	private function getAttributeOption($attribute, $value)
	{
		foreach ($attribute->getAttribute()->getOptions() as $option) {
			/** @var $option Product\Attribute\Option */
			if ($option->getValue() == $value) {
				return $option;
			}
		}

		return null;
	}
}
