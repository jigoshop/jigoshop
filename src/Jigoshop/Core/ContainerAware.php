<?php

namespace Jigoshop\Core;

use Symfony\Component\DependencyInjection\Container;

/**
 * Interface for Container aware services.
 *
 * Please note you have to add jigoshop.container_aware tag to services implementing this interface.
 *
 * @package Jigoshop\Core
 */
interface ContainerAware
{
	/**
	 * Sets container for every container aware service.
	 *
	 * @param Container $container
	 */
	public function setContainer(Container $container);
}
