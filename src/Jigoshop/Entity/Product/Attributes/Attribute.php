<?php

namespace Jigoshop\Entity\Product\Attributes;

/**
 * Product's attribute.
 *
 * @package Jigoshop\Entity\Product\Attributes
 * @author Amadeusz Starzykiewicz
 */
class Attribute
{
	const MULTISELECT = 'multiselect';
	const SELECT = 'select';
	const TEXT = 'text';
	private static $types;

	private $id;
	private $local;
	private $slug;
	private $label;
	private $type;
	private $options = array();
	private $value;

	/**
	 * @return array List of available types with its labels.
	 */
	public static function getTypes()
	{
		if (self::$types === null) {
			self::$types = array(
				self::MULTISELECT => __('Multiselect', 'jigoshop'),
				self::SELECT => __('Select', 'jigoshop'),
				self::TEXT => __('Text', 'jigoshop'),
			);
		}

		return self::$types;
	}

	/**
	 * @return int Attribute ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id New ID for attribute.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return boolean Is attribute local for a product?
	 */
	public function isLocal()
	{
		return $this->local;
	}

	/**
	 * @param boolean $isLocal Is attribute local for a product?
	 */
	public function setLocal($isLocal)
	{
		$this->local = $isLocal;
	}

	/**
	 * @return string Product human-readable name.
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @param string $label New label for attribute.
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return array List of available options for attribute.
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @param array $options Mew set of available options.
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}

	/**
	 * @return string Simplified name for URL purposes.
	 */
	public function getSlug()
	{
		return $this->slug;
	}

	/**
	 * @param string $slug New slug.
	 */
	public function setSlug($slug)
	{
		$this->slug = $slug;
	}

	/**
	 * @return int Type of attribute.
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param int $type New attribute type.
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return mixed Attribute value.
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value New value for attribute.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
}
