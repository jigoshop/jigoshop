<?php
/**
 * Flat rate shipping
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package		Jigoshop
 * @category	Checkout
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */
class flat_rate extends jigoshop_shipping_method {

	public function __construct() {
		
		$js_options = Jigoshop_Options::instance();
		
		$js_options->add_option('jigoshop_flat_rate_availability', 'all');
		$js_options->add_option('jigoshop_flat_rate_title', 'Flat Rate');
		$js_options->add_option('jigoshop_flat_rate_tax_status', 'taxable');
		
        $this->id 			= 'flat_rate';
        $this->enabled		= $js_options->get_option('jigoshop_flat_rate_enabled');
		$this->title 		= $js_options->get_option('jigoshop_flat_rate_title');
		$this->availability = $js_options->get_option('jigoshop_flat_rate_availability');
		$this->countries 	= $js_options->get_option('jigoshop_flat_rate_countries');
		$this->type 		= $js_options->get_option('jigoshop_flat_rate_type');
		$this->tax_status	= $js_options->get_option('jigoshop_flat_rate_tax_status');
		$this->cost 		= $js_options->get_option('jigoshop_flat_rate_cost');
		$this->fee 			= $js_options->get_option('jigoshop_flat_rate_handling_fee');

		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
    }

    public function calculate_shipping() {

		$js_options = Jigoshop_Options::instance();
		
    	$_tax = $this->get_tax();

    	$this->shipping_total 	= 0;
		$this->shipping_tax 	= 0;

    	if ($this->type=='order') :
			// Shipping for whole order
			$this->shipping_total = $this->cost + $this->get_fee( $this->fee, jigoshop_cart::$cart_contents_total );

			if ( $js_options->get_option('jigoshop_calc_taxes')=='yes' && $this->tax_status=='taxable' ) :

				$rate = $_tax->get_shipping_tax_rate();
				if ($rate>0) :
					$tax_amount = $_tax->calc_shipping_tax( $this->shipping_total, $rate );

					$this->shipping_tax = $this->shipping_tax + $tax_amount;
				endif;
			endif;
		else :
			// Shipping per item
			if (sizeof(jigoshop_cart::$cart_contents)>0) : foreach (jigoshop_cart::$cart_contents as $item_id => $values) :
				$_product = $values['data'];
				if ($_product->exists() && $values['quantity']>0 && $_product->product_type <> 'downloadable') :

					$item_shipping_price = ($this->cost + $this->get_fee( $this->fee, $_product->get_price() )) * $values['quantity'];

					$this->shipping_total = $this->shipping_total + $item_shipping_price;

					if ( $_product->is_shipping_taxable() && $this->tax_status=='taxable' ) :

						$rate = $_tax->get_shipping_tax_rate( $_product->data['tax_class'] );

						if ($rate>0) :

							$tax_amount = $_tax->calc_shipping_tax( $item_shipping_price, $rate );

							$this->shipping_tax = $this->shipping_tax + $tax_amount;

						endif;

					endif;

				endif;
			endforeach; endif;
		endif;
    }

    public function admin_options() {
    	?>
    	<thead><tr><th scope="col" width="200px"><?php _e('Flat Rates', 'jigoshop'); ?></th><th scope="col" class="desc"><?php _e('Flat rates let you define a standard rate per item, or per order.', 'jigoshop'); ?>&nbsp;</th></tr></thead>
    	<tr>
	        <td class="titledesc"><?php _e('Enable Flat Rate', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_flat_rate_enabled" id="jigoshop_flat_rate_enabled" style="min-width:100px;">
		            <option value="yes" <?php if ($js_options->get_option('jigoshop_flat_rate_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
		            <option value="no" <?php if ($js_options->get_option('jigoshop_flat_rate_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input type="text" name="jigoshop_flat_rate_title" id="jigoshop_flat_rate_title" style="min-width:50px;" value="<?php if ($value = $js_options->get_option('jigoshop_flat_rate_title')) echo $value; else echo 'Flat Rate'; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><?php _e('Type', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_flat_rate_type" id="jigoshop_flat_rate_type" style="min-width:100px;">
		            <option value="order" <?php if ($js_options->get_option('jigoshop_flat_rate_type') == 'order') echo 'selected="selected"'; ?>><?php _e('Per Order', 'jigoshop'); ?></option>
		            <option value="item" <?php if ($js_options->get_option('jigoshop_flat_rate_type') == 'item') echo 'selected="selected"'; ?>><?php _e('Per Item', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <?php $_tax = new jigoshop_tax(); ?>
	    <tr>
	        <td class="titledesc"><?php _e('Tax Status', 'jigoshop') ?>:</td>
	        <td class="forminp">
	        	<select name="jigoshop_flat_rate_tax_status">
	        		<option value="taxable" <?php if ($js_options->get_option('jigoshop_flat_rate_tax_status')=='taxable') echo 'selected="selected"'; ?>><?php _e('Taxable', 'jigoshop'); ?></option>
	        		<option value="none" <?php if ($js_options->get_option('jigoshop_flat_rate_tax_status')=='none') echo 'selected="selected"'; ?>><?php _e('None', 'jigoshop'); ?></option>
	        	</select>
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Cost excluding tax. Enter an amount, e.g. 2.50.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Cost', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input type="text" name="jigoshop_flat_rate_cost" id="jigoshop_flat_rate_cost" style="min-width:50px;" value="<?php if ($value = $js_options->get_option('jigoshop_flat_rate_cost')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Handling Fee', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input type="text" name="jigoshop_flat_rate_handling_fee" id="jigoshop_flat_rate_handling_fee" style="min-width:50px;" value="<?php if ($value = $js_options->get_option('jigoshop_flat_rate_handling_fee')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><?php _e('Method available for', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_flat_rate_availability" id="jigoshop_flat_rate_availability" style="min-width:100px;">
		            <option value="all" <?php if ($js_options->get_option('jigoshop_flat_rate_availability') == 'all') echo 'selected="selected"'; ?>><?php _e('All allowed countries', 'jigoshop'); ?></option>
		            <option value="specific" <?php if ($js_options->get_option('jigoshop_flat_rate_availability') == 'specific') echo 'selected="selected"'; ?>><?php _e('Specific Countries', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <?php
    	$countries = jigoshop_countries::$countries;
    	asort($countries);
    	$selections = (array) $js_options->get_option('jigoshop_flat_rate_countries');
    	?><tr class="multi_select_countries">
            <td class="titledesc"><?php _e('Specific Countries', 'jigoshop'); ?>:</td>
            <td class="forminp">
            	<div class="multi_select_countries"><ul><?php
        			if ($countries) foreach ($countries as $key=>$val) :

        				echo '<li><label><input type="checkbox" name="jigoshop_flat_rate_countries[]" value="' . esc_attr( $key ) . '" ';
        				if (in_array($key, $selections)) echo 'checked="checked"';
        				echo ' />'. __($val, 'jigoshop') .'</label></li>';

            		endforeach;
       			?></ul></div>
       		</td>
       	</tr>
       	<script type="text/javascript">
		jQuery(function() {
			jQuery('select#jigoshop_flat_rate_availability').change(function(){
				if (jQuery(this).val()=="specific") {
					jQuery(this).parent().parent().next('tr.multi_select_countries').show();
				} else {
					jQuery(this).parent().parent().next('tr.multi_select_countries').hide();
				}
			}).change();
		});
		</script>
    	<?php
    }

    public function process_admin_options() {
		$js_options = Jigoshop_Options::instance();
   		if(isset($_POST['jigoshop_flat_rate_tax_status'])) $js_options->set_option('jigoshop_flat_rate_tax_status', jigowatt_clean($_POST['jigoshop_flat_rate_tax_status']));
   		else $js_options->delete_option('jigoshop_flat_rate_tax_status');

   		if(isset($_POST['jigoshop_flat_rate_enabled'])) $js_options->set_option('jigoshop_flat_rate_enabled', jigowatt_clean($_POST['jigoshop_flat_rate_enabled']));
   		else $js_options->delete_option('jigoshop_flat_rate_enabled');
   		if(isset($_POST['jigoshop_flat_rate_title'])) $js_options->set_option('jigoshop_flat_rate_title', jigowatt_clean($_POST['jigoshop_flat_rate_title']));
   		else $js_options->delete_option('jigoshop_flat_rate_title');
   		if(isset($_POST['jigoshop_flat_rate_type'])) $js_options->set_option('jigoshop_flat_rate_type', jigowatt_clean($_POST['jigoshop_flat_rate_type']));
   		else $js_options->delete_option('jigoshop_flat_rate_type');
   		if(isset($_POST['jigoshop_flat_rate_cost'])) $js_options->set_option('jigoshop_flat_rate_cost', jigowatt_clean($_POST['jigoshop_flat_rate_cost']));
   		else $js_options->delete_option('jigoshop_flat_rate_cost');
   		if(isset($_POST['jigoshop_flat_rate_handling_fee'])) $js_options->set_option('jigoshop_flat_rate_handling_fee', jigowatt_clean($_POST['jigoshop_flat_rate_handling_fee']));
   		else $js_options->delete_option('jigoshop_flat_rate_handling_fee');

   		if(isset($_POST['jigoshop_flat_rate_availability'])) $js_options->set_option('jigoshop_flat_rate_availability', jigowatt_clean($_POST['jigoshop_flat_rate_availability']));
   		else $js_options->delete_option('jigoshop_flat_rate_availability');
	    if (isset($_POST['jigoshop_flat_rate_countries'])) $selected_countries = $_POST['jigoshop_flat_rate_countries'];
	    else $selected_countries = array();
	    $js_options->set_option('jigoshop_flat_rate_countries', $selected_countries);

    }

}

function add_flat_rate_method( $methods ) {
	$methods[] = 'flat_rate'; return $methods;
}

add_filter('jigoshop_shipping_methods', 'add_flat_rate_method' );
