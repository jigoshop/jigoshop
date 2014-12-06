<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product;
use WPAL\Wordpress;

class Downloadable extends Simple
{
	const TYPE = 'downloadable';

	/** @var string */
	private $url;
	/** @var int */
	private $limit;

	public function __construct(Wordpress $wp)
	{
		parent::__construct($wp);
	}

	/**
	 * @return string Product type.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param string $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		$this->dirtyFields[] = 'url';
	}

	/**
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @param int $limit
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
		$this->dirtyFields[] = 'limit';
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		$toSave = parent::getStateToSave();

		foreach ($this->dirtyFields as $field) {
			switch ($field) {
				case 'url':
					$toSave['url'] = $this->url;
					break;
				case 'limit':
					$toSave['limit'] = $this->limit;
					break;
			}
		}

		return $toSave;
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		parent::restoreState($state);

		if (isset($state['url'])) {
			$this->url = $state['url'];
		}
		if (isset($state['limit'])) {
			$this->limit = $state['limit'];
		}
	}

	/**
	 * @return array Minimal state to identify the product.
	 */
	public function getState()
	{
		return array(
			'type' => $this->getType(),
			'id' => $this->getId(),
		);
	}

	/**
	 * Checks whether the product requires shipping.
	 *
	 * @return bool Whether the product requires shipping.
	 */
	public function isShippable()
	{
		return false;
	}
}
