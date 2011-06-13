<?php
/**
 * Jigoshop countries
 * @class 		jigoshop_countries
 * 
 * The JigoShop countries class stores country/state data.
 *
 * @author 		Jigowatt
 * @category 	Classes
 * @package 	JigoShop
 */
class jigoshop_countries {
	
	public static $countries = array(
		'DZ' => 'Algeria' , 
		'AR' => 'Argentina' ,
		'AW' => 'Aruba' ,
		'AU' => 'Australia' ,
		'AT' => 'Austria' ,
		'BB' => 'Barbados' ,
		'BS' => 'Bahamas' ,
		'BH' => 'Bahrain' ,
		'BE' => 'Belgium' ,
		'BR' => 'Brazil' ,
		'BG' => 'Bulgaria' ,
		'CA' => 'Canada' , 
		'CL' => 'Chile' ,
		'CN' => 'China' ,
		'CO' => 'Colombia' ,
		'CR' => 'Costa Rica' ,
		'HR' => 'Croatia' ,
		'CY' => 'Cyprus' ,
		'CZ' => 'Czech Republic' ,
		'DK' => 'Denmark' , 
		'DO' => 'Dominican Republic' , 
		'EC' => 'Ecuador' , 
		'EG' => 'Egypt' ,
		'EE' => 'Estonia' ,
		'FI' => 'Finland' ,
		'FR' => 'France' ,
		'DE' => 'Germany' , 
		'GR' => 'Greece' , 
		'GP' => 'Guadeloupe' , 
		'GT' => 'Guatemala' , 
		'HK' => 'Hong Kong' , 
		'HU' => 'Hungary' ,
		'IS' => 'Iceland' ,
		'IN' => 'India' ,
		'ID' => 'Indonesia' ,
		'IE' => 'Ireland' ,
		'IL' => 'Israel' ,
		'IT' => 'Italy' ,
		'JM' => 'Jamaica' ,
		'JP' => 'Japan' ,
		'LV' => 'Latvia' ,
		'LT' => 'Lithuania' ,
		'LU' => 'Luxembourg' ,
		'MY' => 'Malaysia' ,
		'MT' => 'Malta' ,
		'MX' => 'Mexico' ,
		'NL' => 'Netherlands' ,
		'NZ' => 'New Zealand' ,
		'NG' => 'Nigeria' ,
		'NO' => 'Norway' ,
		'PK' => 'Pakistan' ,
		'PE' => 'Peru' ,
		'PH' => 'Philippines' ,
		'PL' => 'Poland' ,
		'PT' => 'Portugal' ,
		'PR' => 'Puerto Rico' ,
		'RO' => 'Romania' ,
		'RU' => 'Russia' ,
		'SG' => 'Singapore' ,
		'SK' => 'Slovakia' ,
		'SI' => 'Slovenia' ,
		'ZA' => 'South Africa' ,
		'KR' => 'South Korea' ,
		'ES' => 'Spain' ,
		'VC' => 'St. Vincent' ,
		'SE' => 'Sweden' ,
		'CH' => 'Switzerland' ,
		'TW' => 'Taiwan' ,
		'TH' => 'Thailand' ,
		'TT' => 'Trinidad and Tobago' ,
		'TR' => 'Turkey' ,
		'UA' => 'Ukraine' ,
		'AE' => 'United Arab Emirates' ,
		'GB' => 'United Kingdom' , 
		'US' => 'United States' , 
		'UY' => 'Uruguay' ,
	  	'USAF' => 'US Armed Forces' , 
		'VE' => 'Venezuela' 
	);
	
	public static $states = array(
		'AU' => array(
			'ACT' =>  'Australian Capital Territory' ,
			'NSW' =>  'New South Wales' ,
			'NT' =>  'Northern Territory' ,
			'QLD' =>  'Queensland' ,
			'SA' =>  'South Australia' ,
			'TAS' =>  'Tasmania' ,
			'VIC' =>  'Victoria' ,
			'WA' =>  'Western Australia' 
		),
		'CA' => array(
			'AB' =>  'Alberta' ,
			'BC' =>  'British Columbia' ,
			'MB' =>  'Manitoba' ,
			'NB' =>  'New Brunswick' ,
			'NF' =>  'Newfoundland' ,
			'NT' =>  'Northwest Territories' ,
			'NS' =>  'Nova Scotia' ,
			'NU' =>  'Nunavut' ,
			'ON' =>  'Ontario' ,
			'PE' =>  'Prince Edward Island' ,
			'PQ' =>  'Quebec' ,
			'SK' =>  'Saskatchewan' ,
			'YT' =>  'Yukon Territory' 
		),
		'US' => array(
			'AL' =>  'Alabama' ,
			'AK' =>  'Alaska ' ,
			'AZ' =>  'Arizona' ,
			'AR' =>  'Arkansas' ,
			'CA' =>  'California' ,
			'CO' =>  'Colorado' ,
			'CT' =>  'Connecticut' ,
			'DE' =>  'Delaware' ,
			'DC' =>  'District Of Columbia' ,
			'FL' =>  'Florida' ,
			'GA' =>  'Georgia' ,
			'HI' =>  'Hawaii' ,
			'ID' =>  'Idaho' ,
			'IL' =>  'Illinois' ,
			'IN' =>  'Indiana' ,
			'IA' =>  'Iowa' ,
			'KS' =>  'Kansas' ,
			'KY' =>  'Kentucky' ,
			'LA' =>  'Louisiana' ,
			'ME' =>  'Maine' ,
			'MD' =>  'Maryland' ,
			'MA' =>  'Massachusetts' ,
			'MI' =>  'Michigan' ,
			'MN' =>  'Minnesota' ,
			'MS' =>  'Mississippi' ,
			'MO' =>  'Missouri' ,
			'MT' =>  'Montana' ,
			'NE' =>  'Nebraska' ,
			'NV' =>  'Nevada' ,
			'NH' =>  'New Hampshire' ,
			'NJ' =>  'New Jersey' ,
			'NM' =>  'New Mexico' ,
			'NY' =>  'New York' ,
			'NC' =>  'North Carolina' ,
			'ND' =>  'North Dakota' ,
			'OH' =>  'Ohio' ,
			'OK' =>  'Oklahoma' ,
			'OR' =>  'Oregon' ,
			'PA' =>  'Pennsylvania' ,
			'RI' =>  'Rhode Island' ,
			'SC' =>  'South Carolina' ,
			'SD' =>  'South Dakota' ,
			'TN' =>  'Tennessee' ,
			'TX' =>  'Texas' ,
			'UT' =>  'Utah' ,
			'VT' =>  'Vermont' ,
			'VA' =>  'Virginia' ,
			'WA' =>  'Washington' ,
			'WV' =>  'West Virginia' ,
			'WI' =>  'Wisconsin' ,
			'WY' =>  'Wyoming' 
		),
		'USAF' => array(
			'AA' =>  'Americas' ,
			'AE' =>  'Europe' ,
			'AP' =>  'Pacific' 
		)
	);
	
	/** get countries we allow only */
	function get_allowed_countries() {
		
		if (get_option('jigoshop_allowed_countries')!=='specific') return self::$countries;

		$allowed_countries = array();
		
		$allowed_countries_raw = get_option('jigoshop_specific_allowed_countries');
		
		foreach ($allowed_countries_raw as $country) :
			
			$allowed_countries[$country] = self::$countries[$country];
			
		endforeach;
		
		return $allowed_countries;
	}
	
	/** Gets the correct string for shipping - ether 'to the' or 'to' */
	function shipping_to_prefix() {
		$return = '';
		if (in_array(jigoshop_customer::get_country(), array( 'GB', 'US', 'AE', 'CZ', 'DO', 'NL', 'PH', 'USAF' ))) $return = __('to the', 'jigoshop');
		else $return = __('to', 'jigoshop');
		$return = apply_filters('shipping_to_prefix', $return, jigoshop_customer::get_country());
		return $return;
	}
	
	/** get states */
	function get_states( $cc ) {
		if (isset( self::$states[$cc] )) return self::$states[$cc];
	}
	
	/** Outputs the list of countries and states for use in dropdown boxes */
	function country_dropdown_options( $selected_country = '', $selected_state = '' ) {
		if ( self::$countries) foreach ( self::$countries as $key=>$value) :
			if ( $states =  self::get_states($key) ) :
				echo '<optgroup label="'.$value.'">';
    				echo '<option value="'.$key.'"';
    				if ($selected_country==$key && $selected_state=='*') echo ' selected="selected"';
    				echo '>'.$value.' &mdash; '.__('All states', 'jigoshop').'</option>';
    				foreach ($states as $state_key=>$state_value) :
    					echo '<option value="'.$key.':'.$state_key.'"';
    					
    					if ($selected_country==$key && $selected_state==$state_key) echo ' selected="selected"';
    					
    					echo '>'.$value.' &mdash; '.$state_value.'</option>';
    				endforeach;
    			echo '</optgroup>';
			else :
    			echo '<option';
    			if ($selected_country==$key && $selected_state=='*') echo ' selected="selected"';
    			echo ' value="'.$key.'">'.$value.'</option>';
			endif;
		endforeach;
	}
}