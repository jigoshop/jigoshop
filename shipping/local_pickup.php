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
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

function add_local_pickup_method( $methods ) {
	$methods[] = 'local_pickup';
	return $methods;
}
add_filter( 'jigoshop_shipping_methods', 'add_local_pickup_method', 30 );


class local_pickup extends jigoshop_shipping_method {

	public function __construct() {
		
		parent::__construct();
		
        $this->id 			= 'local_pickup';
        $this->enabled		= Jigoshop_Base::get_options()->get_option('jigoshop_local_pickup_enabled');
		$this->title 		= Jigoshop_Base::get_options()->get_option('jigoshop_local_pickup_title');
		$this->availability = Jigoshop_Base::get_options()->get_option('jigoshop_local_pickup_availability');
		$this->countries 	= Jigoshop_Base::get_options()->get_option('jigoshop_local_pickup_countries');
		
		if ( isset( jigoshop_session::instance()->chosen_shipping_method_id )
			&& jigoshop_session::instance()->chosen_shipping_method_id == $this->id ) {
			
			$this->chosen = true;
		
		}
		
		add_action( 'jigoshop_settings_scripts', array( &$this, 'admin_scripts' ) );

    }

	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 *
	 * These should be installed on the Jigoshop_Options 'Shipping' tab
	 *
	 */	
	protected function get_default_options() {
	
		$defaults = array();
		
		// Define the Section name for the Jigoshop_Options
		$defaults[] = array( 'name' => __('Local pickup', 'jigoshop'), 'type' => 'title', 'desc' => '' );
		
		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable local pickup','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_local_pickup_enabled',
			'std' 		=> 'no',
			'type' 		=> 'checkbox',
			'choices'	=> array(
				'no'			=> __('No', 'jigoshop'),
				'yes'			=> __('Yes', 'jigoshop')
			)
		);
		
		$defaults[] = array(
			'name'		=> __('Method Title','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> __('This controls the title which the user sees during checkout.','jigoshop'),
			'id' 		=> 'jigoshop_local_pickup_title',
			'std' 		=> __('Local pickup','jigoshop'),
			'type' 		=> 'text'
		);
		
		$defaults[] = array(
			'name'		=> __('Method available for','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_local_pickup_availability',
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
			'id' 		=> 'jigoshop_local_pickup_countries',
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
					jQuery('select#jigoshop_local_pickup_availability').change(function() {
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

}
