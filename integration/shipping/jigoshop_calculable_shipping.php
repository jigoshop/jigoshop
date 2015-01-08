<?php

use Jigoshop\Integration;

abstract class jigoshop_calculable_shipping extends jigoshop_shipping_method
{
	protected $from_zip_or_pac; // the zip or postalcode from the shipper
	protected $url; // url to connect to the shipping calculator server
	protected $user_id; // the user id to connect to the shipping servers
	protected $services; // services that have been selected to be used by wp-admin

	/** constructor */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * template method that determines the algorithm to send data to and from the shipping
	 * server. This method should be called from the implementing classes calculate shipping
	 * function.
	 */
	final protected function calculate_rate()
	{
		$options = Integration::getOptions();
		$services_to_use = $this->filter_services();
		$this->has_error = false;

		if (!$options->get('shipping.calculator') && !\Jigoshop\Frontend\Pages::isCheckout()) {
			Integration::getMessages()->addError(__('Please proceed to checkout to get shipping estimates', 'jigoshop'));
			return;
		}

		if ($services_to_use) {
			foreach ($services_to_use as $current_service) {
				$request = $this->create_mail_request($current_service);

				if ($request) { // send to shipping server and get xml back
					$post_response = $this->send_to_shipping_server($request);

					// convert xml into an array
					$xml_response = $this->convert_xml_to_array($post_response);

					// sums up the rates from flattened array, and generates amounts.
					$rate = $this->retrieve_rate_from_response($xml_response);

					if ($this->has_error()) {
						\Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($xml_response);
					}

					$this->add_rate($rate, $current_service);
				}
			}
		} else {
			$request = $this->create_mail_request();

			if ($request) { // send to shipping server and get xml back
				$post_response = $this->send_to_shipping_server($request);

				// convert xml into an array
				$xml_response = $this->convert_xml_to_array($post_response);

				// services are obtained from response
				$services = $this->get_services_from_response($xml_response);

				if (empty($services)) {
					\Monolog\Registry::getInstance(JIGOSHOP_LOGGER)->addDebug($xml_response);
				}

				foreach ($services as $current_service) {
					$rate = $this->retrieve_rate_from_response($xml_response, $current_service);
					$this->add_rate($rate, $current_service);
				}
			}
		}

		// service returned an error since no rates were calculated
		if (($this->rates == null || !$this->rates)) {
			$this->has_error = true;
		}
	}

	abstract protected function filter_services();

	abstract protected function create_mail_request($service = '');

	protected function send_to_shipping_server($xml)
	{
		$request = curl_init($this->url);

		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

		$post_response = curl_exec($request);

		curl_close($request);

		return $post_response;
	}

	protected function convert_xml_to_array($xml)
	{
		$xml = strstr($xml, "<?");

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $xml, $vals, $index);
		xml_parser_free($xml_parser);

		$params = array();
		$level = array();

		foreach ($vals as $xml_elem) {
			if ($xml_elem['type'] == 'open') {
				if (array_key_exists('attributes', $xml_elem)) {
					list($level[$xml_elem['level']]) = array_values($xml_elem['attributes']);
				} else {
					$level[$xml_elem['level']] = $xml_elem['tag'];
				}
			}

			if ($xml_elem['type'] == 'complete') {
				$start_level = 1;
				$php_stmt = '$params';

				while ($start_level < $xml_elem['level']) {
					$php_stmt .= '[$level['.$start_level.']]';
					$start_level++;
				}

				$php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';

				if (isset($xml_elem['tag']) && isset($xml_elem['value'])) :
					eval($php_stmt);
				endif;
			}
		}

		return $params;
	}

	abstract protected function retrieve_rate_from_response($array_response, $service = '');

	protected function get_services_from_response($array_response)
	{
		return array();
	}

	protected function get_from_zip_or_pac()
	{
		return $this->from_zip_or_pac;
	}

	protected function get_url()
	{
		return $this->url;
	}

	protected function get_user_id()
	{
		return $this->user_id;
	}

	protected function get_services()
	{
		return $this->services;
	}
}
