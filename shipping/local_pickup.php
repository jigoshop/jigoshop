<?php
/**
 * Local pickup
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
class local_pickup extends jigoshop_shipping_method {

	public function __construct() {
		
		$js_options = Jigoshop_Options::instance();
		
		add_option('jigoshop_local_pickup_availability', 'all');
		add_option('jigoshop_local_pickup_title', 'Local Pickup');
		
        $this->id 			= 'local_pickup';
        $this->enabled		= $js_options->get_option('jigoshop_local_pickup_enabled');
		$this->title 		= $js_options->get_option('jigoshop_local_pickup_title');
		$this->availability = $js_options->get_option('jigoshop_local_pickup_availability');
		$this->countries 	= $js_options->get_option('jigoshop_local_pickup_countries');
		if (isset( jigoshop_session::instance()->chosen_shipping_method_id ) && jigoshop_session::instance()->chosen_shipping_method_id==$this->id) $this->chosen = true;

		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));

    }

    public function calculate_shipping() {
		$this->shipping_total 	= 0;
		$this->shipping_tax 	= 0;
		$this->shipping_label 	= $this->title;
    }

    public function admin_options() {
		$js_options = Jigoshop_Options::instance();
    	?>
    	<thead><tr><th scope="col" width="200px"><?php _e('Local pickup', 'jigoshop'); ?></th><th scope="col" class="desc">&nbsp;</th></tr></thead>
    	<tr>
	        <td class="titledesc"><?php _e('Enable local pickup', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_local_pickup_enabled" id="jigoshop_local_pickup_enabled" style="min-width:100px;">
		            <option value="yes" <?php if ($js_options->get_option('jigoshop_local_pickup_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
		            <option value="no" <?php if ($js_options->get_option('jigoshop_local_pickup_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input type="text" name="jigoshop_local_pickup_title" id="jigoshop_local_pickup_title" style="min-width:50px;" value="<?php if ($value = $js_options->get_option('jigoshop_local_pickup_title')) echo $value; else echo 'Local Pickup'; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><?php _e('Method available for', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_local_pickup_availability" id="jigoshop_local_pickup_availability" style="min-width:100px;">
		            <option value="all" <?php if ($js_options->get_option('jigoshop_local_pickup_availability') == 'all') echo 'selected="selected"'; ?>><?php _e('All allowed countries', 'jigoshop'); ?></option>
		            <option value="specific" <?php if ($js_options->get_option('jigoshop_local_pickup_availability') == 'specific') echo 'selected="selected"'; ?>><?php _e('Specific Countries', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <?php
    	$countries = jigoshop_countries::$countries;
    	$selections = $js_options->get_option('jigoshop_local_pickup_countries', array());
    	?><tr class="multi_select_countries">
            <td class="titledesc"><?php _e('Specific Countries', 'jigoshop'); ?>:</td>
            <td class="forminp">
            	<div class="multi_select_countries"><ul><?php
        			if ($countries) foreach ($countries as $key=>$val) :

        				echo '<li><label><input type="checkbox" name="jigoshop_local_pickup_countries[]" value="' . esc_attr( $key ) . '" ';
        				if (in_array($key, $selections)) echo 'checked="checked"';
        				echo ' />'. __($val, 'jigoshop') .'</label></li>';

            		endforeach;
       			?></ul></div>
       		</td>
       	</tr>
       	<script type="text/javascript">
		jQuery(function() {
			jQuery('select#jigoshop_local_pickup_availability').change(function(){
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
   		if(isset($_POST['jigoshop_local_pickup_enabled'])) $js_options->set_option('jigoshop_local_pickup_enabled', jigowatt_clean($_POST['jigoshop_local_pickup_enabled']));
   		else $js_options->delete_option('jigoshop_local_pickup_enabled');
   		if(isset($_POST['jigoshop_local_pickup_title'])) $js_options->set_option('jigoshop_local_pickup_title', jigowatt_clean($_POST['jigoshop_local_pickup_title']));
   		else $js_options->delete_option('jigoshop_local_pickup_title');
   		if(isset($_POST['jigoshop_local_pickup_availability'])) $js_options->set_option('jigoshop_local_pickup_availability', jigowatt_clean($_POST['jigoshop_local_pickup_availability']));
   		else $js_options->delete_option('jigoshop_local_pickup_availability');

	    if (isset($_POST['jigoshop_local_pickup_countries'])) $selected_countries = $_POST['jigoshop_local_pickup_countries'];
	    else $selected_countries = array();
	    $js_options->set_option('jigoshop_local_pickup_countries', $selected_countries);

    }

}

function add_local_pickup_method( $methods ) {
	$methods[] = 'local_pickup'; return $methods;
}

add_filter('jigoshop_shipping_methods', 'add_local_pickup_method' );