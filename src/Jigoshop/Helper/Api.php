<?php

namespace Jigoshop\Helper;

class Api
{
	/**
	 * Returns cleaned and schemed Jigoshop API URL for given API endpoint.
	 *
	 * Null $forceSsl causes function to determine whether to use SSL based on default shop home URL.
	 *
	 * @param $endpoint string Endpoint name.
	 * @param $forceSsl boolean|null Force SSL?
	 * @return string Prepared URL.
	 */
	public static function getUrl($endpoint, $forceSsl = null)
	{
		if ($forceSsl === null) {
			$scheme = parse_url(get_option('home'), PHP_URL_SCHEME);
		} elseif ($forceSsl) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}

		return esc_url_raw(home_url( '/', $scheme ).'?'.\Jigoshop\Api::API_ENDPOINT.'='.$endpoint);
	}
}
