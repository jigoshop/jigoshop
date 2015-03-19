<?php
/**
 * Local pickup
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

add_filter('jigoshop_shipping_methods', function($methods){
	$methods[] = 'local_pickup';

	return $methods;
}, 30);

class local_pickup extends jigoshop_shipping_method
{
	private $shipping_label;

	public function __construct()
	{
		parent::__construct();

		$options = Jigoshop_Base::get_options();
		$this->id = 'local_pickup';
		$this->enabled = $options->get('jigoshop_local_pickup_enabled');
		$this->title = $options->get('jigoshop_local_pickup_title');
		$this->fee = $options->get('jigoshop_local_pickup_handling_fee');
		$this->availability = $options->get('jigoshop_local_pickup_availability');
		$this->countries = $options->get('jigoshop_local_pickup_countries');

		$session = jigoshop_session::instance();
		if (isset($session->chosen_shipping_method_id) && $session->chosen_shipping_method_id == $this->id) {
			$this->chosen = true;
		}

		add_action('jigoshop_settings_scripts', array($this, 'admin_scripts'));
	}

	public function calculate_shipping()
	{
		$this->shipping_total = $this->get_fee($this->fee, jigoshop_cart::get_total(false));
		$this->shipping_tax = 0;
		$this->shipping_label = $this->title;
	}

	public function admin_scripts()
	{
		?>
		<script type="text/javascript">
			/*<![CDATA[*/
			jQuery(function($){
				$('select#jigoshop_local_pickup_availability').change(function(){
					if($(this).val() == "specific"){
						$(this).parent().parent().next('tr').show();
					} else {
						$(this).parent().parent().next('tr').hide();
					}
				}).change();
			});
			/*]]>*/
		</script>
	<?php
	}

	/**
	 * Default Option settings for WordPress Settings API using the Jigoshop_Options class
	 * These should be installed on the Jigoshop_Options 'Shipping' tab
	 */
	protected function get_default_options()
	{
		return array(
			array('name' => __('Local pickup', 'jigoshop'), 'type' => 'title', 'desc' => ''),
			array(
				'name' => __('Enable local pickup', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_local_pickup_enabled',
				'std' => 'no',
				'type' => 'checkbox',
				'choices' => array(
					'no' => __('No', 'jigoshop'),
					'yes' => __('Yes', 'jigoshop')
				)
			),
			array(
				'name' => __('Method Title', 'jigoshop'),
				'desc' => '',
				'tip' => __('This controls the title which the user sees during checkout.', 'jigoshop'),
				'id' => 'jigoshop_local_pickup_title',
				'std' => __('Local pickup', 'jigoshop'),
				'type' => 'text'
			),
			array(
				'name' => __('Handling Fee', 'jigoshop'),
				'desc' => '',
				'type' => 'text',
				'tip' => __('Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'jigoshop'),
				'id' => 'jigoshop_local_pickup_handling_fee',
				'std' => ''
			),
			array(
				'name' => __('Method available for', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_local_pickup_availability',
				'std' => 'all',
				'type' => 'select',
				'choices' => array(
					'all' => __('All allowed countries', 'jigoshop'),
					'specific' => __('Specific Countries', 'jigoshop')
				)
			),
			array(
				'name' => __('Specific Countries', 'jigoshop'),
				'desc' => '',
				'tip' => '',
				'id' => 'jigoshop_local_pickup_countries',
				'std' => '',
				'type' => 'multi_select_countries'
			),
		);
	}
}
