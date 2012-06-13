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
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class free_shipping extends jigoshop_shipping_method {

	public function __construct() {
        $this->id 			= 'free_shipping';
        $this->enabled		= get_option('jigoshop_free_shipping_enabled');
		$this->title 		= get_option('jigoshop_free_shipping_title');
		$this->min_amount 	= get_option('jigoshop_free_shipping_minimum_amount');
		$this->availability = get_option('jigoshop_free_shipping_availability');
		$this->countries 	= get_option('jigoshop_free_shipping_countries');
		if (isset( jigoshop_session::instance()->chosen_shipping_method_id ) && jigoshop_session::instance()->chosen_shipping_method_id==$this->id) $this->chosen = true;

		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));

    }

    public function calculate_shipping() {
		$this->shipping_total 	= 0;
		$this->shipping_tax 	= 0;
		$this->shipping_label 	= $this->title;
    }

    public function admin_options() {

		$options = array (

			array( 'name'        => __('Free Shipping', 'jigoshop'), 'type' => 'title', 'desc' => '' ),

			array(
				'name'           => __('Enable Free Shipping','jigoshop'),
				'id'             => 'jigoshop_free_shipping_enabled',
				'type'           => 'checkbox',
				'std'            => 'no'
			),

			array(
				'name'           => __('Method Title','jigoshop'),
				'tip'            => __('This controls the title which the user sees during checkout.','jigoshop'),
				'id'             => 'jigoshop_free_shipping_title',
				'type'           => 'text',
				'std'            => 'Free Shipping'
			),

			array(
				'name'           => __('Minimum Order Amount','jigoshop'),
				'tip'            => __('Users will need to spend this amount to get free shipping. Leave blank to disable.','jigoshop'),
				'id'             => 'jigoshop_free_shipping_minimum_amount',
				'css'            => 'width:60px;',
				'type'           => 'number',
				'restrict'       => array( 'min' => 0 ),
			),

			array(
				'name'           => __('Allowed Countries','jigoshop'),
				'desc'           => '',
				'tip'            => __('These are countries that you are willing to ship to.','jigoshop'),
				'id'             => 'jigoshop_flat_rate_availability',
				'std'            => 'all',
				'type'           => 'select',
				'options'        => array(
					'all'        => __('All Countries', 'jigoshop'),
					'specific'   => __('Specific Countries', 'jigoshop')
				)
			),

			array(
				'name'           => __('Specific Countries','jigoshop'),
				'id'             => 'jigoshop_flat_rate_countries',
				'type'           => 'multi_select_countries'
			),
		);
		jigoshop_admin_option_display($options);

    	?>
       	<script type="text/javascript">
		jQuery(function() {
			jQuery('select#jigoshop_free_shipping_availability').change(function(){
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

   		if(isset($_POST['jigoshop_free_shipping_enabled'])) update_option('jigoshop_free_shipping_enabled', 'yes'); else update_option('jigoshop_free_shipping_enabled', 'no');
   		if(isset($_POST['jigoshop_free_shipping_title'])) update_option('jigoshop_free_shipping_title', jigowatt_clean($_POST['jigoshop_free_shipping_title'])); else @delete_option('jigoshop_free_shipping_title');
   		if(isset($_POST['jigoshop_free_shipping_minimum_amount'])) update_option('jigoshop_free_shipping_minimum_amount', jigowatt_clean($_POST['jigoshop_free_shipping_minimum_amount'])); else @delete_option('jigoshop_free_shipping_minimum_amount');
   		if(isset($_POST['jigoshop_free_shipping_availability'])) update_option('jigoshop_free_shipping_availability', jigowatt_clean($_POST['jigoshop_free_shipping_availability'])); else @delete_option('jigoshop_free_shipping_availability');

	    if (isset($_POST['jigoshop_free_shipping_countries'])) $selected_countries = $_POST['jigoshop_free_shipping_countries']; else $selected_countries = array();
	    update_option('jigoshop_free_shipping_countries', $selected_countries);

    }

}

function add_free_shipping_method( $methods ) {
	$methods[] = 'free_shipping'; return $methods;
}

add_filter('jigoshop_shipping_methods', 'add_free_shipping_method' );