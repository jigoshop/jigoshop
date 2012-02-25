<?php
/**
 * Jigoshop_Admin_Settings class for management and display all Jigoshop option settings
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package		Jigoshop
 * @category	Admin
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */

class Jigoshop_Admin_Settings extends Jigoshop_Singleton {

	private $our_parser;
	private static $page_name;
	
	
	/**
	 * Constructor
	 *
	 * @since 1.2
	 */
	protected function __construct() {
		
		self::$page_name = 'jigoshop_options';	// should match our WordPress Jigoshop_Options table entry name
		
		$this->our_parser = new Jigoshop_Options_Parser( 
			Jigoshop_Options::get_default_options(), 
			$this->get_options_name()
		);

		add_action( 'admin_menu', array( &$this, 'add_settings_page' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );

	}
	
	
	/**
	 * Get the name of our Settings page and WordPress options table entry
	 *
	 * @since 1.2
	 */
	public function get_options_name() {
		return self::$page_name;
	}
	
	
	/**
	 * Add options page
	 *
	 * @since 1.2
	 */
	public function add_settings_page() {

		$admin_page = add_submenu_page( 'jigoshop', __( 'Jigoshop Settings' ), __( 'Jigoshop Settings' ), 'manage_options', $this->get_options_name(), array( &$this, 'output_markup' ) );

		add_action( 'admin_print_scripts-' . $admin_page, array( &$this, 'settings_scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page, array( &$this, 'settings_styles' ) );

	}
	
	
	/**
	* Register settings
	*
	* @since 1.2
	*/
	public function register_settings() {

		register_setting( $this->get_options_name(), $this->get_options_name(), array ( &$this, 'validate_settings' ) );
		
		$slug = $this->get_current_tab_slug();
		$options = $this->our_parser->sections[$slug];
		
		add_settings_section( $slug, '', array( &$this, 'display_section' ), $this->get_options_name() );
			
		foreach ( $options as $option ) {
			$this->create_setting( $option );
		}

	}
	
	
	/**
	 * Create settings field
	 *
	 * @since 1.2
	 */
	public function create_setting( $args = array() ) {
	
		$defaults = array(
			'section'		=> '',
			'id'			=> null,
			'type'			=> '',
			'name'			=> __( '' ),
			'desc'			=> __( '' ),
			'tip'			=> '',
			'std'			=> '',
			'choices'		=> array(),
			'class'			=> '',
			'css'			=> '',
			'args_input'	=> ''
		);

		extract( wp_parse_args( $args, $defaults ) );

		$field_args = array(
			'type'			=> $type,
			'id'			=> $id,
			'name'			=> $name,
			'desc'			=> $desc,
			'tip'			=> $tip,
			"std"			=> $std,
			'choices'		=> $choices,
			'label_for'		=> $id,
			'class'			=> $class,
			'css'			=> $css,
			'args_input'	=> $args_input
		);
		
		if ( $type <> 'heading' && $type <> 'title' ) {
			add_settings_field( 
				$id, 
				esc_attr( $name ), 
				array( &$this, 'display_option' ), 
				$this->get_options_name(), 
				$section, 
				$field_args
			);
		}
	}
	
	
	/**
	* jQuery Tabs
	*
	* @since 1.2
	*/
	public function settings_scripts() {
		
    	wp_register_script( 'jigoshop-easytooltip', jigoshop::assets_url() . '/assets/js/easyTooltip.js', '' );
    	wp_enqueue_script( 'jigoshop-easytooltip' );
		wp_enqueue_script( 'jquery-ui-tabs' );

	}
	
	
	/**
	* Styling for the options page
	*
	* @since 1.2
	*/
	public function settings_styles() {

//		wp_register_style('jigoshop_settings_api_styles', jigoshop::assets_url() . '/assets/css/settings.css');
//		wp_enqueue_style( 'jigoshop_settings_api_styles' );

	}
	
	
	/**
	 * Render the Options page
	 *
	 * @since 1.2
	 */
	public function output_markup() {
		?>
			<div class="wrap">
				<div class="icon32" id="icon-options-general"></div>
				<h2><?php _e( 'Jigoshop Settings' ) ?></h2>
				
				<?php
					if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true )
						echo '<div class="updated fade"><p>' . __( 'Jigoshop settings updated.' ) . '</p></div>';
				?>
				<div class="ui-tabs">
					<ul class="ui-tabs-nav">
						<?php echo $this->build_tab_menu_items(); ?>
					</ul>
					<form action="options.php" method="post" style="clear:both;">
						<?php settings_fields( $this->get_options_name() ); ?>
						<?php do_settings_sections( $this->get_options_name() ); ?>
						<p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" /></p>
					</form>
				</div>
			</div>
		<?php
	}
	
	
	/**
	 * Create the Navigation Menu Tabs
	 *
	 * @since 1.2
	 */
	function build_tab_menu_items() {
		$menus_li = '';
		$slug = $this->get_current_tab_slug();
		foreach ( $this->our_parser->tab_headers as $section ) {
			$this_slug = sanitize_title( $section );
			if ( $slug == $this_slug ) {
				$menus_li .= '<li><a
					class="active"
					title="'.$section.'"
					href="?page='.Jigoshop_Admin_Settings::get_options_name().'&tab='.$this_slug.'">' . $section . '</a></li>';
			} else {
				$menus_li .= '<li><a
					title="'.$section.'"
					href="?page='.Jigoshop_Admin_Settings::get_options_name().'&tab='.$this_slug.'">' . $section . '</a></li>';
			}
		}
		return $menus_li;
	}
	
	
	/**
	 * Format markup for an option and output it
	 *
	 * @since 1.2
	 */
	public function display_option( $option ) {
		echo $this->our_parser->format_option_for_display( $option );
	}
	
	
	/**
	 * Description for section
	 *
	 * @since 1.2
	 */
	public function display_section() {
		// code
	}
	
	
	/**
	 * Return the current Tab slug in view
	 *
	 * @since 1.2
	 */
	public function get_current_tab_slug() {
		$current = "";

		if ( isset( $_GET['tab'] ) ):
			$current = $_GET['tab'];
		else:
			$current = sanitize_title( $this->our_parser->these_options[0]['name'] );
		endif;
		return $current;
	}
	
	
	/**
	 * Return the current Tab full name in view
	 *
	 * @since 1.2
	 */
	public function get_current_tab_name() {
		$current = "";

		if ( isset( $_GET['tab'] ) ) {
			foreach ( $this->our_parser->these_options as $option ) {
				if ( $option['type'] == 'heading' ) {
					if ( $option['section'] == $_GET['tab'] ) {
						$current = $option['name'];
						break;
					}
				}
			}
		} else {
			$current = $this->our_parser->these_options[0]['name'];
		}
		return $current;
	}
	
	
	/**
	* Validate settings
	*
	* @since 1.2
	*/
	public function validate_settings( $input ) {
		$current_options = Jigoshop_options::get_current_options();
//		$current_options['validation-error'] = true; // if no errors in validation, we will reset this to false
//		$current_options['message'] = "There was an error validating the data. No update occured!";
		$valid_input = $current_options;	// we start with the current options, plus the error flag and message
		$operation = "";
		
		if ( ! empty( $input )) foreach ( $input as $id => $value ) {
			$valid_input[$id] = $value;	// obviously we aren't validating very much yet (JAP)
		}
		return $valid_input;
	}
	
}


class Jigoshop_Options_Parser {

	var $these_options;		// The array of default options items to parse
	var $tab_headers;
	var $sections;


	function __construct( $option_items, $this_options_entry ) {
		$this->these_options = $option_items;
		$this->parse_options();
	}


	private function parse_options() {
		
		$tab_headers = array();
		$sections = array();
		
		foreach ( $this->these_options as $item ) {
			
			$defaults = array(
				'section'		=> '',
				'id'			=> null,
				'type'			=> '',
				'name'			=> __( '' ),
				'desc'			=> __( '' ),
				'tip'			=> '',
				'std'			=> '',
				'choices'		=> array(),
				'class'			=> '',
				'css'			=> '',
				'args_input'	=> ''
			);
	
			$item = wp_parse_args( $item, $defaults );
			
			if ( isset( $item['id'] ) ) $item['id'] = sanitize_title( $item['id'] );
			
			if ( $item['type'] == "heading" ) {
				$tab_headers[] = $item['name'];
				$section_name = sanitize_title( $item['name'] );
			}
						
			$item['section'] = $section_name;
			$sections[$section_name][] = $item; // store each option item in it's section heading
			
		}

		$this->tab_headers = $tab_headers;
		$this->sections = $sections;
	}
	
	
	public function format_option_for_display( $item ) {
	
		$data = Jigoshop_Options::get_current_options();
		$display = "";
		$class = "";
		
		if ( isset( $item['class'] ) ) {
			$class = $item['class'];
		}
		
        $display .= '<td class="titledesc">';
        if ( ! empty( $item['tip'] )) {
			$display .= '<a href="#" tip="'.$item['tip'].'" class="tips" tabindex="99"></a>';
        }
		$display .= '</td>';
		
		$display .= '<td class="forminp "'.$class.'>';
		
		switch ( $item['type'] ) {
			case 'gateway_options' :
                foreach (jigoshop_payment_gateways::payment_gateways() as $gateway) :
                    $gateway->admin_options();
                endforeach;
				break;
				
			case 'shipping_options' :
                foreach (jigoshop_shipping::get_all_methods() as $method) :
                    $method->admin_options();
                endforeach;
				break;
				
			case 'tax_rates' :
				$this->format_tax_classes_for_display( $item );
				break;
				
			case 'coupons' :
				$this->format_coupons_for_display( $item );
				break;
				
			case 'image_size' :
				$width = $data[$item['id']];
				$display .= '<input
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					id="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					class="jigoshop-input"
					type="text"
					value="'.$data[$item['id']].'" />';
				break;
				
			case 'single_select_page' :
				$page_setting = (int) $data[$item['id']];
				$args = array(
					'name' => Jigoshop_Admin_Settings::get_options_name() . '[' . $item['id'] . ']',
					'id' => $item['id'],
					'sort_order' => 'ASC',
					'echo' => 0,
					'selected' => $page_setting
				);
				if ( isset( $args_input )) $args = wp_parse_args( $args_input, $args );
				$display .= wp_dropdown_pages( $args );
				break;

			case 'single_select_country' :	// must fix jigoshop_countries::country_dropdown_options(), echo's output (-JAP-)
				$countries = jigoshop_countries::$countries;
				$country_setting = (string) $data[$item['id']];
				if (strstr($country_setting, ':')) :
					$country = current(explode(':', $country_setting));
					$state = end(explode(':', $country_setting));
				else :
					$country = $country_setting;
					$state = '*';
				endif;
				$display .= '<select class="select' . $class . '" name="' . Jigoshop_Admin_Settings::get_options_name() . '[' . $item['id'] . ']">';
				$display .= jigoshop_countries::country_dropdown_options($country, $state, false, true, false);
				$display .= '</select>';
				break;
				
			case 'multi_select_countries' :
				$countries = jigoshop_countries::$countries;
				asort($countries);
				$selections = (array) $data[$item['id']];
				$display .= '<div class="multi_select_countries"><ul>';
				if ($countries)
					foreach ($countries as $key => $val) :
						$display .= '<li><label><input type="checkbox" name="' . Jigoshop_Admin_Settings::get_options_name() . '[' . $item['id'] . '][] value="' . esc_attr( $key ) . '" ';
						$display .= in_array($key, $selections) ? 'checked="checked" />' : ' />';
						$display .=  $val . '</label></li>';
					endforeach;
					$display .= '</ul></div>';
				break;
				
			case 'text':
				$display .= '<input
					id="'.$item['id'].'"
					class="jigoshop-input"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					type="'.$item['type'].'"
					value="'.$data[$item['id']].'" />';
				break;

			case 'textarea':
				$cols = '15';
				$ta_value = '';
				if ( isset( $item['choices'] ) ) {
					$ta_options = $item['choices'];
					if ( isset( $ta_options['cols'] ) ) {
						$cols = $ta_options['cols'];
					}
				}
				$ta_value = stripslashes( $data[$item['id']] );
				$display .= '<textarea
					id="'.$item['id'].'"
					class="jigoshop-input"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					cols="'.$cols.'"
					rows="8">'.$ta_value.'</textarea>';
				break;

			case "radio":
				foreach ( $item['choices'] as $option => $name ) {
					$display .= '<input
						class="jigoshop-input jigoshop-radio"
						name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
						type="radio"
						value="'.$option.'" '.checked( $data[$item['id']], $option, '0' ).' /><label>'.$name.'</label><div style="clear:right;">  </div>';
				}
				break;

			case 'checkbox':
				$display .= '<input
					id="'.$item['id'].'"
					type="checkbox"
					class="jigoshop-input jigoshop-checkbox"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					'.checked( $data[$item['id']], true, false ).' />';
				break;

			case 'multicheck':
				$multi_stored = $data[$item['id']];
				foreach ( $item['choices'] as $key => $option ) {
					$display .= '<input
						id="'.$item['id'].'_'.$key.'"
						class="jigoshop-input jigoshop-checkbox"
						name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']['.$key.']"
						type="checkbox"
						'.checked( $multi_stored[$key], true, false ).' />
						<label for="'.$item['id'].'_'.$key.'">'.$option.'</label>';
				}
				break;

			case 'range':
				$display .= '<input
					id="'.$item['id'].'"
					class="jigoshop-input"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']"
					type="'.$item['type'].'"
					min="'.$item['choices']['min'].'"
					max="'.$item['choices']['max'].'"
					step="'.$item['choices']['step'].'"
					value="'.$data[$item['id']].'" />';
				break;

			case 'select':
				$display .= '<select
					id="'.$item['id'].'"
					class="jigoshop-select"
					name="'.Jigoshop_Admin_Settings::get_options_name().'['.$item['id'].']" >';
				foreach ( $item['choices'] as $value => $label ) {
					$display .= '<option
						value="'.$value.'" '.selected( $data[$item['id']], $value, false ).' />'.$label.'
						</option>';
				}
				$display .= '</select>';
				break;
			
			default:
//				logme( "UNKOWN _type_ in parsing" );
//				logme( $item );
		}

		if ( $item['type'] != 'heading' ) {
			if ( empty( $item['desc'] ) ) {
				$explain_value = '';
			} else {
				$explain_value = $item['desc'];
			}
			$display .= '<div class="jigoshop-explain">' . $explain_value . '</div>';
			$display .= '</td>';
		}

		return $display;
	}
	
	
	// TODO: clean this mess up, move jQuery (-JAP-)
	function format_coupons_for_display( $value ) {
	
		$coupons = new jigoshop_coupons();
		$coupon_codes = $coupons->get_coupons();
		?><tr>
			<td class="titledesc"><?php echo $value['name'] ?>:</td>
			<td class="forminp" id="coupon_codes">
				<table class="coupon_rows" cellspacing="0">
					<thead>
						<tr>
							<th></th>
							<th><?php _e('Code', 'jigoshop'); ?></th>
							<th><?php _e('Type', 'jigoshop'); ?></th>
							<th><?php _e('Amount', 'jigoshop'); ?></th>
							<th><?php _e("ID's", 'jigoshop'); ?></th>
							<th><?php _e('From', 'jigoshop'); ?></th>
							<th><?php _e('To', 'jigoshop'); ?></th>
							<th><?php _e('Alone', 'jigoshop'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = -1;
						if ($coupon_codes && is_array($coupon_codes) && sizeof($coupon_codes) > 0)
							foreach ($coupon_codes as $coupon) : $i++;
								echo '<tr class="coupon_row">';
								echo '<td><a href="#" class="remove button" title="' . __('Delete this Coupon', 'jigoshop') . '">&times;</a></td>';
								echo '<td><input type="text" value="' . esc_attr( $coupon['code'] ) . '" name="coupon_code[' . esc_attr( $i ) . ']" title="' . __('Coupon Code', 'jigoshop') . '" placeholder="' . __('Coupon Code', 'jigoshop') . '" class="text" /></td><td><select name="coupon_type[' . esc_attr( $i ) . ']" title="Coupon Type">';

								$discount_types = array(
									'fixed_cart' => __('Cart Discount', 'jigoshop'),
									'percent' => __('Cart % Discount', 'jigoshop'),
									'fixed_product' => __('Product Discount', 'jigoshop'),
									'percent_product' => __('Product % Discount', 'jigoshop')
								);

								foreach ($discount_types as $type => $label) :
									$selected = ($coupon['type'] == $type) ? 'selected="selected"' : '';
									echo '<option value="' . esc_attr( $type ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
								endforeach;
								echo '</select></td>';
								echo '<td><input type="text" value="' . esc_attr( $coupon['amount'] ) . '" name="coupon_amount[' . esc_attr( $i ) . ']" title="' . __('Coupon Amount', 'jigoshop') . '" placeholder="' . __('Amount', 'jigoshop') . '" class="text" /></td>
								<td><input type="text" value="' . implode(', ', $coupon['products']) . '" name="product_ids[' . esc_attr( $i ) . ']" placeholder="' . __('1, 2, 3,', 'jigoshop') . '" class="text" /></td>';

								$date_from = $coupon['date_from'];
								echo '<td><label for="coupon_date_from[' . esc_attr( $i ) . ']"></label><input type="text" class="text date-pick" name="coupon_date_from[' . esc_attr( $i ) . ']" id="coupon_date_from[' . esc_attr( $i ) . ']" value="';
								if ($date_from)
									echo date('Y-m-d', $date_from);
								echo '" placeholder="' . __('yyyy-mm-dd', 'jigoshop') . '" /></td>';

								$date_to = $coupon['date_to'];
								echo '<td><label for="coupon_date_to[' . esc_attr( $i ) . ']"></label><input type="text" class="text date-pick" name="coupon_date_to[' . esc_attr( $i ) . ']" id="coupon_date_to[' . esc_attr( $i ) . ']" value="';
								if ($date_to)
									echo date('Y-m-d', $date_to);
								echo '" placeholder="' . __('yyyy-mm-dd', 'jigoshop') . '" /></td>';

								echo '<td><input type="checkbox" name="individual[' . esc_attr( $i ) . ']" ';
								if (isset($coupon['individual_use']) && $coupon['individual_use'] == 'yes')
									echo 'checked="checked"';
								echo ' /></td>';
								echo '</tr>';
								?>
								<script type="text/javascript">
									/* <![CDATA[ */
									jQuery(function() {
										jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );
	
									});
									/* ]]> */
								</script>
								<?php
							endforeach;
						?>
					</tbody>
				</table>
				<p><a href="#" class="add button"><?php _e('+ Add Coupon', 'jigoshop'); ?></a></p>
			</td>
		</tr>
		<script type="text/javascript">
			/* <![CDATA[ */
			jQuery(function() {
				jQuery('#coupon_codes a.add').live('click', function(){
					var size = jQuery('#coupon_codes table.coupon_rows tbody .coupon_row').size();
					// Make sure tbody exists
					var tbody_size = jQuery('#coupon_codes table.coupon_rows tbody').size();
					if (tbody_size==0) jQuery('#coupon_codes table.coupon_rows').append('<tbody></tbody>');

					// Add the row
					jQuery('<tr class="coupon_row">\
						<td><a href="#" class="remove button" title="<?php __('Delete this Coupon', 'jigoshop'); ?>">&times;</a></td>\
						<td><input type="text" value="" name="coupon_code[' + size + ']" title="<?php _e('Coupon Code', 'jigoshop'); ?>" placeholder="<?php _e('Coupon Code', 'jigoshop'); ?>" class="text" /></td>\
						<td><select name="coupon_type[' + size + ']" title="Coupon Type">\
							<option value="fixed_cart"><?php _e('Cart Discount', 'jigoshop'); ?></option>\
							<option value="percent"><?php _e('Cart % Discount', 'jigoshop'); ?></option>\
							<option value="fixed_product"><?php _e('Product Discount', 'jigoshop'); ?></option>\
							<option value="percent_product"><?php _e('Product % Discount', 'jigoshop'); ?></option>\
						</select></td>\
						<td><input type="text" value="" name="coupon_amount[' + size + ']" title="<?php _e('Coupon Amount', 'jigoshop'); ?>" placeholder="<?php _e('Amount', 'jigoshop'); ?>" class="text" /></td>\
						<td><input type="text" value="" name="product_ids[' + size + ']" \
							placeholder="<?php _e('1, 2, 3,', 'jigoshop'); ?>" class="text" /></td>\
						<td><label for="coupon_date_from[' + size + ']"></label>\
							<input type="text" class="text date-pick" name="coupon_date_from[' + size + ']" \
							id="coupon_date_from[' + size + ']" value="" \
							placeholder="<?php _e('yyyy-mm-dd', 'jigoshop'); ?>" /></td>\
						<td><label for="coupon_date_to[' + size + ']"></label>\
							<input type="text" class="text date-pick" name="coupon_date_to[' + size + ']" \
							id="coupon_date_to[' + size + ']" value="" \
							placeholder="<?php _e('yyyy-mm-dd', 'jigoshop'); ?>" /></td>\
						<td><input type="checkbox" name="individual[' + size + ']" /></td>').appendTo('#coupon_codes table.coupon_rows tbody');

					jQuery(function() {
						jQuery('.date-pick').datepicker( {dateFormat: 'yy-mm-dd', gotoCurrent: true} );

					});

					return false;
				});
				jQuery('#coupon_codes a.remove').live('click', function(){
					var answer = confirm("<?php _e('Delete this coupon?', 'jigoshop'); ?>")
					if (answer) {
						jQuery('input', jQuery(this).parent().parent()).val('');
						jQuery(this).parent().parent().hide();
					}
					return false;
				});
			});
			/* ]]> */
		</script>
		<?php
	}
	
	
	// TODO: clean this mess up, move jQuery (-JAP-)
	function format_tax_classes_for_display( $value ) {
	
		$_tax = new jigoshop_tax();
		$tax_classes = $_tax->get_tax_classes();
		$tax_rates = Jigoshop_Options::instance()->get_option( 'jigoshop_tax_rates' );
		$applied_all_states = array();
		?>
		<tr>
			<td class="titledesc"><?php if ($value['tip']) { ?><a href="#" tip="<?php echo $value['tip'] ?>" class="tips" tabindex="99"></a><?php } ?><?php echo $value['name'] ?>:</td>
			<td class="forminp" id="tax_rates">
				<div class="taxrows">
					<?php
					$i = -1;
					if ($tax_rates && is_array($tax_rates) && sizeof($tax_rates) > 0) {
						foreach ($tax_rates as $rate) :
							if ($rate['is_all_states']) :
								if (in_array($rate['country'], $applied_all_states)) :
									continue;
								endif;
							endif;
	
							$i++;// increment counter after check for all states having been applied
	
							echo '<p class="taxrow"><select name="tax_classes[' . esc_attr( $i ) . ']" title="Tax Classes"><option value="*">' . __('Standard Rate', 'jigoshop') . '</option>';
	
							if ($tax_classes)
								foreach ($tax_classes as $class) :
									echo '<option value="' . sanitize_title($class) . '"';
	
									if ($rate['class'] == sanitize_title($class))
										echo 'selected="selected"';
	
									echo '>' . $class . '</option>';
								endforeach;
	
							echo '</select><input type="text" class="text" value="' . esc_attr( $rate['label']  ) . '" name="tax_label[' . esc_attr( $i ) . ']" title="' . __('Online Label', 'jigoshop') . '" placeholder="' . __('Online Label', 'jigoshop') . '" maxlength="15" />';
	
							echo '</select><select name="tax_country[' . esc_attr( $i ) . ']" title="Country">';
	
							if ($rate['is_all_states']) :
								if (is_array($applied_all_states) && !in_array($rate['country'], $applied_all_states)) :
									$applied_all_states[] = $rate['country'];
									jigoshop_countries::country_dropdown_options($rate['country'], '*'); //all-states
								else :
									continue;
								endif;
							else :
								jigoshop_countries::country_dropdown_options($rate['country'], $rate['state']);
							endif;
	
							echo '</select><input type="text" class="text" value="' . esc_attr( $rate['rate']  ) . '" name="tax_rate[' . esc_attr( $i ) . ']" title="' . __('Rate', 'jigoshop') . '" placeholder="' . __('Rate', 'jigoshop') . '" maxlength="8" />% <label><input type="checkbox" name="tax_shipping[' . esc_attr( $i ) . ']" ';
	
							if (isset($rate['shipping']) && $rate['shipping'] == 'yes')
								echo 'checked="checked"';
	
							echo ' /> ' . __('Apply to shipping', 'jigoshop') . '</label><label><input type="checkbox" name="tax_compound[' . esc_attr( $i ) . ']" ';
	
							if (isset($rate['compound']) && $rate['compound'] == 'yes')
								echo 'checked="checked"';
	
							echo ' /> ' . __('Compound', 'jigoshop') . '</label><a href="#" class="remove button">&times;</a></p>';
						endforeach;
					}
					?>
				</div>
				<p><a href="#" class="add button"><?php _e('+ Add Tax Rule', 'jigoshop'); ?></a></p>
			</td>
		</tr>
		<script type="text/javascript">
			/* <![CDATA[ */
			jQuery(function() {
				jQuery('#tax_rates a.add').live('click', function(){
					var size = jQuery('.taxrows .taxrow').size();

					// Add the row
					jQuery('<p class="taxrow"> \
						<select name="tax_classes[' + size + ']" title="Tax Classes"> \
							<option value="*"><?php _e('Standard Rate', 'jigoshop'); ?></option><?php
							$tax_classes = $_tax->get_tax_classes();
							if ($tax_classes)
								foreach ($tax_classes as $class) :
									echo '<option value="' . sanitize_title($class) . '">' . $class . '</option>';
								endforeach;
							?></select><input type="text" class="text" name="tax_label[' + size + ']" title="<?php _e('Online Label', 'jigoshop'); ?>" placeholder="<?php _e('Online Label', 'jigoshop'); ?>" maxlength="15" />\
							</select><select name="tax_country[' + size + ']" title="Country"><?php
							jigoshop_countries::country_dropdown_options('', '', true);
							?></select><input type="text" class="text" name="tax_rate[' + size + ']" title="<?php _e('Rate', 'jigoshop'); ?>" placeholder="<?php _e('Rate', 'jigoshop'); ?>" maxlength="8" />%\
							<label><input type="checkbox" name="tax_shipping[' + size + ']" /> <?php _e('Apply to shipping', 'jigoshop'); ?></label>\
							<label><input type="checkbox" name="tax_compound[' + size + ']" /> <?php _e('Compound', 'jigoshop'); ?></label><a href="#" class="remove button">&times;</a>\
											</p>').appendTo('#tax_rates div.taxrows');
								return false;
					});
				jQuery('#tax_rates a.remove').live('click', function(){
					var answer = confirm("<?php _e('Delete this rule?', 'jigoshop'); ?>");
					if (answer) {
						jQuery('input', jQuery(this).parent()).val('');
						jQuery(this).parent().hide();
					}
					return false;
				});
			});
			/* ]]> */
		</script>
	<?php
	}
	
}

?>