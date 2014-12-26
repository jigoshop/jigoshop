<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Helper\Render;
use WPAL\Wordpress;

class Options implements Tool
{
	const ID = 'jigoshop_options_migration';

	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, \Jigoshop\Core\Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
	}

	/**
	 * @return string Tool ID.
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * Shows migration tool in Migration tab.
	 */
	public function display()
	{
		Render::output('admin/migration/options', array());
	}

	/**
	 * Migrates data from old format to new one.
	 */
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
