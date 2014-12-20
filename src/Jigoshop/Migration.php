<?php

namespace Jigoshop;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Product;
use WPAL\Wordpress;

/**
 * Migration helper - transforms Jigoshop 1.x entities into Jigoshop 2.x ones.
 *
 * WARNING: Do NOT use this class, it is useful only as transition for Jigoshop 1.x and will be removed in future!
 */
class Migration
{
	/** @var Wordpress */
	private static $wp;
	/** @var Options */
	private static $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		self::$wp = $wp;
		self::$options = $options;
	}

	public static function migrateOptions()
	{
		$options = self::$wp->getOption('jigoshop_options');
		$transformations = \Jigoshop_Base::get_options()->__getTransformations();

		foreach ($transformations as $old => $new) {
			self::$options->update($new, $options[$old]);
		}

		// TODO: How to migrate plugin options?
	}
}
