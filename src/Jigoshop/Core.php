<?php

namespace Jigoshop;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Template;
use WPAL\Wordpress;

class Core
{
	const VERSION = '2.0';

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
//		if($container->has('jigoshop.service.shipping')){
//			$service = $container->get('jigoshop.service.shipping');
//
//			// TODO: Build enabled shipping methods
//			$methods = $container->get('jigoshop.shipping.method');
//			var_dump($methods); exit;
//			foreach($methods as $id => $attributes){
////				$service->addMethod();
//			}
//		}

		// TODO: Build required extensions
		$this->wp->addFilter('template_include', array($this->template, 'process'));
		$this->wp->addFilter('template_redirect', array($this->template, 'redirect'));
	}

	/**
	 * @return \WPAL\Wordpress WordPress abstraction instance.
	 */
	public function getWordpress()
	{
		return $this->wp;
	}

	/**
	 * @return Options Options holder.
	 * @since 2.0
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @return Messages Messages container.
	 * @since 2.0
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * @return Pages Helper for checking Jigoshop pages.
	 * @since 2.0
	 */
	public function getPages()
	{
		return $this->pages;
	}
}
