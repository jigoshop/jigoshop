<?php

namespace Jigoshop\Core\Installer;

use WPAL\Wordpress;

interface Initializer
{
	/**
	 * Initializes installation of new part of the system.
	 *
	 * @param Wordpress $wp WPAL instance.
	 */
	public function initialize(Wordpress $wp);
}
