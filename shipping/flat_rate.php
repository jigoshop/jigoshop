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
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */

function add_flat_rate_method( $methods ) {
	$methods[] = 'flat_rate';
	return $methods;
}
add_filter( 'jigoshop_shipping_methods', 'add_flat_rate_method', 10 );


class flat_rate extends jigoshop_shipping_method {

	public function __construct() {
		
		parent::__construct();
		
        $this->id 			= 'flat_rate';
        $this->enabled		= Jigoshop_Base::get_options()->get_option('jigoshop_flat_rate_enabled');
		$this->title 		= Jigoshop_Base::get_options()->get_option('jigoshop_flat_rate_title');
		$this->availability = Jigoshop_Base::get_options()->get_option('jigoshop_flat_rate_availability');
		$this->countries 	= Jigoshop_Base::get_options()->get_option('jigoshop_flat_rate_countries');
		$this->type 		= Jigoshop_Base::get_options()->get_option('jigoshop_flat_rate_type');
		$this->tax_status	= Jigoshop_Base::get_options()->get_option('jigoshop_flat_rate_tax_status');
		$this->cost 		= Jigoshop_Base::get_options()->get_option('jigoshop_flat_rate_cost');
		$this->fee 			= Jigoshop_Base::get_options()->get_option('jigoshop_flat_rate_handling_fee');

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
		$defaults[] = array( 'name' => __('Flat Rates', 'jigoshop'), 'type' => 'title', 'desc' => __('Flat rates let you define a standard rate per item, or per order.', 'jigoshop') );
		
		// List each option in order of appearance with details
		$defaults[] = array(
			'name'		=> __('Enable Flat Rate','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_flat_rate_enabled',
			'std' 		=> 'yes',
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
			'id' 		=> 'jigoshop_flat_rate_title',
			'std' 		=> __('Flat Rate','jigoshop'),
			'type' 		=> 'text'
		);
		
		$defaults[] = array(
			'name'		=> __('Type','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_flat_rate_type',
			'std' 		=> 'order',
			'type' 		=> 'radio',
			'choices'	=> array(
				'order'			=> __('Per Order', 'jigoshop'),
				'item'			=> __('Per Item', 'jigoshop')
			)
		);
		
		$defaults[] = array(
			'name'		=> __('Tax Status','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_flat_rate_tax_status',
			'std' 		=> 'taxable',
			'type' 		=> 'radio',
			'choices'	=> array(
				'taxable'		=> __('Taxable', 'jigoshop'),
				'none'			=> __('None', 'jigoshop')
			)
		);
		
		$defaults[] = array(
			'name'		=> __('Cost','jigoshop'),
			'desc' 		=> '',
			'type' 		=> 'decimal',
			'tip' 		=> __('Cost excluding tax. Enter an amount, e.g. 2.50.','jigoshop'),
			'id' 		=> 'jigoshop_flat_rate_cost',
			'std' 		=> '0',
		);
		
		$defaults[] = array(
			'name'		=> __('Handling Fee','jigoshop'),
			'desc' 		=> '',
			'type' 		=> 'text',
			'tip' 		=> __('Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.','jigoshop'),
			'id' 		=> 'jigoshop_flat_rate_handling_fee',
			'std' 		=> ''
		);
		
		$defaults[] = array(
			'name'		=> __('Method available for','jigoshop'),
			'desc' 		=> '',
			'tip' 		=> '',
			'id' 		=> 'jigoshop_flat_rate_availability',
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
			'id' 		=> 'jigoshop_flat_rate_countries',
			'std' 		=> '',
			'type' 		=> 'multi_select_countries'
		);
		
		return $defaults;
	}
	
    public function calculate_shipping() {

		
    	$_tax = $this->get_tax();

    	$this->shipping_total 	= 0;
		$this->shipping_tax 	= 0;

    	if ($this->type=='order') :
			// Shipping for whole order
			$this->shipping_total = $this->cost + $this->get_fee( $this->fee, jigoshop_cart::$cart_contents_total );
            $this->shipping_total = ($this->shipping_total < 0 ? 0 : $this->shipping_total);
        
			if ( Jigoshop_Base::get_options()->get_option('jigoshop_calc_taxes')=='yes' && $this->tax_status=='taxable' ) :

				// fix flat rate taxes for now. This is old and deprecated, but need to think about how to utilize the total_shipping_tax_amount yet
				$this->shipping_tax = $this->calculate_shipping_tax($this->shipping_total - jigoshop_cart::get_cart_discount_leftover());
                //$_tax->calculate_shipping_tax( $this->shipping_total - jigoshop_cart::get_cart_discount_leftover(), $this->id );
                //$this->shipping_tax = //$_tax->get_total_shipping_tax_amount();
				//$this->shipping_tax = $this->get_selected_tax(0);
				
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
						// * currently not working. Will need to fix
                        if ( $_product->is_shipping_taxable() && $this->tax_status=='taxable' ) :
                            $_tax->calculate_shipping_tax( $item_shipping_price, $this->id, $_product->get_tax_classes() );
                        endif;

                    endif;
                endforeach;
                $this->shipping_tax = $_tax->get_total_shipping_tax_amount();
            endif;
		endif;
    }

    public function admin_scripts() {
    	?>
		<script type="text/javascript">
			/*<![CDATA[*/
				jQuery(function($) {
					jQuery('select#jigoshop_flat_rate_availability').change(function() {
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
