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
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class local_pickup extends jigoshop_shipping_method {

	public function __construct() {
		$this->id           = 'local_pickup';
		$this->enabled      = get_option('jigoshop_local_pickup_enabled');
		$this->title        = get_option('jigoshop_local_pickup_title');
		$this->availability = get_option('jigoshop_local_pickup_availability');
		$this->countries    = get_option('jigoshop_local_pickup_countries');
		if (isset( jigoshop_session::instance()->chosen_shipping_method_id ) && jigoshop_session::instance()->chosen_shipping_method_id==$this->id) $this->chosen = true;

		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));

    }

    public function calculate_shipping() {
		$this->shipping_total = 0;
		$this->shipping_tax   = 0;
		$this->shipping_label = $this->title;
    }

    public function admin_options() {

		$options = array (

			array( 'name'        => __('Local pickup', 'jigoshop'), 'type' => 'title', 'desc' => '' ),

			array(
				'name'           => __('Enable Local pickup','jigoshop'),
				'id'             => 'jigoshop_local_pickup_enabled',
				'type'           => 'checkbox',
				'std'            => 'no'
			),

			array(
				'name'           => __('Method Title','jigoshop'),
				'tip'            => __('This controls the title which the user sees during checkout.','jigoshop'),
				'id'             => 'jigoshop_local_pickup_title',
				'type'           => 'text',
				'std'            => 'Local pickup'
			),

			array(
				'name'           => __('Allowed Countries','jigoshop'),
				'desc'           => '',
				'tip'            => __('These are countries that you are willing to ship to.','jigoshop'),
				'id'             => 'jigoshop_local_pickup_availability',
				'std'            => 'all',
				'type'           => 'select',
				'options'        => array(
					'all'        => __('All Countries', 'jigoshop'),
					'specific'   => __('Specific Countries', 'jigoshop')
				)
			),

			array(
				'name'           => __('Specific Countries','jigoshop'),
				'id'             => 'jigoshop_local_pickup_countries',
				'type'           => 'multi_select_countries'
			),
		);
		jigoshop_admin_option_display($options);

    	?>
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

   		if(isset($_POST['jigoshop_local_pickup_enabled'])) update_option('jigoshop_local_pickup_enabled', jigowatt_clean($_POST['jigoshop_local_pickup_enabled'])); else update_option('jigoshop_local_pickup_enabled', 'no');
   		if(isset($_POST['jigoshop_local_pickup_title'])) update_option('jigoshop_local_pickup_title', jigowatt_clean($_POST['jigoshop_local_pickup_title'])); else @delete_option('jigoshop_local_pickup_title');
   		if(isset($_POST['jigoshop_local_pickup_availability'])) update_option('jigoshop_local_pickup_availability', jigowatt_clean($_POST['jigoshop_local_pickup_availability'])); else @delete_option('jigoshop_local_pickup_availability');

	    if (isset($_POST['jigoshop_local_pickup_countries'])) $selected_countries = $_POST['jigoshop_local_pickup_countries']; else $selected_countries = array();
	    update_option('jigoshop_local_pickup_countries', $selected_countries);

    }

}

function add_local_pickup_method( $methods ) {
	$methods[] = 'local_pickup'; return $methods;
}

add_filter('jigoshop_shipping_methods', 'add_local_pickup_method' );