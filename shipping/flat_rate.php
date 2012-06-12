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
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigowatt
 * @copyright           Copyright © 2011-2012 Jigowatt Ltd.
 * @license             http://jigoshop.com/license/commercial-edition
 */
class flat_rate extends jigoshop_shipping_method {

	public function __construct() {
        $this->id 			= 'flat_rate';
        $this->enabled		= get_option('jigoshop_flat_rate_enabled');
		$this->title 		= get_option('jigoshop_flat_rate_title');
		$this->availability = get_option('jigoshop_flat_rate_availability');
		$this->countries 	= get_option('jigoshop_flat_rate_countries');
		$this->type 		= get_option('jigoshop_flat_rate_type');
		$this->tax_status	= get_option('jigoshop_flat_rate_tax_status');
		$this->cost 		= get_option('jigoshop_flat_rate_cost');
		$this->fee 			= get_option('jigoshop_flat_rate_handling_fee');

		add_action('jigoshop_update_options', array(&$this, 'process_admin_options'));
		add_option('jigoshop_flat_rate_availability', 'all');
		add_option('jigoshop_flat_rate_title', 'Flat Rate');
		add_option('jigoshop_flat_rate_tax_status', 'taxable');
    }

    public function calculate_shipping() {

    	$_tax = $this->get_tax();

    	$this->shipping_total 	= 0;
		$this->shipping_tax 	= 0;

    	if ($this->type=='order') :
			// Shipping for whole order
			$this->shipping_total = $this->cost + $this->get_fee( $this->fee, jigoshop_cart::$cart_contents_total );
            $this->shipping_total = ($this->shipping_total < 0 ? 0 : $this->shipping_total);

			if ( get_option('jigoshop_calc_taxes')=='yes' && $this->tax_status=='taxable' ) :

                $_tax->calculate_shipping_tax( $this->shipping_total - jigoshop_cart::get_cart_discount_leftover(), $this->id );
                $this->shipping_tax = $_tax->get_total_shipping_tax_amount();

			endif;
		else :

			// Shipping per item
            if (sizeof(jigoshop_cart::$cart_contents)>0) :
                foreach (jigoshop_cart::$cart_contents as $item_id => $values) :
                    $_product = $values['data'];
                    if ($_product->exists() && $values['quantity']>0 && $_product->product_type <> 'downloadable') :

                        $item_shipping_price = ($this->cost + $this->get_fee( $this->fee, $_product->get_price() )) * $values['quantity'];
                        $this->shipping_total = $this->shipping_total + $item_shipping_price;

                        //TODO: need to figure out how to handle per item shipping with discounts that apply to shipping as well
                        if ( $_product->is_shipping_taxable() && $this->tax_status=='taxable' ) :
                            $_tax->calculate_shipping_tax( $item_shipping_price, $this->id, $_product->get_tax_classes() );
                        endif;

                    endif;
                endforeach;
                $this->shipping_tax = $_tax->get_total_shipping_tax_amount();
            endif;
		endif;
    }

    public function admin_options() {

		$options = array (

			array( 'name'        => __('Flat Rate', 'jigoshop'), 'type' => 'title', 'desc' => __('Flat rates let you define a standard rate per item, or per order.', 'jigoshop') ),

			array(
				'name'           => __('Enable Flat Rate','jigoshop'),
				'id'             => 'jigoshop_flat_rate_enabled',
				'type'           => 'checkbox',
				'std'            => 'no'
			),

			array(
				'name'           => __('Method Title','jigoshop'),
				'tip'            => __('This controls the title which the user sees during checkout.','jigoshop'),
				'id'             => 'jigoshop_flat_rate_title',
				'type'           => 'text',
				'std'            => 'Flat Rate'
			),

			array(
				'name'           => __('Type','jigoshop'),
				'desc'           => '',
				'id'             => 'jigoshop_flat_rate_type',
				'type'           => 'radio',
				'options'        => array(
					'order'      => __('Per Order', 'jigoshop'),
					'item'       => __('Per Item', 'jigoshop')
				)
			),

			array(
				'name'           => __('Tax Status','jigoshop'),
				'desc'           => '',
				'id'             => 'jigoshop_flat_rate_tax_status',
				'type'           => 'radio',
				'options'        => array(
					'taxable'    => __('Taxable', 'jigoshop'),
					'none'       => __('None', 'jigoshop')
				)
			),

			array(
				'name'           => __('Cost','jigoshop'),
				'tip'            => __('Cost excluding tax. Enter an amount, e.g. 2.50.','jigoshop'),
				'id'             => 'jigoshop_flat_rate_cost',
				'css'            => 'width:60px;',
				'type'           => 'number',
				'restrict'       => array( 'min' => 0 ),
			),

			array(
				'name'           => __('Handling Fee','jigoshop'),
				'tip'            => __('Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.','jigoshop'),
				'id'             => 'jigoshop_flat_rate_handling_fee',
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

   		if(isset($_POST['jigoshop_flat_rate_tax_status'])) update_option('jigoshop_flat_rate_tax_status', jigowatt_clean($_POST['jigoshop_flat_rate_tax_status'])); else @delete_option('jigoshop_flat_rate_tax_status');

   		if(isset($_POST['jigoshop_flat_rate_enabled'])) update_option('jigoshop_flat_rate_enabled', jigowatt_clean($_POST['jigoshop_flat_rate_enabled'])); else update_option('jigoshop_flat_rate_enabled', 'no');
   		if(isset($_POST['jigoshop_flat_rate_title'])) update_option('jigoshop_flat_rate_title', jigowatt_clean($_POST['jigoshop_flat_rate_title'])); else @delete_option('jigoshop_flat_rate_title');
   		if(isset($_POST['jigoshop_flat_rate_type'])) update_option('jigoshop_flat_rate_type', jigowatt_clean($_POST['jigoshop_flat_rate_type'])); else @delete_option('jigoshop_flat_rate_type');
   		if(isset($_POST['jigoshop_flat_rate_cost'])) update_option('jigoshop_flat_rate_cost', jigowatt_clean($_POST['jigoshop_flat_rate_cost'])); else @delete_option('jigoshop_flat_rate_cost');
   		if(isset($_POST['jigoshop_flat_rate_handling_fee'])) update_option('jigoshop_flat_rate_handling_fee', jigowatt_clean($_POST['jigoshop_flat_rate_handling_fee'])); else @delete_option('jigoshop_flat_rate_handling_fee');

   		if(isset($_POST['jigoshop_flat_rate_availability'])) update_option('jigoshop_flat_rate_availability', jigowatt_clean($_POST['jigoshop_flat_rate_availability'])); else @delete_option('jigoshop_flat_rate_availability');
	    if (isset($_POST['jigoshop_flat_rate_countries'])) $selected_countries = $_POST['jigoshop_flat_rate_countries']; else $selected_countries = array();
	    update_option('jigoshop_flat_rate_countries', $selected_countries);

    }

}

function add_flat_rate_method( $methods ) {
	$methods[] = 'flat_rate'; return $methods;
}

add_filter('jigoshop_shipping_methods', 'add_flat_rate_method' );
