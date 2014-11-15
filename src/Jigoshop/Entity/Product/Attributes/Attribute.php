<?php

namespace Jigoshop\Entity\Product\Attributes;

use Jigoshop\Entity\Product\Attributes\Attribute\Option;

/**
 * Product's attribute.
 *
 * @package Jigoshop\Entity\Product\Attributes
 * @author Amadeusz Starzykiewicz
 */
class Attribute
{
	const MULTISELECT = 0;
	const SELECT = 1;
	const TEXT = 2;
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
	 * Adds option to the attribute.
	 *
	 * @param Option $option Option to add.
	 */
	public function addOption(Attribute\Option $option)
	{
		$this->options[$option->getId()] = $option;
	}

	/**
	 * Removes option from the attribute.
	 *
	 * @param int $id Option ID to remove.
	 * @return Option Removed option.
	 */
	public function removeOption($id)
	{
		$option = $this->options[$id];
		unset($this->options[$id]);
		return $option;
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
		// TODO: Maybe we should keep array and just always join values ion save?
		if ($this->type == Attribute::MULTISELECT) {
			return explode('|', $this->value);
		}

		return $this->value;
	}

	/**
	 * @param mixed $value New value for attribute.
	 */
	public function setValue($value)
	{
		if ($this->type == Attribute::MULTISELECT && is_array($value)) {
			$value = join('|', $value);
		}

		$this->value = $value;
	}
}
