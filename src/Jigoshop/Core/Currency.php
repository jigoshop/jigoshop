<?php

namespace Jigoshop\Core;

use Jigoshop\Helper\Product;

/**
 * Available currencies.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class Currency
{
	/**
	 * @return array List of currency symbols.
	 */
	public static function symbols()
	{
		$symbols = array(
			'AED' => '&#1583;&#46;&#1573;', /*'United Arab Emirates dirham'*/
			'AFN' => '&#1547;', /*'Afghanistan Afghani'*/
			'ALL' => 'Lek', /*'Albania Lek'*/
			'ANG' => '&fnof;', /*'Netherlands Antilles Guilder'*/
			'ARS' => '$', /*'Argentina Peso'*/
			'AUD' => '$', /*'Australia Dollar'*/
			'AWG' => '&fnof;', /*'Aruba Guilder'*/
			'AZN' => '&#1084;&#1072;&#1085;', /*'Azerbaijan New Manat'*/
			'BAM' => 'KM', /*'Bosnia and Herzegovina Convertible Marka'*/
			'BBD' => '$', /*'Barbados Dollar'*/
			'BGN' => '&#1083;&#1074;', /*'Bulgaria Lev'*/
			'BMD' => '$', /*'Bermuda Dollar'*/
			'BND' => '$', /*'Brunei Darussalam Dollar'*/
			'BOB' => '$b', /*'Bolivia Boliviano'*/
			'BRL' => '&#82;&#36;', /*'Brazil Real'*/
			'BSD' => '$', /*'Bahamas Dollar'*/
			'BWP' => 'P', /*'Botswana Pula'*/
			'BYR' => 'p.', /*'Belarus Ruble'*/
			'BZD' => 'BZ$', /*'Belize Dollar'*/
			'CAD' => '$', /*'Canada Dollar'*/
			'CHF' => 'CHF', /*'Switzerland Franc'*/
			'CLP' => '$', /*'Chile Peso'*/
			'CNY' => '&yen;', /*'China Yuan Renminbi'*/
			'COP' => '$', /*'Colombia Peso'*/
			'CRC' => '&#8353;', /*'Costa Rica Colon'*/
			'CUP' => '&#8369;', /*'Cuba Peso'*/
			'CZK' => 'K&#269;', /*'Czech Republic Koruna'*/
			'DKK' => 'kr', /*'Denmark Krone'*/
			'DOP' => 'RD$', /*'Dominican Republic Peso'*/
			'EEK' => 'kr', /*'Estonia Kroon'*/
			'EGP' => '&pound;', /*'Egypt Pound'*/
			'EUR' => '&euro;', /*'Euro Member Countries'*/
			'FJD' => '$', /*'Fiji Dollar'*/
			'FKP' => '&pound;', /*'Falkland Islands'*/
			'GBP' => '&pound;', /*'United Kingdom Pound'*/
			'GEL' => 'ლ', /*'Georgia Lari'*/
			'GGP' => '&pound;', /*'Guernsey Pound'*/
			'GHC' => '&cent;', /*'Ghana Cedis'*/
			'GIP' => '&cent;', /*'Gibraltar Pound'*/
			'GTQ' => 'Q', /*'Guatemala Quetzal'*/
			'GYD' => '$', /*'Guyana Dollar'*/
			'HKD' => '$', /*'Hong Kong Dollar'*/
			'HNL' => 'L', /*'Honduras Lempira'*/
			'HRK' => 'kn', /*'Croatia Kuna'*/
			'HUF' => '&#70;&#116;', /*'Hungary Forint'*/
			'IDR' => '&#82;&#112;', /*'Indonesia Rupiah'*/
			'ILS' => '&#8362;', /*'Israel Shekel'*/
			'IMP' => '&pound;', /*'Isle of Man Pound'*/
			'INR' => '&#8360;', /*'India Rupee'*/
			'IRR' => '&#65020;', /*'Iran Rial'*/
			'ISK' => 'kr', /*'Iceland Krona'*/
			'JEP' => '&pound;', /*'Jersey Pound'*/
			'JMD' => 'J$', /*'Jamaica Dollar'*/
			'JPY' => '&yen;', /*'Japan Yen'*/
			'KGS' => '&#1083;&#1074;', /*'Kyrgyzstan Som'*/
			'KHR' => '&#6107;', /*'Cambodia Riel'*/
			'KPW' => '&#8361;', /*'North Korea Won'*/
			'KRW' => '&#8361;', /*'South Korea Won'*/
			'KYD' => '$', /*'Cayman Islands Dollar'*/
			'KZT' => '&#1083;&#1074;', /*'Kazakhstan Tenge'*/
			'LAK' => '&#8365;', /*'Laos Kip'*/
			'LBP' => '&pound;', /*'Lebanon Pound'*/
			'LKR' => '&#8360;', /*'Sri Lanka Rupee'*/
			'LRD' => '$', /*'Liberia Dollar'*/
			'LTL' => 'Lt', /*'Lithuania Litas'*/
			'LVL' => 'Ls', /*'Latvia Lat'*/
			'MAD' => '&#1583;.&#1605;.', /*'Moroccan Dirham'*/
			'MKD' => '&#1076;&#1077;&#1085;', /*'Macedonia Denar'*/
			'MNT' => '&#8366;', /*'Mongolia Tughrik'*/
			'MUR' => '&#8360;', /*'Mauritius Rupee'*/
			'MXN' => '&#36;', /*'Mexico Peso'*/
			'MYR' => 'RM', /*'Malaysia Ringgit'*/
			'MZN' => 'MT', /*'Mozambique Metical'*/
			'NAD' => '$', /*'Namibia Dollar'*/
			'NGN' => '&#8358;', /*'Nigeria Naira'*/
			'NIO' => 'C$', /*'Nicaragua Cordoba'*/
			'NOK' => 'kr', /*'Norway Krone'*/
			'NPR' => '&#8360;', /*'Nepal Rupee'*/
			'NZD' => '$', /*'New Zealand Dollar'*/
			'OMR' => '&#65020;', /*'Oman Rial'*/
			'PAB' => 'B/.', /*'Panama Balboa'*/
			'PEN' => 'S/.', /*'Peru Nuevo Sol'*/
			'PHP' => '&#8369;', /*'Philippines Peso'*/
			'PKR' => '&#8360;', /*'Pakistan Rupee'*/
			'PLN' => '&#122;&#322;', /*'Poland Zloty'*/
			'PYG' => 'Gs', /*'Paraguay Guarani'*/
			'QAR' => '&#65020;', /*'Qatar Riyal'*/
			'RON' => '&#108;&#101;&#105;', /*'Romania New Leu'*/
			'RSD' => 'РСД', /*'Serbia Dinar'*/
			'RUB' => '&#1088;&#1091;&#1073;', /*'Russia Ruble'*/
			'SAR' => '&#65020;', /*'Saudi Arabia Riyal'*/
			'SBD' => '$', /*'Solomon Islands Dollar'*/
			'SCR' => '&#8360;', /*'Seychelles Rupee'*/
			'SEK' => 'kr', /*'Sweden Krona'*/
			'SGD' => '$', /*'Singapore Dollar'*/
			'SHP' => '&pound;', /*'Saint Helena Pound'*/
			'SOS' => 'S', /*'Somalia Shilling'*/
			'SRD' => '$', /*'Suriname Dollar'*/
			'SVC' => '$', /*'El Salvador Colon'*/
			'SYP' => '&pound;', /*'Syria Pound'*/
			'THB' => '&#3647;', /*'Thailand Baht'*/
			'TRL' => '&#8356;', /*'Turkey Lira'*/
			'TRY' => 'TL', /*'Turkey Lira'*/
			'TTD' => 'TT$', /*'Trinidad and Tobago Dollar'*/
			'TVD' => '$', /*'Tuvalu Dollar'*/
			'TWD' => 'NT$', /*'Taiwan New Dollar'*/
			'UAH' => '&#8372;', /*'Ukraine Hryvna'*/
			'USD' => '$', /*'United States Dollar'*/
			'UYU' => '$U', /*'Uruguay Peso'*/
			'UZS' => '&#1083;&#1074;', /*'Uzbekistan Som'*/
			'VEF' => 'Bs', /*'Venezuela Bolivar Fuerte'*/
			'VND' => '&#8363;', /*'Viet Nam Dong'*/
			'XCD' => '$', /*'East Caribbean Dollar'*/
			'YER' => '&#65020;', /*'Yemen Rial'*/
			'ZAR' => 'R', /*'South Africa Rand'*/
			'ZWD' => 'Z$', /*'Zimbabwe Dollar'*/
		);

		ksort($symbols);

		return $symbols;
	}

	/**
	 * @return array List of countries with selected currency.
	 */
	public static function countries()
	{
		$countries = array(
			'AED' => __('United Arab Emirates dirham', 'jigoshop'),
			'AFN' => __('Afghanistan Afghani', 'jigoshop'),
			'ALL' => __('Albania Lek', 'jigoshop'),
			'ANG' => __('Netherlands Antilles Guilder', 'jigoshop'),
			'ARS' => __('Argentina Peso', 'jigoshop'),
			'AUD' => __('Australia Dollar', 'jigoshop'),
			'AWG' => __('Aruba Guilder', 'jigoshop'),
			'AZN' => __('Azerbaijan New Manat', 'jigoshop'),
			'BAM' => __('Bosnia and Herzegovina Convertible Marka', 'jigoshop'),
			'BBD' => __('Barbados Dollar', 'jigoshop'),
			'BGN' => __('Bulgaria Lev', 'jigoshop'),
			'BMD' => __('Bermuda Dollar', 'jigoshop'),
			'BND' => __('Brunei Darussalam Dollar', 'jigoshop'),
			'BOB' => __('Bolivia Boliviano', 'jigoshop'),
			'BRL' => __('Brazil Real', 'jigoshop'),
			'BSD' => __('Bahamas Dollar', 'jigoshop'),
			'BWP' => __('Botswana Pula', 'jigoshop'),
			'BYR' => __('Belarus Ruble', 'jigoshop'),
			'BZD' => __('Belize Dollar', 'jigoshop'),
			'CAD' => __('Canada Dollar', 'jigoshop'),
			'CHF' => __('Switzerland Franc', 'jigoshop'),
			'CLP' => __('Chile Peso', 'jigoshop'),
			'CNY' => __('China Yuan Renminbi', 'jigoshop'),
			'COP' => __('Colombia Peso', 'jigoshop'),
			'CRC' => __('Costa Rica Colon', 'jigoshop'),
			'CUP' => __('Cuba Peso', 'jigoshop'),
			'CZK' => __('Czech Republic Koruna', 'jigoshop'),
			'DKK' => __('Denmark Krone', 'jigoshop'),
			'DOP' => __('Dominican Republic Peso', 'jigoshop'),
			'EEK' => __('Estonia Kroon', 'jigoshop'),
			'EGP' => __('Egypt Pound', 'jigoshop'),
			'EUR' => __('Euro Member Countries', 'jigoshop'),
			'FJD' => __('Fiji Dollar', 'jigoshop'),
			'FKP' => __('Falkland Islands', 'jigoshop'),
			'GBP' => __('United Kingdom Pound', 'jigoshop'),
			'GEL' => __('Georgian Lari', 'jigoshop'),
			'GGP' => __('Guernsey Pound', 'jigoshop'),
			'GHC' => __('Ghana Cedis', 'jigoshop'),
			'GIP' => __('Gibraltar Pound', 'jigoshop'),
			'GTQ' => __('Guatemala Quetzal', 'jigoshop'),
			'GYD' => __('Guyana Dollar', 'jigoshop'),
			'HKD' => __('Hong Kong Dollar', 'jigoshop'),
			'HNL' => __('Honduras Lempira', 'jigoshop'),
			'HRK' => __('Croatia Kuna', 'jigoshop'),
			'HUF' => __('Hungary Forint', 'jigoshop'),
			'IDR' => __('Indonesia Rupiah', 'jigoshop'),
			'ILS' => __('Israel Shekel', 'jigoshop'),
			'IMP' => __('Isle of Man Pound', 'jigoshop'),
			'INR' => __('India Rupee', 'jigoshop'),
			'IRR' => __('Iran Rial', 'jigoshop'),
			'ISK' => __('Iceland Krona', 'jigoshop'),
			'JEP' => __('Jersey Pound', 'jigoshop'),
			'JMD' => __('Jamaica Dollar', 'jigoshop'),
			'JPY' => __('Japan Yen', 'jigoshop'),
			'KGS' => __('Kyrgyzstan Som', 'jigoshop'),
			'KHR' => __('Cambodia Riel', 'jigoshop'),
			'KPW' => __('North Korea Won', 'jigoshop'),
			'KRW' => __('South Korea Won', 'jigoshop'),
			'KYD' => __('Cayman Islands Dollar', 'jigoshop'),
			'KZT' => __('Kazakhstan Tenge', 'jigoshop'),
			'LAK' => __('Laos Kip', 'jigoshop'),
			'LBP' => __('Lebanon Pound', 'jigoshop'),
			'LKR' => __('Sri Lanka Rupee', 'jigoshop'),
			'LRD' => __('Liberia Dollar', 'jigoshop'),
			'LTL' => __('Lithuania Litas', 'jigoshop'),
			'LVL' => __('Latvia Lat', 'jigoshop'),
			'MAD' => __('Moroccan Dirham', 'jigoshop'),
			'MKD' => __('Macedonia Denar', 'jigoshop'),
			'MNT' => __('Mongolia Tughrik', 'jigoshop'),
			'MUR' => __('Mauritius Rupee', 'jigoshop'),
			'MXN' => __('Mexico Peso', 'jigoshop'),
			'MYR' => __('Malaysia Ringgit', 'jigoshop'),
			'MZN' => __('Mozambique Metical', 'jigoshop'),
			'NAD' => __('Namibia Dollar', 'jigoshop'),
			'NGN' => __('Nigeria Naira', 'jigoshop'),
			'NIO' => __('Nicaragua Cordoba', 'jigoshop'),
			'NOK' => __('Norway Krone', 'jigoshop'),
			'NPR' => __('Nepal Rupee', 'jigoshop'),
			'NZD' => __('New Zealand Dollar', 'jigoshop'),
			'OMR' => __('Oman Rial', 'jigoshop'),
			'PAB' => __('Panama Balboa', 'jigoshop'),
			'PEN' => __('Peru Nuevo Sol', 'jigoshop'),
			'PHP' => __('Philippines Peso', 'jigoshop'),
			'PKR' => __('Pakistan Rupee', 'jigoshop'),
			'PLN' => __('Poland Zloty &#122;&#322;', 'jigoshop'),
			'PYG' => __('Paraguay Guarani', 'jigoshop'),
			'QAR' => __('Qatar Riyal', 'jigoshop'),
			'RON' => __('Romania New Leu', 'jigoshop'),
			'RSD' => __('Serbia Dinar', 'jigoshop'),
			'RUB' => __('Russia Ruble', 'jigoshop'),
			'SAR' => __('Saudi Arabia Riyal', 'jigoshop'),
			'SBD' => __('Solomon Islands Dollar', 'jigoshop'),
			'SCR' => __('Seychelles Rupee', 'jigoshop'),
			'SEK' => __('Sweden Krona', 'jigoshop'),
			'SGD' => __('Singapore Dollar', 'jigoshop'),
			'SHP' => __('Saint Helena Pound', 'jigoshop'),
			'SOS' => __('Somalia Shilling', 'jigoshop'),
			'SRD' => __('Suriname Dollar', 'jigoshop'),
			'SVC' => __('El Salvador Colon', 'jigoshop'),
			'SYP' => __('Syria Pound', 'jigoshop'),
			'THB' => __('Thailand Baht', 'jigoshop'),
			'TRL' => __('Turkey Lira', 'jigoshop'),
			'TRY' => __('Turkey Lira', 'jigoshop'),
			'TTD' => __('Trinidad and Tobago Dollar', 'jigoshop'),
			'TVD' => __('Tuvalu Dollar', 'jigoshop'),
			'TWD' => __('Taiwan New Dollar', 'jigoshop'),
			'UAH' => __('Ukraine Hryvna', 'jigoshop'),
			'USD' => __('United States Dollar', 'jigoshop'),
			'UYU' => __('Uruguay Peso', 'jigoshop'),
			'UZS' => __('Uzbekistan Som', 'jigoshop'),
			'VEF' => __('Venezuela Bolivar Fuerte', 'jigoshop'),
			'VND' => __('Viet Nam Dong', 'jigoshop'),
			'XCD' => __('East Caribbean Dollar', 'jigoshop'),
			'YER' => __('Yemen Rial', 'jigoshop'),
			'ZAR' => __('South Africa Rand', 'jigoshop'),
			'ZWD' => __('Zimbabwe Dollar', 'jigoshop'),
		);

		asort($countries);

		return $countries;
	}

	public static function displays()
	{
		$symbol = Product::currencySymbol();
		$separator = '.'; // TODO: Introduce decimal separator
		$code = 'USD'; // TODO: Introduce currency code

		// TODO: Maybe replace with proper sprintf() text?
		return array(
			'left' => sprintf('%1$s0%2$s00', $symbol, $separator),// symbol.'0'.separator.'00'
			'left_space' => sprintf('%1$s0 %2$s00', $symbol, $separator),// symbol.' 0'.separator.'00'
			'right' => sprintf('0%2$s00%1$s', $symbol, $separator),// '0'.separator.'00'.symbol
			'right_space' => sprintf('0%2$s00 %1$s', $symbol, $separator),// '0'.separator.'00 '.symbol
			'left_code' => sprintf('%1$s0%2$s00', $code, $separator),// code.'0'.separator.'00'
			'left_code_space' => sprintf('%1$s 0%2$s00', $code, $separator),// code.' 0'.separator.'00'
			'right_code' => sprintf('0%2$s00%1$s', $code, $separator),// '0'.separator.'00'.code
			'right_code_space' => sprintf('0%2$s00 %1$s', $code, $separator),// '0'.separator.'00 '.code
			'symbol_code' => sprintf('%1$s0%2$s00%3$s', $symbol, $separator, $code),// symbol.'0'.separator.'00'.code
			'symbol_code_space' => sprintf('%1$s 0%2$s00 %3$s', $symbol, $separator, $code),// symbol.' 0'.separator.'00 '.code
			'code_symbol' => sprintf('%3$s0%2$s00%1$s', $symbol, $separator, $code),// code.'0'.separator.'00'.symbol
			'code_symbol_space' => sprintf('%3$s 0%2$s00 %1$s', $symbol, $separator, $code),// code.' 0'.separator.'00 '.symbol
		);
	}
}