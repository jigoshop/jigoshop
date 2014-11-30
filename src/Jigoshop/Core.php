<?php

namespace Jigoshop;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Template;
use WPAL\Wordpress;

class Core
{
	const VERSION = '2.0-dev';

	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Core\Messages */
	private $messages;
	/** @var \Jigoshop\Core\Pages */
	private $pages;
	/** @var \Jigoshop\Core\Template */
	private $template;
	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, Pages $pages, Template $template)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->pages = $pages;
		$this->template = $template;

		$wp->wpEnqueueScript('jquery');
	}

	/**
	 * Starts Jigoshop extensions and Jigoshop itself.
	 *
	 * @param \JigoshopContainer $container
	 */
	public function run(\JigoshopContainer $container)
	{
		$this->wp->addFilter('template_include', array($this->template, 'process'));
		$this->wp->addFilter('template_redirect', array($this->template, 'redirect'));

		$container->get('jigoshop.permalinks');

		/** @var \Jigoshop\Api $api */
		$api = $container->get('jigoshop.api');
		$api->run();

		// TODO: Why this is required? :/
		$this->wp->flushRewriteRules();
	}
}
