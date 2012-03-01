<?php
/**
 * Free shipping
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

function add_free_shipping_method( $methods ) {
	$methods[] = 'free_shipping';
	return $methods;
}
add_filter( 'jigoshop_shipping_methods', 'add_free_shipping_method' );


class free_shipping extends jigoshop_shipping_method {

	public function __construct() {
		
		$jsOptions = Jigoshop_Options::instance();
		
		$jsOptions->install_new_options( 'Shipping', $this->get_default_options() );
		
		$jsOptions->add_option( 'jigoshop_free_shipping_availability', 'all' );
		$jsOptions->add_option( 'jigoshop_free_shipping_title', 'Free Shipping' );
		
        $this->id 			= 'free_shipping';
        $this->enabled		= $jsOptions->get_option('jigoshop_free_shipping_enabled');
		$this->title 		= $jsOptions->get_option('jigoshop_free_shipping_title');
		$this->min_amount 	= $jsOptions->get_option('jigoshop_free_shipping_minimum_amount');
		$this->availability = $jsOptions->get_option('jigoshop_free_shipping_availability');
		$this->countries 	= $jsOptions->get_option('jigoshop_free_shipping_countries');
		
		if ( isset( jigoshop_session::instance()->chosen_shipping_method_id )
			&& jigoshop_session::instance()->chosen_shipping_method_id == $this->id ) {
			
			$this->chosen = true;
			
		}

		add_action( 'jigoshop_update_options', array( &$this, 'process_admin_options' ) );
		add_action( 'jigoshop_settings_scripts', array( &$this, 'admin_scripts' ) );

    }

	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *
	 * These should be installed on the Jigoshop_Optons 'Shipping' tab
	 *
	 */	
	public function get_default_options() {
	
		$defaults = array();
		
		// Define the Section name for the Jigoshop_Options
		$defaults[] = array( 'name' => __('Free Shipping', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Free Shipping','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_free_shipping_enabled',
			'std' 		=> 'no',
			'type' 		=> 'radio',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		$defaults[] = array(
			'name'		=> __('Method Title','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the title which the user sees during checkout.','jigoshop'),
			'id' 		=> 'jigoshop_free_shipping_title',
			'std' 		=> __('Free Shipping','jigoshop'),
			'type' 		=> 'text'
		);
		
		$defaults[] = array(
			'name'		=> __('Minimum Order Amount','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('Users will need to spend this amount to get free shipping. Leave blank to disable.','jigoshop'),
			'id' 		=> 'jigoshop_free_shipping_minimum_amount',
			'std' 		=> __('Free Shipping','jigoshop'),
			'type' 		=> 'text'
		);
		
		$defaults[] = array(
			'name'		=> __('Method available for','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_free_shipping_availability',
			'std' 		=> 'all',
			'type' 		=> 'select',
			'choices'	=> array(
				'all'			=> __('All allowed countries', 'jigoshop'),
				'specific'		=> __('Specific Countries', 'jigoshop')
			)
		);
		
		$defaults[] = array(
			'name'		=> __('Specific Countries','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_free_shipping_countries',
			'std' 		=> '',
			'type' 		=> 'multi_select_countries'
		);
		
		return $defaults;
	}
	
    public function calculate_shipping() {
		$this->shipping_total 	= 0;
		$this->shipping_tax 	= 0;
		$this->shipping_label 	= $this->title;
    }

    public function admin_scripts() {
    	?>
		<script type="text/javascript">
			/*<![CDATA[*/
				jQuery(function($) {
					jQuery('select#jigoshop_free_shipping_availability').change(function() {
						if (jQuery(this).val()=="specific") {
							jQuery(this).parent().parent().next('tr').show();
						} else {
							jQuery(this).parent().parent().next('tr').hide();
						}
					}).change();
				});
			/*]]>*/
		</script>
    	<?php
    }

    public function process_admin_options() {

 		$jsOptions = Jigoshop_Options::instance();
 		
  		if ( isset( $_POST['jigoshop_free_shipping_enabled'] ))
  			$jsOptions->set_option('jigoshop_free_shipping_enabled', jigowatt_clean($_POST['jigoshop_free_shipping_enabled']));
  		else $jsOptions->delete_option('jigoshop_free_shipping_enabled');
  		
   		if ( isset( $_POST['jigoshop_free_shipping_title'] ))
   			$jsOptions->set_option('jigoshop_free_shipping_title', jigowatt_clean($_POST['jigoshop_free_shipping_title']));
   		else $jsOptions->delete_option('jigoshop_free_shipping_title');
   		
   		if ( isset( $_POST['jigoshop_free_shipping_minimum_amount'] )) $jsOptions->set_option('jigoshop_free_shipping_minimum_amount', jigowatt_clean($_POST['jigoshop_free_shipping_minimum_amount']));
   		else $jsOptions->delete_option('jigoshop_free_shipping_minimum_amount');
   		
   		if ( isset( $_POST['jigoshop_free_shipping_availability'] )) $jsOptions->set_option('jigoshop_free_shipping_availability', jigowatt_clean($_POST['jigoshop_free_shipping_availability'] ));
   		else $jsOptions->delete_option('jigoshop_free_shipping_availability');

	    if ( isset( $_POST['jigoshop_free_shipping_countries'] ))
	    	$selected_countries = $_POST['jigoshop_free_shipping_countries'];
	    else $selected_countries = array();
	    
	    $jsOptions->set_option( 'jigoshop_free_shipping_countries', $selected_countries );

    }

}
