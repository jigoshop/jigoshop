<?php

namespace Jigoshop\Helper;

/**
 * Rendering helper.
 *
 * @package Jigoshop\Helper
 * @author Amadeusz Starzykiewicz
 */
class Render
{
	/**
	 * Returns rendered HTML.
	 *
	 * @param string $template Template to render.
	 * @param array $environment Variables to make available to the template
	 * @return string Rendered HTML.
	 */
	public static function get($template, array $environment)
	{
		ob_start();
		self::output($template, $environment);
		return ob_get_clean();
	}

	/**
	 * Outputs HTML template.
	 *
	 * @param string $template Template to render.
	 * @param array $environment Variables to make available to the template
	 */
	public static function output($template, array $environment)
	{
		$file = self::locateTemplate($template);
		extract($environment);
		/** @noinspection PhpIncludeInspection */
		require($file);
	}

	/**
	 * Locates template based on available sources - current theme directory, stylesheet directory and Jigoshop templates directory.
	 *
	 * @param string $template Template to find.
	 * @return string Path to located file.
	 */
	public static function locateTemplate($template)
	{
		$file = locate_template(array('jigoshop/'.$template.'.php'), false, false);
		if (empty($file)) {
			$file = JIGOSHOP_DIR.'/templates/'.$template.'.php';
		}

		return $file;
	}
}