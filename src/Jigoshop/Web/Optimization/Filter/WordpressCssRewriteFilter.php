<?php

namespace Jigoshop\Web\Optimization\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\BaseCssFilter;

/**
 * Fixes relative CSS urls.
 *
 * This class is based on CssRewriteFilter from Assetic.
 *
 * @package Jigoshop\Web\Optimization\Filter
 * @author Amadeusz Starzykiewicz
 */
class WordpressCssRewriteFilter extends BaseCssFilter
{
	/** @var  string */
	private $targetPath;

	public function __construct($targetPath)
	{
		$this->targetPath = $targetPath;
	}

	public function filterLoad(AssetInterface $asset)
	{
	}

	public function filterDump(AssetInterface $asset)
	{
		$sourceBase = $asset->getSourceRoot();
		$sourcePath = $asset->getSourcePath();

		if(null === $sourcePath || null === $this->targetPath || $sourcePath == $this->targetPath)
		{
			return;
		}

		// learn how to get from the target back to the source
		if(false !== strpos($sourceBase, '://'))
		{
			list($scheme, $url) = explode('://', $sourceBase.'/'.$sourcePath, 2);
			list($host, $path) = explode('/', $url, 2);

			$host = $scheme.'://'.$host.'/';
			$path = false === strpos($path, '/') ? '' : dirname($path);
			$path .= '/';
		}
		else
		{
			$sourceParts = explode(DIRECTORY_SEPARATOR, $sourceBase);
			$targetParts = explode(DIRECTORY_SEPARATOR, $this->targetPath);

			foreach($sourceParts as $index => $part)
			{
				if(isset($targetParts[$index]) && $part == $targetParts[$index])
				{
					unset($sourceParts[$index], $targetParts[$index]);
				}
			}

			$host = '';
			$path = str_repeat('..'.DIRECTORY_SEPARATOR, count($targetParts)).join(DIRECTORY_SEPARATOR, $sourceParts);
		}

		$content = $this->filterReferences($asset->getContent(), function ($matches) use ($host, $path)
		{
			if(false !== strpos($matches['url'], '://') || 0 === strpos($matches['url'], '//') || 0 === strpos($matches['url'], 'data:'))
			{
				// absolute or protocol-relative or data uri
				return $matches[0];
			}

			if(isset($matches['url'][0]) && '/' == $matches['url'][0])
			{
				// root relative
				return str_replace($matches['url'], $host.$matches['url'], $matches[0]);
			}

			// document relative
			$url = $matches['url'];
			while(0 === strpos($url, '../') && 2 <= substr_count($path, '/'))
			{
				$path = substr($path, 0, strrpos(rtrim($path, '/'), '/') + 1);
				$url = substr($url, 3);
			}

			$parts = array();
			foreach(explode('/', $host.$path.$url) as $part)
			{
				if('..' === $part && count($parts) && '..' !== end($parts))
				{
					array_pop($parts);
				}
				else
				{
					$parts[] = $part;
				}
			}

			return str_replace($matches['url'], implode('/', $parts), $matches[0]);
		});

		$asset->setContent($content);
	}
}
