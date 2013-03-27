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
 * @package             Jigoshop
 * @category            Checkout
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2013 Jigoshop.
 * @license             http://jigoshop.com/license/commercial-edition
 */
abstract class jigoshop_calculable_shipping extends jigoshop_shipping_method {

    protected $from_zip_or_pac; // the zip or postalcode from the shipper
    protected $url; // url to connect to the shipping calculator server
    protected $user_id; // the user id to connect to the shipping servers
    protected $services; // services that have been selected to be used by wp-admin
    
    /** constructor */
    public function __construct() {
        parent::__construct();

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
                    
                    if ($this->has_error()) :
                        jigoshop_log($xml_response, 'jigoshop_calculable_shipping');
                    endif;
                    
                    $this->add_rate($rate, $current_service);

                endif;

            endforeach;

        else :
            if (!jigoshop_shipping::show_shipping_calculator() && !( defined('JIGOSHOP_CHECKOUT') && JIGOSHOP_CHECKOUT )) :
                $request = '';
                $this->set_error_message('Please proceed to checkout to get shipping estimates');
            else :
                // create request input for shipping service
                $request = $this->create_mail_request();
            endif;

            if ($request) :

                // send to shipping server and get xml back
                $post_response = $this->send_to_shipping_server($request);

                // convert xml into an array
                $xml_response = $this->convert_xml_to_array($post_response);

                // services are obtained from response
                $services = $this->get_services_from_response($xml_response);
                
                if (empty($services)) :
                    jigoshop_log($xml_response, 'jigoshop_calculable_shipping');
                endif;

                foreach ($services as $current_service) :
                    $rate = $this->retrieve_rate_from_response($xml_response, $current_service);
                    $this->add_rate($rate, $current_service);

                endforeach;

            endif;

        endif;

        // service returned an error since no rates were calculated
        if (($this->rates == NULL || !$this->rates) && !$this->has_error()) :
            $this->has_error = true;
        endif;
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
                    list($level[$xml_elem['level']]) = array_values($xml_elem['attributes']);
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

                if (isset($xml_elem['tag']) && isset($xml_elem['value'])) :
                    eval($php_stmt);
                endif;
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

}

