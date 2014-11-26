<?php

namespace Jigoshop;

use Jigoshop\Core\ContainerAware;
use Symfony\Component\DependencyInjection\Container;
use WPAL\Wordpress;

class Api implements ContainerAware
{
	const API_ENDPOINT = 'jigoshop_api';

	/** @var Wordpress */
	private $wp;
	/** @var \JigoshopContainer */
	private $di;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
	}

	public function run()
	{
		$this->wp->addFilter('query_vars', array($this, 'addQueryVars'), 0);
		$this->wp->addAction('init', array($this, 'addEndpoint'), 1);
		$this->wp->addAction('parse_request', array($this, 'parseRequest'), 0);
	}

	/**
	 * Adds Jigoshop API query var to available vars.
	 *
	 * @param $vars array Current list of variables.
	 * @return array Updated list of variables.
	 */
	public function addQueryVars($vars)
	{
		$vars[] = self::API_ENDPOINT;
		return $vars;
	}

	/**
	 * Adds rewrite endpoint for processing Jigoshop APIs
	 */
	public function addEndpoint()
	{
		$this->wp->addRewriteEndpoint(self::API_ENDPOINT, EP_ALL);
	}

	public function parseRequest()
	{
		if (!empty($_GET[self::API_ENDPOINT])) {
			ob_start();
			$api = strtolower(esc_attr($_GET[self::API_ENDPOINT]));
			$availableApi = $this->di->getParameter('api');

			if (isset($availableApi[$api]) && $this->di->has($availableApi[$api])) {
				$bean = $this->di->get($availableApi[$api]);
				if (!($bean instanceof Api\Processable)) {
					if (WP_DEBUG) {
						throw new Exception(__('Provided API is not processable.', 'jigoshop'));
					}
					return;
				}

				$bean->processResponse();
			} else {
				$this->wp->doAction('jigoshop_api_'.$api);
			}

			exit;
		}
	}

	/**
	 * Sets container for every container aware service.
	 *
	 * @param Container $container
	 */
	public function setContainer(Container $container)
	{
		$this->di = $container;
	}
}
