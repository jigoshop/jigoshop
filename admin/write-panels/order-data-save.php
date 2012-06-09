<?php
/**
 * Order Data Save
 *
 * Function for processing and storing all order data.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */
add_action('jigoshop_process_shop_order_meta', 'jigoshop_process_shop_order_meta', 1, 2);

function jigoshop_process_shop_order_meta($post_id, $post) {

    global $wpdb;

    $jigoshop_errors = array();

    $order = new jigoshop_order($post_id);

    // Get old data + attributes
    $data = (array) maybe_unserialize(get_post_meta($post_id, 'order_data', true));

    //Get old order items
    $old_order_items = (array) maybe_unserialize(get_post_meta($post_id, 'order_items', true));

    // Order status
//    if ( $order->update_status($_POST['order_status'] ) && empty($_POST['invoice']) ) return; // there were errors with status changes, don't continue
	$order->update_status($_POST['order_status'] );

    // Add/Replace data to array
	$customerDetails = array(
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_postcode',
        'billing_country',
        'billing_state',
        'billing_email',
        'billing_phone',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_postcode',
        'shipping_country',
        'shipping_state'
	);

    $order_fields = array(
        'shipping_method',
        'shipping_service',
        'payment_method',
        'order_subtotal',
        'order_discount_subtotal',
        'order_shipping',
        'order_discount',
        'order_discount_coupons',
        'order_tax_total', // value from order totals for tax
        'order_shipping_tax',
        'order_total',
        'order_total_prices_per_tax_class_ex_tax'
    );

	/* Pre-fill the customer addresses */
	foreach($customerDetails as $key) :
		$order_fields[] = $key;

		/* Checks if this is a new order from "Add Order" button */
		if ( !empty($_POST['auto_draft']) && !empty($_POST['customer_user']) && empty($_POST[$key]) ) :

			/**
			 * Some nasty but necessary checks for finding the meta data. It's either this or a whole
			 * new list of arrays just for this checking bit.
			 */
			if      (strstr($key, 'billing_'))  $adr = str_replace('billing_' , 'billing-' , $key);
			else if (strstr($key, 'shipping_')) $adr = str_replace('shipping_', 'shipping-', $key);
			if      (strstr($adr, 'address_1')) $adr = str_replace('address_1', 'address'  , $adr);
			else if (strstr($adr, 'address_2')) $adr = str_replace('address_1', 'address2' , $adr);

			$data[$key] = get_user_meta( $_POST['customer_user'], $adr, true );

		endif;

	endforeach;

	//run stripslashes on all valid fields
	foreach ($order_fields as $field_name) :

		if ( isset( $_POST[$field_name] ) )
			$data[$field_name] = stripslashes( $_POST[$field_name] );

	endforeach;

    // if total tax has been modified from order tax, then create a customized tax array
    // just for the order. At this point, we no longer know about multiple tax classes.
    // Even if we used the old tax array data, we still don't know how to break down
    // the amounts since they're customized.
    if (isset($data['order_tax_total']) && $order->get_total_tax() != $data['order_tax_total']) :
        // need to create new tax array string
        $new_tax = $data['order_tax_total'];
        $data['order_tax'] = jigoshop_tax::create_custom_tax($data['order_total'] - $data['order_tax_total'], $data['order_tax_total'], $data['order_shipping_tax'], $data['order_tax_divisor']);
    endif;

    // Customer
    update_post_meta($post_id, 'customer_user', (int) $_POST['customer_user']);

    // Order items
    $order_items = array();

    if (isset($_POST['item_id'])) {
		$item_id        = $_POST['item_id'];
		$item_variation = $_POST['item_variation_id'];
		$item_name      = $_POST['item_name'];
		$item_quantity  = $_POST['item_quantity'];
		$item_cost      = $_POST['item_cost'];
		$item_tax_rate  = $_POST['item_tax_rate'];

        for ($i = 0; $i < count($item_id); $i++) {

            if (!isset($item_id[$i]) || !isset($item_name[$i]) || !isset($item_quantity[$i]) || !isset($item_cost[$i]) || !isset($item_tax_rate[$i])) {
                continue;
            }

            $variation_id = '';
            $variation = '';
            if(!empty($item_variation[$i])) {
                $variation_id = (int)$item_variation[$i];

                //if this is a variation, we should check if it is an old one
                //and copy the 'variation' field describing details of variation
                foreach($old_order_items as $old_item_index => $old_item) {
                    if($old_item['variation_id'] == $variation_id) {
                        $variation = $old_item['variation'];

                        unset($old_order_items[$old_item_index]);
                        break;
                    }
                }
            }

            $order_items[] = apply_filters('update_order_item', array(
				'id'          => htmlspecialchars(stripslashes($item_id[$i])),
				'variation_id'=> $variation_id,
				'variation'   => $variation,
				'name'        => htmlspecialchars(stripslashes($item_name[$i])),
				'qty'         => (int) $item_quantity[$i],
				'cost'        => number_format((float)jigowatt_clean($item_cost[$i]), 2),
				'cost_inc_tax'=> -1, //TODO: need to look at this action when manually adding order
				'taxrate'     => number_format((float)jigowatt_clean($item_tax_rate[$i]), 4)
                ));
        }
    }

    // Save
    update_post_meta($post_id, 'order_data', $data);
    update_post_meta($post_id, 'order_items', $order_items);

    // Handle button actions

    if (isset($_POST['reduce_stock']) && $_POST['reduce_stock'] && count($order_items) > 0) {

        $order->add_order_note(__('Manually reducing stock.', 'jigoshop'));

        foreach ($order_items as $order_item) {

            $_product = $order->get_product_from_item($order_item);

            if ($_product->exists) {

                if ($_product->managing_stock()) {

                    $old_stock = $_product->stock;

                    $new_quantity = $_product->reduce_stock($order_item['qty']);

                    $order->add_order_note(sprintf(__('Item #%s stock reduced from %s to %s.', 'jigoshop'), $order_item['id'], $old_stock, $new_quantity));

                    if ($new_quantity < 0) {
                    	if ( $old_stock < 0 ) $backorder_qty = $order_item['qty'];
                    	else $backorder_qty = $old_stock - $order_item['qty'];
						do_action( 'jigoshop_product_on_backorder_notification', $post_id, $order_item['id'], $backorder_qty );
                   }

                    // stock status notifications
                    if (get_option('jigoshop_notify_no_stock_amount') >= 0 && get_option('jigoshop_notify_no_stock_amount') >= $new_quantity) {
                        do_action('jigoshop_no_stock_notification', $order_item['id']);
                    } else if (get_option('jigoshop_notify_low_stock_amount') && get_option('jigoshop_notify_low_stock_amount') >= $new_quantity) {
                        do_action('jigoshop_low_stock_notification', $order_item['id']);
                    }
                }
            } else {

                $order->add_order_note(sprintf(__('Item %s %s not found, skipping.', 'jigoshop'), $order_item['id'], $order_item['name']));
            }
        }

        $order->add_order_note(__('Manual stock reduction complete.', 'jigoshop'));
    } else if (isset($_POST['restore_stock']) && $_POST['restore_stock'] && sizeof($order_items) > 0) {

        $order->add_order_note(__('Manually restoring stock.', 'jigoshop'));

        foreach ($order_items as $order_item) {

            $_product = $order->get_product_from_item($order_item);

            if ($_product->exists) {

                if ($_product->managing_stock()) {

                    $old_stock = $_product->stock;

                    $new_quantity = $_product->increase_stock($order_item['qty']);

                    $order->add_order_note(sprintf(__('Item #%s stock increased from %s to %s.', 'jigoshop'), $order_item['id'], $old_stock, $new_quantity));
                }
            } else {

                $order->add_order_note(sprintf(__('Item %s %s not found, skipping.', 'jigoshop'), $order_item['id'], $order_item['name']));
            }
        }

        $order->add_order_note(__('Manual stock restore complete.', 'jigoshop'));
    } else if (isset($_POST['invoice']) && $_POST['invoice']) {

        // Mail link to customer
        jigoshop_send_customer_invoice($order->id);
    }

    // Error Handling
    if (count($jigoshop_errors) > 0) {
        update_option('jigoshop_errors', $jigoshop_errors);
    }
}