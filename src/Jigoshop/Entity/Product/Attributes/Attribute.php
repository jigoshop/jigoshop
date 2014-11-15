<?php

namespace Jigoshop\Entity\Product\Attributes;

use Jigoshop\Entity\Product\Attributes\Attribute\Multiselect;
use Jigoshop\Entity\Product\Attributes\Attribute\Option;
use Jigoshop\Entity\Product\Attributes\Attribute\Select;
use Jigoshop\Entity\Product\Attributes\Attribute\Text;

/**
 * Product's attribute.
 *
 * @package Jigoshop\Entity\Product\Attributes
 * @author Amadeusz Starzykiewicz
 */
abstract class Attribute
{
	private static $types;

	private $id;
	private $local;
	private $slug;
	private $label;
	protected $options = array();
	protected $value;

	/**
	 * @return array List of available types with its labels.
	 */
	public static function getTypes()
	{
		if (self::$types === null) {
			self::$types = array(
				Multiselect::TYPE => __('Multiselect', 'jigoshop'),
				Select::TYPE => __('Select', 'jigoshop'),
				Text::TYPE => __('Text', 'jigoshop'),
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
	 * @return mixed Attribute value.
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return int Type of attribute.
	 */
	abstract public function getType();

	/**
	 * @param mixed $value New value for attribute.
	 */
	abstract public function setValue($value);

	/**
	 * @return string Value of attribute to be printed.
	 */
	abstract public function printValue();
}
