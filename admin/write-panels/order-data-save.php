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
 * @package    Jigoshop
 * @category   Admin
 * @author     Jigowatt
 * @copyright  Copyright (c) 2011 Jigowatt Ltd.
 * @license    http://jigoshop.com/license/commercial-edition
 */
add_action('jigoshop_process_shop_order_meta', 'jigoshop_process_shop_order_meta', 1, 2);

function jigoshop_process_shop_order_meta($post_id, $post)
{
    global $wpdb;

    $jigoshop_errors = array();

    $order = &new jigoshop_order($post_id);

    // Get old data + attributes
    $data = (array) maybe_unserialize(get_post_meta($post_id, 'order_data', true));
    
    //Get old order items
    $old_order_items = (array) maybe_unserialize(get_post_meta($post_id, 'order_items', true));

    // Add/Replace data to array
    $order_fields = array(
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
        'shipping_state',
        'shipping_method',
        'payment_method',
        'order_subtotal',
        'order_shipping',
        'order_discount',
        'order_tax',
        'order_shipping_tax',
        'order_total'
    );

    //run stripslashes on all valid fields
    foreach ($order_fields as $field_name) {
        $field_value = '';
        if(isset($_POST[$field_name])) {
            $field_value = $_POST[$field_name];
        }
        
        $data[$field_name] = stripslashes($_POST[$field_name]);
    }

    // Customer
    update_post_meta($post_id, 'customer_user', (int) $_POST['customer_user']);

    // Order status
    $order->update_status($_POST['order_status']);

    // Order items
    $order_items = array();
    
    if (isset($_POST['item_id'])) {
        $item_id = $_POST['item_id'];
        $item_variation = $_POST['item_variation_id'];
        $item_name = $_POST['item_name'];
        $item_quantity = $_POST['item_quantity'];
        $item_cost = $_POST['item_cost'];
        $item_tax_rate = $_POST['item_tax_rate'];

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
                'id' => htmlspecialchars(stripslashes($item_id[$i])),
                'variation_id' => $variation_id,
                'variation' => $variation,
                'name' => htmlspecialchars(stripslashes($item_name[$i])),
                'qty' => (int) $item_quantity[$i],
                'cost' => number_format((float)jigowatt_clean($item_cost[$i]), 2),
                'taxrate' => number_format(jigowatt_clean($item_tax_rate[$i]), 4)
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
                    if (get_option('jigoshop_notify_no_stock_amount') && get_option('jigoshop_notify_no_stock_amount') >= $new_quantity) {
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
        jigoshop_pay_for_order_customer_notification($order->id);
    }

    // Error Handling
    if (count($jigoshop_errors) > 0) {
        update_option('jigoshop_errors', $jigoshop_errors);
    }
}