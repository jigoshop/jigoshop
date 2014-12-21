<?php

namespace Jigoshop\Migration;

use WPAL\Wordpress;

class Options
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
	}

	public function migrate()
	{
		$options = $this->wp->getOption('jigoshop_options');
		$transformations = \Jigoshop_Base::get_options()->__getTransformations();

		foreach ($transformations as $old => $new) {
			$this->options->update($new, $options[$old]);
		}

		// TODO: How to migrate plugin options?
	}
}
