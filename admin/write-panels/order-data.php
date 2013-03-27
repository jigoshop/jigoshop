<?php
/**
 * Order Data
 *
 * Functions for displaying the order data meta box
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Admin
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

/**
 * Order data meta box
 *
 * Displays the meta box
 *
 * @since 		1.0
 */
function jigoshop_order_data_meta_box($post) {

	global $post, $wpdb, $thepostid;
	add_action('admin_footer', 'jigoshop_meta_scripts');

	wp_nonce_field('jigoshop_save_data', 'jigoshop_meta_nonce');

    $data = (array) maybe_unserialize( get_post_meta($post->ID, 'order_data', true) );

	$data['customer_user'] = (int) get_post_meta($post->ID, 'customer_user', true);

	$order_status = get_the_terms($post->ID, 'shop_order_status');
	if ($order_status) :
		$order_status = current($order_status);
		$data['order_status'] = $order_status->slug;
	else :
		$data['order_status'] = 'pending';
	endif;

	if (!isset($post->post_title) || empty($post->post_title)) :
		$order_title = 'Order';
	else :
		$order_title = $post->post_title;
	endif;

	?>
	<style type="text/css">
		#titlediv, #major-publishing-actions, #minor-publishing-actions { display:none }
	</style>
	<div class="panels jigoshop">
		<input name="post_title" type="hidden" value="<?php echo esc_attr( $order_title ); ?>" />
		<input name="post_status" type="hidden" value="publish" />

		<ul class="product_data_tabs tabs" style="display:none;">

			<li class="active"><a href="#order_data"><?php _e('Order', 'jigoshop'); ?></a></li>

			<li><a href="#order_customer_billing_data"><?php _e('Customer Billing Address', 'jigoshop'); ?></a></li>

			<li><a href="#order_customer_shipping_data"><?php _e('Customer Shipping Address', 'jigoshop'); ?></a></li>

			<?php do_action("jigoshop_order_data_tabs", $post, $data); ?>
			
		</ul>

		<div id="order_data" class="panel jigoshop_options_panel">

			<p class="form-field"><label for="order_status"><?php _e('Order status:', 'jigoshop') ?></label>
			<select id="order_status" name="order_status">
				<?php
					$statuses = (array) get_terms('shop_order_status', array('hide_empty' => 0, 'orderby' => 'id'));
					$names = jigoshop_order::get_order_statuses_and_names();
					foreach ($statuses as $status) :
						echo '<option value="'.esc_attr($status->slug).'" ';
						if ($status->slug==$data['order_status']) echo 'selected="selected"';
						echo '>'. $names[$status->name] .'</option>';
					endforeach;
				?>
			</select></p>
			<script type="text/javascript">
				/*<![CDATA[*/
					jQuery(function() {
						jQuery("#order_status").select2({ width: '150px' });
					});
				/*]]>*/
			</script>

			<p class="form-field"><label for="customer_user"><?php _e('Customer:', 'jigoshop') ?></label>
			<select id="customer_user" name="customer_user">
				<option value=""><?php _e('Guest', 'jigoshop') ?></option>
				<?php
					$users_fields = array( 'ID', 'display_name', 'user_email' );
					$users = new WP_User_Query( array( 'orderby' => 'display_name', 'fields' => $users_fields ) );
 					$users = $users->get_results();
					if ($users) foreach ( $users as $user ) :
						echo '<option value="'.esc_attr($user->ID).'" '; selected($data['customer_user'], $user->ID); echo '>' . $user->display_name . ' ('.$user->user_email.')</option>';
					endforeach;
				?>
			</select></p>
			<script type="text/javascript">
				/*<![CDATA[*/
					jQuery(function() {
						jQuery("#customer_user").select2({ width: '300px' });
					});
				/*]]>*/
			</script>

			<p class="form-field"><label for="excerpt"><?php _e('Customer Note:', 'jigoshop') ?></label>
				<textarea rows="1" cols="40" name="excerpt" tabindex="6" id="excerpt" placeholder="<?php _e('Customer\'s notes about the order', 'jigoshop'); ?>"><?php echo esc_textarea( $post->post_excerpt ); ?></textarea></p>
		</div>

		<div id="order_customer_billing_data" class="panel jigoshop_options_panel">
            <?php
				// display billing fields and values

                $billing_fields = array(
					'company'   => __('Company', 'jigoshop'),
					'first_name'=> __('First Name', 'jigoshop'),
					'last_name' => __('Last Name', 'jigoshop'),
					'address_1' => __('Address 1', 'jigoshop'),
					'address_2' => __('Address 2', 'jigoshop'),
					'city'      => __('City', 'jigoshop'),
					'postcode'  => __('Postcode', 'jigoshop'),
					'country'   => __('Country', 'jigoshop'),
					'state'     => __('State/County', 'jigoshop'),
					'phone'     => __('Tel', 'jigoshop'),
					'email'     => __('Email Address', 'jigoshop'),
				);

				if ( isset( $data['billing_euvatno'] )) {
					$start = $billing_fields;
					array_splice( $start, 1 );
					$end = $billing_fields;
					array_splice( $end, 0, 1 );
					$billing_fields = array_merge(
						$start,
						array( 'euvatno' => __('EU VAT Number', 'jigoshop') ),
						$end
					);
				}
				
                foreach($billing_fields as $field_id => $field_desc) {
                    $field_id = 'billing_' . $field_id;
                    $field_value = '';

                    if(isset($data[$field_id])) {
                        $field_value = $data[$field_id];
                    }

                    echo '<p class="form-field"><label for="' . esc_attr( $field_id ) . '">'.$field_desc.':</label>
				<input type="text" name="'.esc_attr($field_id).'" id="'.esc_attr($field_id).'" value="'.esc_attr($field_value).'" /></p>';
                }

			?>
		</div>

		<div id="order_customer_shipping_data" class="panel jigoshop_options_panel">

			<p class="form-field"><button class="button billing-same-as-shipping"><?php _e('Copy billing address to shipping address', 'jigoshop'); ?></button></p>
			<?php
            //display shipping fieds and values

                $shipping_fields = array(
					'company'   => __('Company', 'jigoshop'),
					'first_name'=> __('First Name', 'jigoshop'),
					'last_name' => __('Last Name', 'jigoshop'),
					'address_1' => __('Address 1', 'jigoshop'),
					'address_2' => __('Address 2', 'jigoshop'),
					'city'      => __('City', 'jigoshop'),
					'postcode'  => __('Postcode', 'jigoshop'),
					'country'   => __('Country', 'jigoshop'),
					'state'     => __('State/County', 'jigoshop')
				);

                foreach($shipping_fields as $field_id => $field_desc) {
                    $field_id = 'shipping_' . $field_id;
                    $field_value = '';

                    if(isset($data[$field_id])) {
                        $field_value = $data[$field_id];
                    }

                    echo '<p class="form-field"><label for="' . esc_attr( $field_id ) . '">'.$field_desc.':</label>
				<input type="text" name="'.esc_attr($field_id).'" id="'.esc_attr($field_id).'" value="'.esc_attr($field_value).'" /></p>';
                }
			?>
		</div>
		
		<?php do_action("jigoshop_order_data_panels", $post, $data); ?>
		
	</div>
	<?php

}

/**
 * Order items meta box
 *
 * Displays the order items meta box - for showing individual items in the order
 *
 * @since 		1.0
 */
function jigoshop_order_items_meta_box($post) {

	$order_items = (array) maybe_unserialize( get_post_meta($post->ID, 'order_items', true) );

	?>
	<div class="jigoshop_order_items_wrapper">
		<table cellpadding="0" cellspacing="0" class="jigoshop_order_items">
			<thead>
				<tr>
					<th class="product-id"><?php _e('ID', 'jigoshop'); ?></th>
					<th class="variation-id"><?php _e('Variation ID', 'jigoshop'); ?></th>
					<th class="product-sku"><?php _e('SKU', 'jigoshop'); ?></th>
					<th class="name"><?php _e('Name', 'jigoshop'); ?></th>
					<th class="variation"><?php _e('Variation', 'jigoshop'); ?></th>
					<!--<th class="meta"><?php _e('Order Item Meta', 'jigoshop'); ?></th>-->
					<?php do_action('jigoshop_admin_order_item_headers'); ?>
					<th class="quantity"><?php _e('Quantity', 'jigoshop'); ?></th>
					<th class="cost"><?php _e('Cost', 'jigoshop'); ?></th>
					<th class="tax"><?php _e('Tax Rate', 'jigoshop'); ?></th>
					<th class="center" width="1%"><?php _e('Remove', 'jigoshop'); ?></th>
				</tr>
			</thead>
			<tbody id="order_items_list">

				<?php if (sizeof($order_items)>0 && isset($order_items[0]['id'])) foreach ($order_items as $item) :

					if (isset($item['variation_id']) && $item['variation_id'] > 0) {
						$_product = new jigoshop_product_variation( $item['variation_id'] );
                        if(is_array($item['variation'])) {
                            $_product->set_variation_attributes($item['variation']);
                        }
                    } else {
						$_product = new jigoshop_product( $item['id'] );
                    }

					?>
					<tr class="item">
						<td class="product-id"><?php echo $item['id']; ?></td>
						<td class="variation-id"><?php if ( isset($item['variation_id']) ) echo $item['variation_id']; else echo '-'; ?></td>
						<td class="product-sku"><?php if ($_product->sku) echo $_product->sku; ?></td>
						<td class="name"><a href="<?php echo esc_url( admin_url('post.php?post='. $_product->id .'&action=edit') ); ?>"><?php echo $item['name']; ?></a>
							<?php
								if ( ! empty( $item['customization'] ) ) :

									$custom = $item['customization'];
									$label = apply_filters( 'jigoshop_customized_product_label', __(' Personal: ','jigoshop') );
									?>
										<div class="customization">
											<span class="customized_product_label"><?php echo $label; ?></span>
											<span class="customized_product"><?php echo $custom; ?></span>
										</div>
									<?php
								endif;
							?>
							</td>
						<td class="variation"><?php
							if (isset($_product->variation_data)) :
								echo jigoshop_get_formatted_variation( $_product->variation_data, true );
							elseif ( isset($item['variation']) && is_array($item['variation']) ) :
								foreach( $item['variation'] as $var ) {
									echo "{$var['name']} : {$var['value']}";
								}
							else :
								echo '-';
							endif;
						?></td>
						<!--<td>
							<table class="meta" cellspacing="0">
								<tfoot>
									<tr>
										<td colspan="3"><button class="add_meta button"><?php _e('Add meta', 'jigoshop'); ?></button></td>
									</tr>
								</tfoot>
								<tbody></tbody>
							</table>
						</td>-->
						<?php do_action('jigoshop_admin_order_item_values', $_product, $item, $post->ID); ?>
						<td class="quantity">
                            <input type="text" name="item_quantity[]" placeholder="<?php _e('Quantity e.g. 2', 'jigoshop'); ?>" value="<?php echo esc_attr( $item['qty'] ); ?>" />
                        </td>
						<td class="cost">
                            <input type="text" name="item_cost[]" placeholder="<?php _e('Cost per unit ex. tax e.g. 2.99', 'jigoshop'); ?>" value="<?php echo esc_attr( $item['cost'] ); ?>" />
                        </td>
						<td class="tax">
                            <input type="text" name="item_tax_rate[]" placeholder="<?php _e('Tax Rate e.g. 20.0000', 'jigoshop'); ?>" value="<?php echo esc_attr( $item['taxrate'] ); ?>" />
                        </td>
						<td class="center">
							<input type="hidden" name="item_id[]" value="<?php echo esc_attr( $item['id'] ); ?>" />
							<input type="hidden" name="item_name[]" value="<?php echo esc_attr( $item['name'] ); ?>" />
                            <input type="hidden" name="item_variation_id[]" value="<?php if ($item['variation_id']) echo $item['variation_id']; else echo ''; ?>" />
							<button type="button" class="remove_row button">&times;</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<p class="buttons">
		<input type='text' class='item_id' name='order_product_select' id='order_product_select' value='' placeholder="<?php _e('Choose a Product', 'jigoshop'); ?>" />
		<script type="text/javascript">
			jQuery(function() {
				jQuery("#order_product_select").select2({
					minimumInputLength: 3,
					multiple: false,
					closeOnSelect: true,
					ajax: {
						url: "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
						dataType: 'json',
						quietMillis: 100,
						data: function(term, page) {
							return {
								term:       term,
								action:     'jigoshop_json_search_products_and_variations',
								security:   '<?php echo wp_create_nonce( "search-products" ); ?>'
							};
						},
						results: function( data, page ) {
							return { results: data };
						}
					},
					initSelection: function( element, callback ) {
						var stuff = {
							action:     'jigoshop_json_search_products_and_variations',
							security:   '<?php echo wp_create_nonce( "search-products" ); ?>',
							term:       element.val()
						};
						jQuery.ajax({
							type: 		'GET',
							url:        "<?php echo (!is_ssl()) ? str_replace('https', 'http', admin_url('admin-ajax.php')) : admin_url('admin-ajax.php'); ?>",
							dataType: 	"json",
							data: 		stuff,
							success: 	function( result ) {
								callback( result );
							}
						});
					}
				});
			});
		</script>

		<button type="button" class="button button-primary add_shop_order_item"><?php _e('Add item', 'jigoshop'); ?></button>
	</p>
	<p class="buttons buttons-alt">
		<button type="button" class="button button calc_totals"><?php _e('Calculate totals', 'jigoshop'); ?></button>
	</p>

	<div class="clear"></div>
	<?php

}

/**
 * Order actions meta box
 *
 * Displays the order actions meta box - buttons for managing order stock and sending the customer an invoice.
 *
 * @since 		1.0
 */
function jigoshop_order_actions_meta_box($post) {
	?>
	<ul class="order_actions">
		<li><input type="submit" class="button button-primary" name="save" value="<?php _e('Save Order', 'jigoshop'); ?>" /> <?php _e('- Save/update the order.', 'jigoshop'); ?></li>

		<li><input type="submit" class="button" name="reduce_stock" value="<?php _e('Reduce stock', 'jigoshop'); ?>" /> <?php _e('- Reduces stock for each item in the order; useful after manually creating an order or manually marking an order as complete/processing after payment.', 'jigoshop'); ?></li>
		<li><input type="submit" class="button" name="restore_stock" value="<?php _e('Restore stock', 'jigoshop'); ?>" /> <?php _e('- Restores stock for each item in the order; useful after refunding or canceling the entire order.', 'jigoshop'); ?></li>

		<li><input type="submit" class="button" name="invoice" value="<?php _e('Email invoice', 'jigoshop'); ?>" /> <?php _e('- Emails the customer order details and a payment link.', 'jigoshop'); ?></li>

		<li>
		<?php
		if ( current_user_can( "delete_post", $post->ID ) ) {
			if ( !EMPTY_TRASH_DAYS )
				$delete_text = __('Delete Permanently');
			else
				$delete_text = __('Move to Trash');
			?>
		<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link($post->ID) ); ?>"><?php echo $delete_text; ?></a><?php
		} ?>
		</li>
	</ul>
	<?php
}

/**
 * Order totals meta box
 *
 * Displays the order totals meta box
 *
 * @since 		1.0
 */
function jigoshop_order_totals_meta_box($post) {

    $_order = new jigoshop_order($post->ID);

    $coupons = array();
    $order_discount_coupons = (array)$_order->get_value_from_data('order_discount_coupons');
	if( ! empty( $order_discount_coupons )) {
		foreach ( $order_discount_coupons as $coupon ) {
			$coupons[] = isset( $coupon['code'] ) ? $coupon['code'] : '';
		}
	}
	?>
	<ul class="totals">
		<li class="left">
			<label><?php _e('Subtotal:', 'jigoshop'); ?></label>
			<input type="text" id="order_subtotal" name="order_subtotal" placeholder="0.00 <?php _e('(ex. tax)', 'jigoshop'); ?>" value="<?php echo esc_attr( $_order->get_value_from_data('order_subtotal') ); ?>" class="first" />
		</li>

		<li class="right">
			<label><?php _e('Discount: ', 'jigoshop'); ?><span class="applied-coupons-values"><?php echo implode( ',', $coupons ); ?></span></label>
			<input type="text" id="order_discount" name="order_discount" placeholder="0.00" value="<?php echo esc_attr( $_order->get_value_from_data('order_discount') ); ?>" />
		</li>
		<?php
			$shipping_methods = jigoshop_shipping::get_all_methods();
			$shipping_select = "<select id='shipping_method' name='shipping_method' class='last' data-placeholder=".__('Choose', 'jigoshop').">";
			$shipping_select .= "<option></option>";
			if ( ! empty( $shipping_methods )) foreach( $shipping_methods as $index => $method ) {
				$mark = '';
				if ( $_order->get_value_from_data('shipping_method') == $method->id ) {
					$mark = 'selected="selected"';
				}
				$shipping_select .= "<option value='{$method->id}' {$mark}>{$method->title}</option>";
			}
			$shipping_select .= "</select>";
		?>
		<li>
			<label><?php _e('Shipping:', 'jigoshop'); ?></label>
            <input type="text" id="order_shipping" name="order_shipping" placeholder="0.00 <?php _e('(ex. tax)', 'jigoshop'); ?>" value="<?php echo esc_attr( $_order->get_value_from_data('order_shipping') ); ?>" class="first" /> <?php echo $shipping_select; ?>
			<script type="text/javascript">
				/*<![CDATA[*/
					jQuery(function() {
						jQuery("#shipping_method").select2({ width: '120px' });
					});
				/*]]>*/
			</script>
        </li>

		<li class="left">
			<label><?php _e('Total Tax:', 'jigoshop'); ?></label>
			<input type="text" id="order_tax" name="order_tax_total" placeholder="0.00" value="<?php echo esc_attr( $_order->get_total_tax() ); ?>" class="first" />
		</li>

		<li class="right">
			<label><?php _e('Shipping Tax:', 'jigoshop'); ?></label>
			<input type="text" id="order_shipping_tax" name="order_shipping_tax" placeholder="0.00" value="<?php echo esc_attr( $_order->get_value_from_data('order_shipping_tax') ); ?>" class="first" />
		</li>
		<?php
			$payment_methods = jigoshop_payment_gateways::get_available_payment_gateways();
			$payment_select = "<select id='payment_method' name='payment_method' class='last' data-placeholder=".__('Choose', 'jigoshop').">";
			$payment_select .= "<option></option>";
			if ( ! empty( $payment_methods )) foreach( $payment_methods as $index => $method ) {
				$mark = '';
				if ( $_order->get_value_from_data('payment_method') == $method->id ) {
					$mark = 'selected="selected"';
				}
				$payment_select .= "<option value='{$method->id}' {$mark}>{$method->title}</option>";
			}
			$payment_select .= "</select>";
		?>
		<?php do_action( 'jigoshop_admin_order_totals_after_shipping', $post->ID ) ?>
		<li>
			<label><?php _e('Total:', 'jigoshop'); ?></label>
            <input type="text" id="order_total" name="order_total" placeholder="0.00" value="<?php echo esc_attr( $_order->get_value_from_data('order_total') ); ?>" class="first" /> <?php echo $payment_select; ?>
			<script type="text/javascript">
				/*<![CDATA[*/
					jQuery(function() {
						jQuery("#payment_method").select2({ width: '120px' });
					});
				/*]]>*/
			</script>
		</li>

	</ul>
	<div class="clear"></div>
	<?php
}