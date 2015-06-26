<?php

class jigoshop_user
{
	private $id;
	private $billing_first_name;
	private $billing_last_name;
	private $billing_company;
	private $billing_euvatno;
	private $billing_address_1;
	private $billing_address_2;
	private $billing_city;
	private $billing_state;
	private $billing_postcode;
	private $billing_country;
	private $billing_email;
	private $billing_phone;

	private $shipping_first_name;
	private $shipping_last_name;
	private $shipping_company;
	private $shipping_address_1;
	private $shipping_address_2;
	private $shipping_city;
	private $shipping_state;
	private $shipping_postcode;
	private $shipping_country;

	public function __construct($id)
	{
		$this->id = $id;
		$meta = get_user_meta($id);

		if (isset($meta['billing_first_name'])) {
			$this->billing_first_name = $meta['billing_first_name'][0];
		}
		if (isset($meta['billing_last_name'])) {
			$this->billing_last_name = $meta['billing_last_name'][0];
		}
		if (isset($meta['billing_company'])) {
			$this->billing_company = $meta['billing_company'][0];
		}
		if (isset($meta['billing_euvatno'])) {
			$this->billing_euvatno = $meta['billing_euvatno'][0];
		}
		if (isset($meta['billing_address_1'])) {
			$this->billing_address_1 = $meta['billing_address_1'][0];
		}
		if (isset($meta['billing_address_2'])) {
			$this->billing_address_2 = $meta['billing_address_2'][0];
		}
		if (isset($meta['billing_city'])) {
			$this->billing_city = $meta['billing_city'][0];
		}
		if (isset($meta['billing_state'])) {
			$this->billing_state = $meta['billing_state'][0];
		}
		if (isset($meta['billing_postcode'])) {
			$this->billing_postcode = $meta['billing_postcode'][0];
		}
		if (isset($meta['billing_country'])) {
			$country = $meta['billing_country'][0];
			$country = jigoshop_countries::has_country($country) ? $country : jigoshop_countries::get_base_country();
			$this->billing_country = $country;
		}
		if (isset($meta['billing_email'])) {
			$this->billing_email = $meta['billing_email'][0];
		}
		if (isset($meta['billing_first_name'])) {
			$this->billing_phone = $meta['billing_phone'][0];
		}

		if (isset($meta['shipping_first_name'])) {
			$this->shipping_first_name = $meta['shipping_first_name'][0];
		}
		if (isset($meta['shipping_last_name'])) {
			$this->shipping_last_name = $meta['shipping_last_name'][0];
		}
		if (isset($meta['shipping_company'])) {
			$this->shipping_company = $meta['shipping_company'][0];
		}
		if (isset($meta['shipping_address_1'])) {
			$this->shipping_address_1 = $meta['shipping_address_1'][0];
		}
		if (isset($meta['shipping_address_2'])) {
			$this->shipping_address_2 = $meta['shipping_address_2'][0];
		}
		if (isset($meta['shipping_city'])) {
			$this->shipping_city = $meta['shipping_city'][0];
		}
		if (isset($meta['shipping_state'])) {
			$this->shipping_state = $meta['shipping_state'][0];
		}
		if (isset($meta['shipping_postcode'])) {
			$this->shipping_postcode = $meta['shipping_postcode'][0];
		}
		if (isset($meta['shipping_country'])) {
			$country = $meta['shipping_country'][0];
			$country = jigoshop_countries::has_country($country) ? $country : jigoshop_countries::get_base_country();
			$this->shipping_country = $country;
		}
	}

	public function populate(array $data)
	{
		foreach($data as $key => $value) {
			if (isset($this->$key)) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * @return string
	 */
	public function getBillingAddress1()
	{
		return $this->billing_address_1;
	}

	/**
	 * @return string
	 */
	public function getBillingAddress2()
	{
		return $this->billing_address_2;
	}

	/**
	 * @return string
	 */
	public function getBillingCity()
	{
		return $this->billing_city;
	}

	/**
	 * @return string
	 */
	public function getBillingCompany()
	{
		return $this->billing_company;
	}

	/**
	 * @return string
	 */
	public function getBillingEuvatno()
	{
		return $this->billing_euvatno;
	}

	/**
	 * @return string|void
	 */
	public function getBillingCountry()
	{
		return $this->billing_country;
	}

	/**
	 * @return string
	 */
	public function getBillingEmail()
	{
		return $this->billing_email;
	}

	/**
	 * @return string
	 */
	public function getBillingFirstName()
	{
		return $this->billing_first_name;
	}

	/**
	 * @return string
	 */
	public function getBillingLastName()
	{
		return $this->billing_last_name;
	}

	/**
	 * @return string
	 */
	public function getBillingPhone()
	{
		return $this->billing_phone;
	}

	/**
	 * @return string
	 */
	public function getBillingPostcode()
	{
		return $this->billing_postcode;
	}

	/**
	 * @return string
	 */
	public function getBillingState()
	{
		return $this->billing_state;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getShippingAddress1()
	{
		return $this->shipping_address_1;
	}

	/**
	 * @return string
	 */
	public function getShippingAddress2()
	{
		return $this->shipping_address_2;
	}

	/**
	 * @return string
	 */
	public function getShippingCity()
	{
		return $this->shipping_city;
	}

	/**
	 * @return string
	 */
	public function getShippingCompany()
	{
		return $this->shipping_company;
	}

	/**
	 * @return string
	 */
	public function getShippingCountry()
	{
		return $this->shipping_country;
	}

	/**
	 * @return string
	 */
	public function getShippingFirstName()
	{
		return $this->shipping_first_name;
	}

	/**
	 * @return string
	 */
	public function getShippingLastName()
	{
		return $this->shipping_last_name;
	}

	/**
	 * @return string
	 */
	public function getShippingPostcode()
	{
		return $this->shipping_postcode;
	}

	/**
	 * @return string
	 */
	public function getShippingState()
	{
		return $this->shipping_state;
	}

	/**
	 * Saves current user data to database.
	 */
	public function save()
	{
		update_user_meta($this->id, 'billing_first_name', $this->billing_first_name);
		update_user_meta($this->id, 'billing_last_name', $this->billing_last_name);
		update_user_meta($this->id, 'billing_company', $this->billing_company);
		update_user_meta($this->id, 'billing_euvatno', $this->billing_euvatno);
		update_user_meta($this->id, 'billing_address_1', $this->billing_address_1);
		update_user_meta($this->id, 'billing_address_2', $this->billing_address_2);
		update_user_meta($this->id, 'billing_city', $this->billing_city);
		update_user_meta($this->id, 'billing_state', $this->billing_state);
		update_user_meta($this->id, 'billing_postcode', $this->billing_postcode);
		update_user_meta($this->id, 'billing_country', $this->billing_country);
		update_user_meta($this->id, 'billing_email', $this->billing_email);
		update_user_meta($this->id, 'billing_phone', $this->billing_phone);

		update_user_meta($this->id, 'shipping_first_name', $this->shipping_first_name);
		update_user_meta($this->id, 'shipping_last_name', $this->shipping_last_name);
		update_user_meta($this->id, 'shipping_company', $this->shipping_company);
		update_user_meta($this->id, 'shipping_address_1', $this->shipping_address_1);
		update_user_meta($this->id, 'shipping_address_2', $this->shipping_address_2);
		update_user_meta($this->id, 'shipping_city', $this->shipping_city);
		update_user_meta($this->id, 'shipping_state', $this->shipping_state);
		update_user_meta($this->id, 'shipping_postcode', $this->shipping_postcode);
		update_user_meta($this->id, 'shipping_country', $this->shipping_country);
	}
}
