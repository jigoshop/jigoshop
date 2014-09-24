<?php

namespace Jigoshop\Query;

use Jigoshop\Core\Types;
use WPAL\Wordpress;

class Interceptor
{
	private $intercepted = false;

	public function __construct(Wordpress $wp)
	{
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
			$request['posts_per_page'] = 1;
		}

		return $request;
	}
}
