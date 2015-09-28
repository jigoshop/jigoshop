<?php

/**
 * Created by PhpStorm.
 * User: Borbis Media
 * Date: 2015-09-25
 * Time: 13:31
 */
class jigoshop_euvat_validator extends Jigoshop_Singleton
{
	const WSDL = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";

	private static $_client = null;
	private static $_valid = false;
	private static $_data = array();

	public function __construct($options = array()) {
		if(!class_exists('SoapClient')) {
			throw new Exception('The Soap library has to be installed and enabled');
		}

		try {
			self::$_client = new SoapClient(self::WSDL);
		} catch(Exception $e) {
			jigoshop::add_error(__('Unable to connect to Check Vat Service Api', 'jigoshop'));
		}
	}

	public static function check($countryCode, $vatNumber)
	{
		try {
			$rs = self::$_client->checkVat(array('countryCode' => $countryCode, 'vatNumber' => $vatNumber));
		} catch(SoapFault $error) {
			return false;
		}
		if($rs->valid) {
			self::$_valid = true;
			list($denomination,$name) = explode(" " ,$rs->name,2);
			self::$_data = array(
				'denomination' => 	$denomination,
				'name' => 			$name,
				'address' => 		$rs->address,
			);

			return true;
		} else {
			self::$_valid = false;
			self::$_data = array();

			return false;
		}
	}

	public static function isValid() {
		return self::$_valid;
	}

	public static function getDenomination() {
		return self::$_data['denomination'];
	}

	public static function getName() {
		return self::$_data['name'];
	}

	public static function getAddress() {
		return self::$_data['address'];
	}
}