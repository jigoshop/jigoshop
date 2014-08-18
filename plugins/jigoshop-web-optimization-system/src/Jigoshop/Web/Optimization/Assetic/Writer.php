<?php

namespace Jigoshop\Web\Optimization\Assetic;

use Assetic\Asset\AssetInterface;
use Assetic\AssetWriter;
use Assetic\Util\VarUtils;

/**
 * Asset Writer class.
 *
 * Checks if file exists before writing it again.
 *
 * @package Jigoshop\Web\Optimization\Assetic
 * @author Amadeusz Starzykiewicz
 */
class Writer extends AssetWriter
{
	private $dir;

	public function __construct($dir, array $values = array())
	{
		$this->dir = $dir;
		parent::__construct($dir, $values);
	}

	public function writeAsset(AssetInterface $asset)
	{
		$name = VarUtils::resolve(
			$asset->getTargetPath(),
			$asset->getVars(),
			$asset->getValues()
		);

		if(!file_exists($this->dir.DIRECTORY_SEPARATOR.$name))
		{
			parent::writeAsset($asset);
		}
	}
}