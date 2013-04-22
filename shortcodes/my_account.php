<?php
/**
 * My Account shortcode
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Customer
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

function get_jigoshop_my_account($atts) {
    return jigoshop_shortcode_wrapper('jigoshop_my_account', $atts);
}

function jigoshop_my_account( $atts ) {

	global $post, $current_user;
    $jigoshop_options = Jigoshop_Base::get_options();

	extract(shortcode_atts(array(
    'recent_orders' => 5
	), $atts));

	ob_start();
	
  	$recent_orders = ('all' == $recent_orders) ? -1 : $recent_orders;

	get_currentuserinfo();

	jigoshop::show_messages();

	if (is_user_logged_in()) :

		?>
		<p><?php echo sprintf( __('Hello, <strong>%s</strong>. From your account dashboard you can view your recent orders, manage your shipping and billing addresses and <a href="%s">change your password</a>.', 'jigoshop'), $current_user->display_name, apply_filters('jigoshop_get_change_password_page_id', get_permalink(jigoshop_get_page_id('change_password')))); ?></p>

		<?php do_action('jigoshop_before_my_account'); ?>

		<?php if ($downloads = jigoshop_customer::get_downloadable_products()) : ?>
		<h2><?php _e('Available downloads', 'jigoshop'); ?></h2>
		<ul class="digital-downloads">
			<?php foreach ($downloads as $download) : ?>
				<li><?php if (is_numeric($download['downloads_remaining'])) : ?><span class="count"><?php echo $download['downloads_remaining'] . _n(' download Remaining', ' downloads Remaining', 'jigoshop'); ?></span><?php endif; ?> <a href="<?php echo esc_url( $download['download_url'] ); ?>"><?php echo $download['download_name']; ?></a></li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>


		<h2><?php _e('Recent Orders', 'jigoshop'); ?></h2>
		<table class="shop_table my_account_orders">

			<thead>
				<tr>
					<th><span class="nobr"><?php _e('#', 'jigoshop'); ?></span></th>
					<th><span class="nobr"><?php _e('Date', 'jigoshop'); ?></span></th>
					<?php if ( $jigoshop_options->get_option( 'jigoshop_calc_shipping' ) == 'yes' ) : ?>
						<th><span class="nobr"><?php _e('Ship to', 'jigoshop'); ?></span></th>
					<?php endif; ?>
					<th><span class="nobr"><?php _e('Total', 'jigoshop'); ?></span></th>
					<th colspan="2"><span class="nobr"><?php _e('Status', 'jigoshop'); ?></span></th>
				</tr>
			</thead>

			<tbody><?php
				$jigoshop_orders = new jigoshop_orders();
				$jigoshop_orders->get_customer_orders( get_current_user_id(), $recent_orders );
				if ($jigoshop_orders->orders) foreach ($jigoshop_orders->orders as $order) :
				
					if ($order->status=='pending') {
						foreach ( $order->items as $item ) {
							$_product = $order->get_product_from_item( $item );
							$temp = new jigoshop_product( $_product->ID );
							if ( $temp->managing_stock() && (!$temp->is_in_stock()
								|| !$temp->has_enough_stock($item['qty']) ) ) {
								$order->cancel_order( sprintf(__("Product - %s - is now out of stock -- Canceling Order", 'jigoshop'), $_product->get_title() ) );
								$junk = ob_get_clean();
								wp_safe_redirect( apply_filters( 'jigoshop_get_myaccount_page_id', get_permalink( jigoshop_get_page_id( 'myaccount' ))) );
								exit;
							}
						}
					}
					?><tr class="order">
						<td><?php echo $order->get_order_number(); ?></td>
						<td><time title="<?php echo esc_attr( date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($order->order_date)) ); ?>"><?php echo date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($order->order_date)); ?></time></td>
						<?php if ( $jigoshop_options->get_option( 'jigoshop_calc_shipping' ) == 'yes' ) : ?>
							<td><address>
							<?php if ($order->formatted_shipping_address) echo $order->formatted_shipping_address; else echo '&ndash;'; ?>
							</address></td>
						<?php endif; ?>
						<td><?php echo apply_filters( 'jigoshop_display_order_total', jigoshop_price($order->order_total), $order); ?></td>
						<td class="nobr"><?php _e($order->status, 'jigoshop'); ?></td>
						<td class="nobr alignright">
							<?php
								if ($order->status=='pending') {
								?>
									<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e('Pay', 'jigoshop'); ?></a>
									<a href="<?php echo esc_url( $order->get_cancel_order_url() ); ?>" class="button cancel"><?php _e('Cancel', 'jigoshop'); ?></a>
								<?php
								}
							?>
							<a href="<?php echo esc_url( add_query_arg('order', $order->id, apply_filters('jigoshop_get_view_order_page_id', get_permalink(jigoshop_get_page_id('view_order')))) ); ?>" class="button"><?php _e('View', 'jigoshop'); ?></a>
						</td>
					</tr><?php
				endforeach;
			?></tbody>

		</table>

		<?php echo ob_get_clean(); /* all orders have been rendered, dump them out */ ?>

		<h2><?php _e('My Addresses', 'jigoshop'); ?></h2>
		<p><?php _e('The following addresses will be used on the checkout page by default.', 'jigoshop'); ?></p>
		<div class="col2-set addresses">

			<div class="col-1">

				<header class="title">
					<h3><?php _e('Billing Address', 'jigoshop'); ?></h3>
					<a href="<?php echo esc_url( add_query_arg('address', 'billing', apply_filters('jigoshop_get_edit_address_page_id', get_permalink(jigoshop_get_page_id('edit_address')))) ); ?>" class="edit"><?php _e('Edit', 'jigoshop'); ?></a>
				</header>
				<address>
					<?php
						if (isset(jigoshop_countries::$countries->countries[get_user_meta( get_current_user_id(), 'billing-country', true )])) $country = jigoshop_countries::$countries->countries[get_user_meta( get_current_user_id(), 'billing-country', true )]; else $country = '';
						$address = array(
							get_user_meta( get_current_user_id(), 'billing-first_name', true ) . ' ' . get_user_meta( get_current_user_id(), 'billing-last_name', true )
							,get_user_meta( get_current_user_id(), 'billing-company', true )
							,get_user_meta( get_current_user_id(), 'billing-address', true )
							,get_user_meta( get_current_user_id(), 'billing-address2', true )
							,get_user_meta( get_current_user_id(), 'billing-city', true )
							,get_user_meta( get_current_user_id(), 'billing-state', true )
							,get_user_meta( get_current_user_id(), 'billing-postcode', true )
							,$country
						);
						$address = array_map('trim', $address);
						$formatted_address = array();
						foreach ($address as $part) if (!empty($part)) $formatted_address[] = $part;
						$formatted_address = implode(', ', $formatted_address);
						if (!$formatted_address) _e('You have not set up a billing address yet.', 'jigoshop'); else echo $formatted_address;
					?>
				</address>

			</div><!-- /.col-1 -->

			<div class="col-2">

				<header class="title">
					<h3><?php _e('Shipping Address', 'jigoshop'); ?></h3>
					<a href="<?php echo esc_url( add_query_arg('address', 'shipping', apply_filters('jigoshop_get_edit_address_page_id', get_permalink(jigoshop_get_page_id('edit_address')))) ); ?>" class="edit"><?php _e('Edit', 'jigoshop'); ?></a>
				</header>
				<address>
					<?php
						if (isset(jigoshop_countries::$countries->countries[get_user_meta( get_current_user_id(), 'shipping-country', true )])) $country = jigoshop_countries::$countries->countries[get_user_meta( get_current_user_id(), 'shipping-country', true )]; else $country = '';
						$address = array(
							get_user_meta( get_current_user_id(), 'shipping-first_name', true ) . ' ' . get_user_meta( get_current_user_id(), 'shipping-last_name', true )
							,get_user_meta( get_current_user_id(), 'shipping-company', true )
							,get_user_meta( get_current_user_id(), 'shipping-address', true )
							,get_user_meta( get_current_user_id(), 'shipping-address2', true )
							,get_user_meta( get_current_user_id(), 'shipping-city', true )
							,get_user_meta( get_current_user_id(), 'shipping-state', true )
							,get_user_meta( get_current_user_id(), 'shipping-postcode', true )
							,$country
						);
						$address = array_map('trim', $address);
						$formatted_address = array();
						foreach ($address as $part) if (!empty($part)) $formatted_address[] = $part;
						$formatted_address = implode(', ', $formatted_address);
						if (!$formatted_address) _e('You have not set up a shipping address yet.', 'jigoshop'); else echo $formatted_address;
					?>
				</address>

			</div><!-- /.col-2 -->

		</div><!-- /.col2-set -->
		<?php

		do_action('jigoshop_after_my_account');

	else :

		jigoshop_login_form();

	endif;
	
}

function get_jigoshop_edit_address() {
    return jigoshop_shortcode_wrapper('jigoshop_edit_address');
}

function jigoshop_edit_address() {

	$user_id = get_current_user_id();

	if ( is_user_logged_in() ) :

		if ( isset($_GET['address']) ) $load_address = $_GET['address']; else $load_address = 'billing';
		if ( $load_address == 'billing' ) $load_address = 'billing'; else $load_address = 'shipping';
		$address = array(
			array(
				'name'        => $load_address . '-first_name',
				'label'       => __('First Name', 'jigoshop'),
				'placeholder' => __('First Name', 'jigoshop'),
				'required'    => true,
				'class'       => array('form-row-first'),
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-first_name', true )),
			array(
				'name'        => $load_address . '-last_name',
				'label'       => __('Last Name', 'jigoshop'),
				'placeholder' => __('Last Name', 'jigoshop'),
				'required'    => true,
				'class'       => array('form-row-last columned'),
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-last_name', true )),
			array(
				'name'        => $load_address . '-company',
				'label'       => __('Company', 'jigoshop'),
				'placeholder' => __('Company', 'jigoshop'),
				'class'       => array('columned full-row clear'),
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-company_name', true ) ),
			array(
				'name'        => $load_address . '-address',
				'label'       => __('Address', 'jigoshop'),
				'placeholder' => __('Address 1', 'jigoshop'),
				'required'    => true,
				'class'       => array('form-row-first'),
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-address', true ) ),
			array(
				'name'        => $load_address . '-address2',
				'label'       => __('Address 2', 'jigoshop'),
				'placeholder' => __('Address 2', 'jigoshop'),
				'class'       => array('form-row-last'),
				'label_class' => array('hidden'),
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-address2', true ) ),
			array(
				'name'        => $load_address . '-city',
				'label'       => __('City', 'jigoshop'),
				'placeholder' => __('City', 'jigoshop'),
				'required'    => true,
				'class'       => array('form-row-first'),
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-city', true ) ),
			array(
				'type'        => 'postcode',
				'validate'    => 'postcode',
				'format'      => 'postcode',
				'name'        => $load_address . '-postcode',
				'label'       => __('Postcode', 'jigoshop'),
				'placeholder' => __('Postcode', 'jigoshop'),
				'required'    => true,
				'class'       => array('form-row-last'),
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-postcode', true ) ),
			array(
				'type'        => 'country',
				'name'        => $load_address . '-country',
				'label'       => __('Country', 'jigoshop'),
				'required'    => true,
				'class'       => array('form-row-first'),
				'rel'         => $load_address . '-state',
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-country', true ) ),
			array(
				'type'        => 'state',
				'name'        => $load_address . '-state',
				'label'       => __('State/County', 'jigoshop'),
				'required'    => true,
				'class'       => array('form-row-last'),
				'rel'         => $load_address . '-country',
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-state', true ) ),
			array(
				'name'        => $load_address . '-email',
				'validate'    => 'email',
				'label'       => __('Email Address', 'jigoshop'),
				'placeholder' => __('you@yourdomain.com', 'jigoshop'),
				'required'    => true,
				'class'       => array('form-row-first'),
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-email', true ) ),
			array(
				'name'        => $load_address . '-phone',
				'validate'    => 'phone',
				'label'       => __('Phone', 'jigoshop'),
				'placeholder' => __('Phone number', 'jigoshop'),
				'required'    => true,
				'class'       => array('form-row-last'),
				'value'       => get_user_meta( get_current_user_id(), $load_address . '-phone', true ) )
		);
		$address = apply_filters( 'jigoshop_customer_account_address_fields', $address );

		if ($_POST) :

			if ( $user_id > 0 && jigoshop::verify_nonce('edit_address') ) :
				foreach ( $address as $field ){
					if ( $_POST[$field['name']] ):
						update_user_meta( $user_id, $field['name'], jigowatt_clean($_POST[$field['name']]) );
					endif;
				}
			endif;

			wp_safe_redirect( apply_filters( 'jigoshop_get_myaccount_page_id', get_permalink( jigoshop_get_page_id( 'myaccount' ))) );
			exit;

		endif;

		?>
		<form action="<?php echo esc_url( add_query_arg('address', $load_address, apply_filters('jigoshop_get_edit_address_page_id', get_permalink(jigoshop_get_page_id('edit_address')))) ); ?>" method="post">
			<?php jigoshop_customer::address_form($load_address, $address);?>
			<?php jigoshop::nonce_field('edit_address') ?>
			<input type="submit" class="button" name="save_address" value="<?php _e('Save Address', 'jigoshop'); ?>" />

		</form>
		<?php

	else :

		wp_safe_redirect( apply_filters( 'jigoshop_get_myaccount_page_id', get_permalink( jigoshop_get_page_id( 'myaccount' )) ));
		exit;

    endif;
}

function get_jigoshop_change_password() {
    return jigoshop_shortcode_wrapper('jigoshop_change_password');
}

function jigoshop_change_password() {

    $user_id = get_current_user_id();

    if (is_user_logged_in()) :

        if ($_POST) :

            if ($user_id > 0 && jigoshop::verify_nonce('change_password')) :

                if ($_POST['password-1'] && $_POST['password-2']) :

                    if ($_POST['password-1'] == $_POST['password-2']) :

                        wp_update_user(array('ID' => $user_id, 'user_pass' => $_POST['password-1']));

                        wp_safe_redirect( apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id('myaccount')) ));

                        exit;

                    else :

                        jigoshop::add_error(__('Passwords do not match.', 'jigoshop'));

                    endif;

                else :

                    jigoshop::add_error(__('Please enter your password.', 'jigoshop'));

                endif;

            endif;
        endif;

        jigoshop::show_messages();

		?>
		<form action="<?php echo esc_url( apply_filters('jigoshop_get_change_password_page_id', get_permalink(jigoshop_get_page_id('change_password'))) ); ?>" method="post">

			<p class="form-row form-row-first">
				<label for="password-1"><?php _e('New password', 'jigoshop'); ?> <span class="required">*</span></label>
				<input type="password" class="input-text" name="password-1" id="password-1" />
			</p>
			<p class="form-row form-row-last">
				<label for="password-2"><?php _e('Re-enter new password', 'jigoshop'); ?> <span class="required">*</span></label>
				<input type="password" class="input-text" name="password-2" id="password-2" />
			</p>
			<div class="clear"></div>
			<?php jigoshop::nonce_field('change_password')?>
			<p><input type="submit" class="button" name="save_password" value="<?php _e('Save', 'jigoshop'); ?>" /></p>

		</form>

		<?php
    else :
		wp_safe_redirect( apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id('myaccount')) ));
		exit;

    endif;
}

function get_jigoshop_view_order() {
    return jigoshop_shortcode_wrapper('jigoshop_view_order');
}

function jigoshop_view_order() {

    $jigoshop_options = Jigoshop_Base::get_options();
    $user_id = get_current_user_id();

    if (is_user_logged_in()) {

        if (isset($_GET['order']))
            $order_id = (int) $_GET['order'];
        else
            $order_id = 0;

        $order = new jigoshop_order($order_id);

        if ($order_id > 0 && $order->user_id == get_current_user_id()) {
        
            do_action('jigoshop_before_order_summary_details', $order->id);
            echo '<p>' . sprintf(__('Order <mark>%s</mark> made on <mark>%s</mark>.', 'jigoshop'), $order->get_order_number(), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->order_date))) . ' ';
            echo sprintf(__('Order status: <mark class="%s">%s</mark>', 'jigoshop'), sanitize_title($order->status), __($order->status, 'jigoshop') );

            echo '.</p>';
           	
			do_action( 'jigoshop_tracking_details_info', $order );
			
			?>
			<h2><?php _e('Order Details', 'jigoshop'); ?></h2>
            <table class="shop_table">
                <thead>
                    <tr>
                        <th><?php _e('ID/SKU', 'jigoshop'); ?></th>
                        <th><?php _e('Product', 'jigoshop'); ?></th>
                        <th><?php _e('Qty', 'jigoshop'); ?></th>
                        <th><?php _e('Totals', 'jigoshop'); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                    <?php if (($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax())
                            || ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0)) : ?>
                            <td colspan="3"><strong><?php _e('Retail Price', 'jigoshop'); ?></strong></td>
                    <?php else : ?>
                            <td colspan="3"><strong><?php _e('Subtotal', 'jigoshop'); ?></strong></td>
                    <?php endif; ?>
                        <td><strong><?php echo $order->get_subtotal_to_display(); ?></strong></td>
                    </tr>
            <?php if ($order->order_shipping > 0) : ?>
                <tr>
                    <td colspan="3"><?php _e('Shipping', 'jigoshop'); ?></td>
                    <td><?php echo $order->get_shipping_to_display(); ?></small></td>
                </tr>
            <?php
            endif;
			
            do_action('jigoshop_processing_fee_after_shipping');
			
            if ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0) : ?><tr class="discount">
                <td colspan="3"><?php _e('Discount', 'jigoshop'); ?></td>
                <td>-<?php echo jigoshop_price($order->order_discount); ?></td>
            </tr><?php endif;
            if (($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes' && $order->has_compound_tax())
              || ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'yes' && $order->order_discount > 0)) :
                ?><tr>
                    <td colspan="3"><strong><?php _e('Subtotal', 'jigoshop'); ?></strong></td>
                    <td><strong><?php echo jigoshop_price($order->order_discount_subtotal); ?></strong></td>
                </tr>
                <?php
            endif;
            if ($jigoshop_options->get_option('jigoshop_calc_taxes') == 'yes') :
                foreach ($order->get_tax_classes() as $tax_class) :
                    if ($order->show_tax_entry($tax_class)) : ?>
                        <tr>
                            <td colspan="3"><?php echo $order->get_tax_class_for_display($tax_class) . ' (' . (float) $order->get_tax_rate($tax_class) . '%):'; ?></td>
                            <td><?php echo $order->get_tax_amount($tax_class) ?></td>
                        </tr>
                        <?php
                    endif;
                endforeach;
            endif;
            if ($jigoshop_options->get_option('jigoshop_tax_after_coupon') == 'no' && $order->order_discount > 0) : ?><tr class="discount">
                <td colspan="3"><?php _e('Discount', 'jigoshop'); ?></td>
                <td>-<?php echo jigoshop_price($order->order_discount); ?></td>
            </tr><?php endif; ?>
                    <tr>
                        <td colspan="3"><strong><?php _e('Grand Total', 'jigoshop'); ?></strong></td>
                        <td><strong><?php echo jigoshop_price($order->order_total); ?></strong></td>
                    </tr>
                    <?php if ($order->customer_note) : ?>
                        <tr>
                            <td><strong><?php _e('Note:', 'jigoshop'); ?></strong></td>
                            <td colspan="3" style="text-align: left;"><?php echo wpautop(wptexturize($order->customer_note)); ?></td>
                        </tr>
                    <?php endif; ?>
                </tfoot>
                <tbody>
                    <?php
                    if (sizeof($order->items) > 0) :

                        foreach ($order->items as $item) :

                            if (isset($item['variation_id']) && $item['variation_id'] > 0) :
                                $_product = new jigoshop_product_variation($item['variation_id']);

                                if (is_array($item['variation'])) :
                                    $_product->set_variation_attributes($item['variation']);
                                endif;
                            else :
                                $_product = new jigoshop_product($item['id']);
                            endif;

                            echo '
								<tr>
								    <td>' . $_product->get_sku() . '</td>
									<td class="product-name">' . $item['name'];

                            if (isset($_product->variation_data)) :
                                echo jigoshop_get_formatted_variation($_product->variation_data);
                            endif;

                            do_action( 'jigoshop_display_item_meta_data', $item );

                            echo '	</td>
									<td>' . $item['qty'] . '</td>
									<td>' . jigoshop_price($item['cost'], array('ex_tax_label' => 1)) . '</td>
								</tr>';
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
			<?php do_action('jigoshop_before_order_customer_details', $order->id); ?>
            <header>
                <h2><?php _e('Customer details', 'jigoshop'); ?></h2>
            </header>
            <dl>
                <?php
                if ($order->billing_email)
                    echo '<dt>' . __('Email:', 'jigoshop') . '</dt><dd>' . $order->billing_email . '</dd>';
                if ($order->billing_phone)
                    echo '<dt>' . __('Telephone:', 'jigoshop') . '</dt><dd>' . $order->billing_phone . '</dd>';
                ?>
            </dl>
			<?php do_action('jigoshop_after_order_customer_details', $order->id); ?>
            <div class="col2-set addresses">

                <div class="col-1">

                    <header class="title">
                        <h3><?php _e('Shipping Address', 'jigoshop'); ?></h3>
                    </header>
			<?php do_action('jigoshop_before_order_shipping_address', $order->id); ?>
                    <address><p>
            <?php
            if (!$order->formatted_shipping_address)
                _e('N/A', 'jigoshop'); else
                echo $order->formatted_shipping_address;
            ?>
                        </p></address>
			<?php do_action('jigoshop_after_order_shipping_address', $order->id); ?>
                </div><!-- /.col-1 -->

                <div class="col-2">

                    <header class="title">
			<?php do_action('jigoshop_before_order_billing_address', $order->id); ?>
                        <h3><?php _e('Billing Address', 'jigoshop'); ?></h3>
                    </header>
                    <address><p>
            <?php
            if (!$order->formatted_billing_address)
                _e('N/A', 'jigoshop'); else
                echo $order->formatted_billing_address;
            ?>
                        </p></address>
			<?php do_action('jigoshop_after_order_billing_address', $order->id); ?>
                </div><!-- /.col-2 -->

            </div><!-- /.col2-set -->

            <div class="clear"></div>

            <?php
            
        } else {

			wp_safe_redirect( apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id('myaccount')) ));
			exit;

        }

    } else {

		wp_safe_redirect( apply_filters('jigoshop_get_myaccount_page_id', get_permalink(jigoshop_get_page_id('myaccount')) ));
		exit;
    }
    
}