<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product\Attribute\Multiselect;
use Jigoshop\Entity\Product\Attribute\Option;
use Jigoshop\Entity\Product\Attribute\Select;
use Jigoshop\Entity\Product\Attribute\Text;

/**
 * Product's attribute.
 *
 * @package Jigoshop\Entity\Product\Attributes
 * @author Amadeusz Starzykiewicz
 */
abstract class Attribute
{
	const PRODUCT_ATTRIBUTE_EXISTS = true;

	private static $types;

	/** @var int */
	private $id;
	/** @var bool */
	private $local;
	/** @var string */
	private $slug;
	/** @var string */
	private $label;
	/** @var bool */
	private $visible;
	/** @var bool */
	private $exists;
	/** @var array */
	protected $options = array();
	/** @var mixed */
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

	public function __construct($exists = false)
	{
		$this->exists = $exists;
		$this->visible = new Attribute\Field('is_visible', true);
	}

	/**
	 * @return boolean Is this attribute in the database?
	 */
	public function exists()
	{
		return $this->exists;
	}

	/**
	 * @param $exists boolean Set attribute to be in the database or not.
	 */
	public function setExists($exists)
	{
		$this->exists = $exists;
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
	 * @return bool Whether attribute has options attached.
	 */
	public function hasOptions()
	{
		return !empty($this->options);
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
		$option->setAttribute($this);
		// Ability to add multiple new options
		$id = $option->getId();
		if (empty($id)) {
			$id = hash('md5', $option->getLabel());
		}
		$this->options[$id] = $option;
	}

	/**
	 * Returns option with selected ID.
	 *
	 * @param $id int Option ID.
	 * @return Option The option.
	 */
	public function getOption($id)
	{
		if (!isset($this->options[$id])) {
			return null;
		}

		return $this->options[$id];
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

	public function hasValue()
	{
		return !empty($this->value);
	}

	/**
	 * @param boolean $visible Is attribute visible on product page?
	 */
	public function setVisible($visible)
	{
		$this->visible->setValue($visible);
	}

	/**
	 * @return bool Whether attribute is visible on product page.
	 */
	public function isVisible()
	{
		return (bool)$this->visible->getValue();
	}

	/**
	 * Returns list of custom fields to save.
	 *
	 * @return array List of custom fields to save.
	 */
	public function getFieldsToSave()
	{
		return array(
			$this->visible,
		);
	}

	/**
	 * Restores custom fields for the attribute.
	 *
	 * @param array $data Data to restore.
	 */
	public function restoreFields($data)
	{
		if (isset($data['is_visible'])) {
			$this->visible = $data['is_visible'];
		}
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
