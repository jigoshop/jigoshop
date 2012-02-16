<?php
/**
 * calculable shipping. This class allows the ability to plugin
 * shipping services in order to retrieve shipping information from their servers.
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package		Jigoshop
 * @category	Checkout
 * @author		Jigowatt
 * @copyright	Copyright (c) 2011-2012 Jigowatt Ltd.
 * @license		http://jigoshop.com/license/commercial-edition
 */
abstract class jigoshop_calculable_shipping extends jigoshop_shipping_method {

    protected $from_zip_or_pac; // the zip or postalcode from the shipper
    protected $url; // url to connect to the shipping calculator server
    protected $user_id; // the user id to connect to the shipping servers
    protected $services; // services that have been selected to be used by wp-admin
    protected $rates; // the rates in array format
    protected $has_error; // determines if an error was returned from shipping APIs
    protected $tax_status; // determines if tax should be calculated

    /** constructor */

    protected function __construct() {
        $this->rates = array();
    }

    public function is_available() {
        $is_available = parent::is_available();
        return ($this->has_error ? false : $is_available);
    }

    /**
     * template method that determines the algorithm to send data to and from the shipping
     * server. This method should be called from the implementing classes calculate shipping
     * function.
     */
    final protected function calculate_rate() {

        $services_to_use = $this->filter_services();

        $this->has_error = false;

        // canada post will return all services you have chosen in your online account in the response.
        // most configurations happen on canada post site. Therefore any other services that do something
        // similar, we don't want to take the approach that there are services to loop through here.
        if ($services_to_use) :

            foreach ($services_to_use as $current_service) :

                if (!jigoshop_shipping::show_shipping_calculator() && !( defined('JIGOSHOP_CHECKOUT') && JIGOSHOP_CHECKOUT )) :
                    $request = '';
                    $this->set_error_message('Please proceed to checkout to get shipping estimates');
                else :
                    // create request input for shipping service
                    $request = $this->create_mail_request($current_service);
                endif;

                if ($request) :

                    // send to shipping server and get xml back
                    $post_response = $this->send_to_shipping_server($request);

                    // convert xml into an array
                    $xml_response = $this->convert_xml_to_array($post_response);

                    // sums up the rates from flattened array, and generates amounts.
                    $rate = $this->retrieve_rate_from_response($xml_response);
                    $rate += (empty($this->fee) ? 0 : $this->get_fee($this->fee, jigoshop_cart::$cart_contents_total_ex_dl));

                    $tax = 0;
                    if (get_option('jigoshop_calc_taxes') == 'yes' && $this->tax_status == 'taxable') :
                        $tax = $this->calculate_shipping_tax($rate);
                    endif;

                    // rate should never be 0 or less from shipping API's
                    if ($rate > 0) :
                        $this->rates[] = array('service' => $current_service, 'price' => $rate, 'tax' => $tax);
                    endif;

                endif;

            endforeach;

        else :
            if (!jigoshop_shipping::show_shipping_calculator() && !( defined('JIGOSHOP_CHECKOUT') && JIGOSHOP_CHECKOUT )) :
                $request = '';
                $this->set_error_message('Please proceed to checkout to get shipping estimates');
            else :
                // create request input for shipping service
                $request = $this->create_mail_request($current_service);
            endif;

            if ($request) :

                // send to shipping server and get xml back
                $post_response = $this->send_to_shipping_server($request);

                // convert xml into an array
                $xml_response = $this->convert_xml_to_array($post_response);

                // services are obtained from response
                $services = $this->get_services_from_response($xml_response);

                foreach ($services as $current_service) :
                    $rate = $this->retrieve_rate_from_response($xml_response, $current_service);
                    $rate += (empty($this->fee) ? 0 : $this->get_fee($this->fee, jigoshop_cart::$cart_contents_total_ex_dl));

                    $tax = 0;
                    if (get_option('jigoshop_calc_taxes') == 'yes' && $this->tax_status == 'taxable') :
                        $tax = $this->calculate_shipping_tax($rate);
                    endif;

                    // rate should never be 0 or less from shipping API's
                    if ($rate > 0) :
                        $this->rates[] = array('service' => $current_service, 'price' => $rate, 'tax' => $tax);
                    endif;

                endforeach;

            endif;

        endif;

        // service returned an error since no rates were calculated
        if (($this->rates == NULL || !$this->rates) && !$this->has_error) :
            $this->has_error = true;
        endif;
    }

    protected function calculate_shipping_tax($rate) {

        $_tax = $this->get_tax();

        $tax_rate = $_tax->get_shipping_tax_rate();

        if ($tax_rate > 0) :
            return $_tax->calc_shipping_tax($rate, $tax_rate);
        endif;

        return 0;
    }

    /**
     * This function can be overridden by subclasses if it is needed. If shipping
     * services return all of their services in one reponse xml file, then use this
     * method to handle that task by retrieving the services and returning them in
     * an array.
     *
     * @param array array_response the response from the shipping api's converted
     * to array format
     */
    protected function get_services_from_response($array_response) {
        // added hook for subclasses to retrieve services from the response
        // no need to add logic here as it is meant to be overridden. Not abstract
        // because not all subclasses would need to implement
        return array(); // return empty array
    }

    /**
     * If there are rules that determine which shipping services can be used based on weight
     * of package, size, etc, then this method is used to determine that and remove
     * shipping services that cannot be used because criteria has failed.
     *
     * @return array of services to use for rate calculation
     */
    abstract protected function filter_services();

    /**
     * create the request input that needs to be sent to the shipping server.
     * @return - the request data to be sent to the shipping server. Most likely xml
     */
    abstract protected function create_mail_request($service = '');

    protected function send_to_shipping_server($xml) {
        $request = curl_init($this->url);

        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);

        $post_response = curl_exec($request);

        curl_close($request);

        return $post_response;
    }

    protected function convert_xml_to_array($xml) {
        $xml = strstr($xml, "<?");

        $xml_parser = xml_parser_create();
        xml_parse_into_struct($xml_parser, $xml, $vals, $index);
        xml_parser_free($xml_parser);

        $params = array();
        $level = array();

        foreach ($vals as $xml_elem) {
            if ($xml_elem['type'] == 'open') {
                if (array_key_exists('attributes', $xml_elem)) {
                    list($level[$xml_elem['level']], $extra) = array_values($xml_elem['attributes']);
                } else {
                    $level[$xml_elem['level']] = $xml_elem['tag'];
                }
            }

            if ($xml_elem['type'] == 'complete') {
                $start_level = 1;
                $php_stmt = '$params';

                while ($start_level < $xml_elem['level']) {
                    $php_stmt .= '[$level[' . $start_level . ']]';
                    $start_level++;
                }

                $php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';

                eval($php_stmt);
            }
        }

        return $params;
    }

    /**
     * add up the rate returned from the shipping server and return it
     *
     * @return the rate from the response
     */
    abstract protected function retrieve_rate_from_response($array_response, $service = '');

    /** Gets the from zip or pac code. Used by child classes */
    protected function get_from_zip_or_pac() {
        return $this->from_zip_or_pac;
    }

    /** Gets the url that is used to access the shipping api server */
    protected function get_url() {
        return $this->url;
    }

    /** Gets the user id that will be used to connect to the shipping api server */
    protected function get_user_id() {
        return $this->user_id;
    }

    /** Gets the services that have been enabled as possible shipping methods for the customer */
    protected function get_services() {
        return $this->services;
    }

    public function get_rates_amount() {
        return ($this->rates == NULL ? 0 : count($this->rates));
    }

    public function reset_method() {
        parent::reset_method();
        $this->rates = array();
        $this->has_error = false;
    }

    public function has_error() {
        return $this->has_error;
    }

    private function get_selected_rate($rate_index) {

        return ($this->rates == NULL ? NULL : $this->rates[$rate_index]);
    }

    /**
     * gets the cheapest rate from the rates returned by shipping service. If an error occurred on
     * on the shipping service service, NULL will be returned
     */
    private function get_cheapest_rate() {

        $cheapest_rate = null;
        if ($this->rates != null) :
            for ($i = 0; $i < count($this->rates); $i++) :
                if (!isset($cheapest_rate) || $this->rates[$i]['price'] < $cheapest_rate['price']) :
                    $cheapest_rate = $this->rates[$i];
                endif;
            endfor;
        endif;

        return $cheapest_rate;
    }

    // Override this functions if you want to provide your own
    // label to the service name displayed
    public function get_cheapest_service() {
        $my_cheapest_rate = $this->get_cheapest_rate();
        return ($my_cheapest_rate == NULL ? NULL : $my_cheapest_rate['service']);
    }

    protected function get_cheapest_price() {
        $my_cheapest_rate = $this->get_cheapest_rate();
        return ($my_cheapest_rate == NULL ? NULL : $my_cheapest_rate['price']);
    }

    protected function get_cheapest_price_tax() {
        $my_cheapest_rate = $this->get_cheapest_rate();
        return ($my_cheapest_rate == NULL ? NULL : $my_cheapest_rate['tax']);
    }

    /**
     * Retrieves the service name from the rate array based on the service selected.
     * Override this method if you wish to provide your own user friendly service name
     * @return - NULL if the rate by index doesn't exist, otherwise the service name associated with the
     * service_id
     */
    public function get_selected_service($rate_index) {
        $my_rate = $this->get_selected_rate($rate_index);
        return ($my_rate == NULL ? NULL : $my_rate['service']);
    }

    /**
     * retrieves the price from the rate array based on the rate index.
     * NULL is returned when no service matches the service_id
     */
    public function get_selected_price($rate_index) {
        $my_rate = $this->get_selected_rate($rate_index);
        return ($my_rate == NULL ? NULL : $my_rate['price']);
    }

    public function get_selected_tax($rate_index) {
        $my_rate = $this->get_selected_rate($rate_index);
        return ($my_rate == NULL ? NULL : $my_rate['tax']);
    }

}

