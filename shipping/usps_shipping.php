<?php
/**
 * USPS shipping
 *
 * This class handles talking to USPS to find the rates of shipping 
 * to a particular address from a specified address.
 *
 * @package    Jigoshop
 * @category   Checkout
 * @author     Jigowatt
 */
 
class usps_shipping extends jigoshop_calculable_shipping {

	public function __construct() { 
		parent::__construct();
        	$this->id 		= 'usps_shipping';
        	$this->enabled		= get_option('jigoshop_usps_shipping_enabled');
		$this->title 		= get_option('jigoshop_usps_shipping_title');
		$this->availability 	= get_option('jigoshop_usps_shipping_availability');
		$this->countries 	= get_option('jigoshop_usps_shipping_countries');
		$this->from_zip_or_pac  = get_option('jigoshop_usps_shipping_from_zip');
		$this->user_id 		= get_option('jigoshop_usps_shipping_user_id');
		$this->url		= get_option('jigoshop_usps_shipping_url');
		$this->services		= get_option('jigoshop_usps_shipping_services');
		
		/* not an exhaustive list. It can be extended if needed. this is only first cut */
		$this->usps_services = array('First Class', 'Priority', 'Express', 'Parcel');
		
		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
		add_option('jigoshop_usps_shipping_availability', 'all');
		add_option('jigoshop_usps_shipping_title', 'USPS shipping');
		
    } 
    
    public function calculate_shipping() {
    	
    	$this->calculate_rate(); 
    	$cheapest_price = $this->get_cheapest_price();
    	
    	if ($cheapest_price != NULL) :
    		$this->shipping_total = $cheapest_price;
    	else :
    		$this->enabled='no';  // return invalid amount for jigoshop_shipping to not include in calculation of cheapest shipping
    	endif;
 		
    } 

	// the function that actually will create the xml and send it to the server, parse return xml and store in rates array	
	protected function filter_services() {	
		$services_to_use = array();
		$index = 0;
		
		// cannot use FIRST CLASS if weight is greater than 13 ounces. Obviously if the weight is at or above 1 pound, the value will exceed 13
		if (jigoshop_cart::$cart_contents_weight*16 > 13) :
			for ($i=0; $i<count($this->services); $i++) {
				
				if ($service != 'FIRST CLASS') :
					$services_to_use[$index] = $this->services[$i];
					$index++;
				endif;
			}
					
		
		else :
			$services_to_use = $this->services;
		
	 	endif;
		return $services_to_use;

	 }

	/** 
	 * define xml specific to usps shipping api. For a better extension, this needs to be thought out more carefully for the future.
	 * For a start, this will handle the easy cases. In the future, there needs to be dimensions added to the products so that they
	 * can be used for calculating different attributes. Also, there should be the ability to add extra services to the shipping. 
	 * Eg. add insurance, Add tracking if not already present, etc.
	 */
	protected function create_mail_request($service)
	{
		$xml = 'API=RateV4&XML=<RateV4Request USERID="'.$this->user_id.'">';

		if (jigoshop_cart::$cart_contents_weight >= 1) {
			$pounds = round(jigoshop_cart::$cart_contents_weight);
			$ounces = 0;
		}
		else {
			$pounds = 0;
			$ounces = round(jigoshop_cart::$cart_contents_weight*16);
		}

		//TODO: need to refactor this method. In fact there should be an extra class that's created as a helper to 
		// create the xml
		
		$x = 1; // package id
		if($pounds > 70)
		{
			 while($pounds > 70)
			 {
				$xml .= '<Package ID="'.$x++.'p">
				<Service>'.$service.'</Service>
				<ZipOrigination>'.$this->from_zip_or_pac.'</ZipOrigination>
				<ZipDestination>'.jigoshop_customer::get_shipping_postcode().'</ZipDestination>
				<Pounds>70</Pounds>
				<Ounces>0</Ounces>
				<Container/>
				<Size>REGULAR</Size>
				<Width>15</Width>
				<Length>15</Length>
				<Height>2</Height>
				<Machinable>true</Machinable>
				</Package>';

				$pounds -= 70;
			}
			$xml .= '<Package ID="'.$x++.'p">
			<Service>'.$service.'</Service>
			<ZipOrigination>'.$this->from_zip_or_pac.'</ZipOrigination>
			<ZipDestination>'.jigoshop_customer::get_shipping_postcode().'</ZipDestination>
			<Pounds>'.$pounds.'</Pounds>
			<Ounces>'.$ounces.'</Ounces>
			<Container/>
			<Size>REGULAR</Size>
			<Width>15</Width>
			<Length>15</Length>
			<Height>2</Height>
			<Machinable>true</Machinable>
			</Package>';
		} else if ($ounces >0 && $ounces <= 13)
		{
			$xml .= '<Package ID="'.$x++.'p">
			<Service>'.$service.'</Service>';  
			if ($service == 'FIRST CLASS') $xml .= '<FirstClassMailType>PARCEL</FirstClassMailType>';
			$xml .= '<ZipOrigination>'.$this->from_zip_or_pac.'</ZipOrigination>
			<ZipDestination>'.jigoshop_customer::get_shipping_postcode().'</ZipDestination>
			<Pounds>'.$pounds.'</Pounds>
			<Ounces>'.$ounces.'</Ounces>
			<Container/>
			<Size>REGULAR</Size>
			<Machinable>true</Machinable>
			</Package>';
		}
		else {
			$xml .= '<Package ID="'.$x++.'p">
			<Service>'.$service.'</Service>
			<ZipOrigination>'.$this->from_zip_or_pac.'</ZipOrigination>
			<ZipDestination>'.jigoshop_customer::get_shipping_postcode().'</ZipDestination>
			<Pounds>'.$pounds.'</Pounds>
			<Ounces>'.$ounces.'</Ounces>
			<Container/>
			<Size>REGULAR</Size>
			<Width>15</Width>
			<Length>15</Length>
			<Height>2</Height>
			<Machinable>true</Machinable>
			</Package>';
		}

		$xml .= "</RateV4Request>";

		return $xml;
	}
	 
	/** 
	 * retrieve the rates into an array from the xml response
	 */
	protected function retrieve_rate_from_response($xml_response) {
	
		$rate = -1; // on error, -1 will be returned

		$response_array = $xml_response['RATEV4RESPONSE']; 
		if ($response_array != NULL) :
			foreach( $response_array as $key=>$value )
			{
				 foreach($response_array[$key] as $rk=>$rv)
				 {
				 	 if(is_array($response_array[$key][$rk]))
					 {
						 if ($rate < 0) $rate = 0;
						 $rate += $response_array[$key][$rk]['RATE'];
					 }
				 }
			 }
		endif;
                
                if ($rate == -1) :
                    $this->has_error = true;
                endif;
                
		return $rate;
		
	
	}
         
    public function admin_options() {
    	?>
    	<thead><tr><th scope="col" width="200px"><?php _e('USPS Shipping', 'jigoshop'); ?></th><th scope="col" class="desc"><?php _e('USPS shipping will calculate shipping totals for various shipping methods that USPS offers.', 'jigoshop'); ?>&nbsp;</th></tr></thead>
    	<tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('When USPS Shipping is enabled, set Enable shipping calculator on cart to yes.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Enable USPS Shipping', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_usps_shipping_enabled" id="jigoshop_usps_shipping_enabled" style="min-width:100px;">
		            <option value="yes" <?php if (get_option('jigoshop_usps_shipping_enabled') == 'yes') echo 'selected="selected"'; ?>><?php _e('Yes', 'jigoshop'); ?></option>
		            <option value="no" <?php if (get_option('jigoshop_usps_shipping_enabled') == 'no') echo 'selected="selected"'; ?>><?php _e('No', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('This controls the title which the user sees during checkout.','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('Method Title', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input type="text" name="jigoshop_usps_shipping_title" id="jigoshop_usps_shipping_title" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_usps_shipping_title')) echo $value; else echo 'USPS shipping'; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('This is the sellers zip code','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('From Zip', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input type="text" name="jigoshop_usps_shipping_from_zip" id="jigoshop_usps_shipping_from_zip" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_usps_shipping_from_zip')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Your assigned user ID from USPS when subscribing to their web tools API','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('USPS User ID', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input type="text" name="jigoshop_usps_shipping_user_id" id="jigoshop_usps_shipping_user_id" style="min-width:50px;" value="<?php if ($value = get_option('jigoshop_usps_shipping_user_id')) echo $value; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <td class="titledesc"><a href="#" tip="<?php _e('Enter the url to the USPS server whether test or production, whichever you are working on','jigoshop') ?>" class="tips" tabindex="99"></a><?php _e('USPS url', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <input type="text" name="jigoshop_usps_shipping_url" id="jigoshop_usps_shipping_url" style="width:400px;" value="<?php if ($value = get_option('jigoshop_usps_shipping_url')) echo $value; ?>" />
	        </td>
	    </tr>
	    <?php
	    	$selections = get_option('jigoshop_usps_shipping_services', array());
	    ?>
	    	<tr class="multi_select_countries">
	            <td class="titledesc"><?php _e('Specific USPS Services', 'jigoshop'); ?>:</td>
	            <td class="forminp">
	            	<div class="multi_select_countries"><ul><?php
	        			foreach ($this->usps_services as $val) :
	            			                    			
	        				echo '<li><label><input type="checkbox" name="jigoshop_usps_shipping_services[]" value="'. strtoupper($val) .'" ';
	        				if (in_array(strtoupper($val), $selections)) echo 'checked="checked"';
	        				echo ' />'. __($val, 'jigoshop') .'</label></li>';
	
	            			endforeach;
	       			?></ul></div>
	       		</td>
	       	</tr>
	        <td class="titledesc"><?php _e('Method available for', 'jigoshop') ?>:</td>
	        <td class="forminp">
		        <select name="jigoshop_usps_shipping_availability" id="jigoshop_usps_shipping_availability" style="min-width:100px;">
		            <option value="all" <?php if (get_option('jigoshop_usps_shipping_availability') == 'all') echo 'selected="selected"'; ?>><?php _e('All allowed countries', 'jigoshop'); ?></option>
		            <option value="specific" <?php if (get_option('jigoshop_usps_shipping_availability') == 'specific') echo 'selected="selected"'; ?>><?php _e('Specific Countries', 'jigoshop'); ?></option>
		        </select>
	        </td>
	    </tr>
	    <?php
    	$countries = jigoshop_countries::$countries;
    	asort($countries);
    	$selections = get_option('jigoshop_usps_shipping_countries', array());
    	?><tr class="multi_select_countries">
            <td class="titledesc"><?php _e('Specific Countries', 'jigoshop'); ?>:</td>
            <td class="forminp">
            	<div class="multi_select_countries"><ul><?php
        			if ($countries) foreach ($countries as $key=>$val) :
            			                    			
        				echo '<li><label><input type="checkbox" name="jigoshop_usps_shipping_countries[]" value="'. $key .'" ';
        				if (in_array($key, $selections)) echo 'checked="checked"';
        				echo ' />'. __($val, 'jigoshop') .'</label></li>';

            		endforeach;
       			?></ul></div>
       		</td>
       	</tr>
       	<script type="text/javascript">
		jQuery(function() {
			jQuery('select#jigoshop_usps_shipping_availability').change(function(){
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

	if(isset($_POST['jigoshop_usps_shipping_enabled'])) update_option('jigoshop_usps_shipping_enabled', jigowatt_clean($_POST['jigoshop_usps_shipping_enabled'])); else @delete_option('jigoshop_usps_shipping_enabled');
	if(isset($_POST['jigoshop_usps_shipping_title'])) update_option('jigoshop_usps_shipping_title', jigowatt_clean($_POST['jigoshop_usps_shipping_title'])); else @delete_option('jigoshop_usps_shipping_title');
	if(isset($_POST['jigoshop_usps_shipping_availability'])) update_option('jigoshop_usps_shipping_availability', jigowatt_clean($_POST['jigoshop_usps_shipping_availability'])); else @delete_option('jigoshop_flat_rate_availability');	    
    	if(isset($_POST['jigoshop_usps_shipping_from_zip'])) update_option('jigoshop_usps_shipping_from_zip', jigowatt_clean($_POST['jigoshop_usps_shipping_from_zip'])); else @delete_option('jigoshop_usps_shipping_from_zip');
    	if(isset($_POST['jigoshop_usps_shipping_user_id'])) update_option('jigoshop_usps_shipping_user_id', jigowatt_clean($_POST['jigoshop_usps_shipping_user_id'])); else @delete_option('jigoshop_usps_shipping_user_id');
    	if(isset($_POST['jigoshop_usps_shipping_url'])) update_option('jigoshop_usps_shipping_url', jigowatt_clean($_POST['jigoshop_usps_shipping_url'])); else @delete_option('jigoshop_usps_shipping_url');
    	
    	if(isset($_POST['jigoshop_usps_shipping_countries'])) $selected_countries = $_POST['jigoshop_usps_shipping_countries']; else $selected_countries = array();
        update_option('jigoshop_usps_shipping_countries', $selected_countries);
    	
    	if(isset($_POST['jigoshop_usps_shipping_services'])) $selected_services = $_POST['jigoshop_usps_shipping_services']; else $selected_services = array();
        update_option('jigoshop_usps_shipping_services', $selected_services);
   		
    }

}

function add_usps_shipping_method( $methods ) {
	$methods[] = 'usps_shipping'; return $methods;
}

add_filter('jigoshop_shipping_methods', 'add_usps_shipping_method' );