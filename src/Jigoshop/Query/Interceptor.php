<?php

namespace Jigoshop\Query;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use WPAL\Wordpress;

class Interceptor
{
	private $intercepted = false;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->options = $options;
		$wp->addFilter('request', array($this, 'intercept'));
	}

	public function intercept($request)
	{
		if ($this->intercepted) {
			return $request;
		}

		$this->intercepted = true;
		return $this->parseRequest($request);
	}

	private function parseRequest($request)
	{
		// TODO: Refactor preparing requests
		if (isset($request['post_type']) && $request['post_type'] == Types::PRODUCT) {
			$options = $this->options->get('shopping');

			$request['posts_per_page'] = $options['catalog_per_page'];
			$request['orderby'] = $options['catalog_order_by'];
			$request['order'] = $options['catalog_order'];
		}

		return $request;
	}
}
