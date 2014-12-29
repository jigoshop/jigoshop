<?php

namespace Jigoshop\Entity\Product\Attribute;

use Jigoshop\Entity\Product\Attribute;

class Multiselect extends Attribute implements Variable
{
	const TYPE = 0;

	/** @var Field */
	private $variable;

	public function __construct($exists = false)
	{
		parent::__construct($exists);
		$this->variable = new Field('is_variable', false);
		$this->value = array();
	}

	/**
	 * @param boolean $variable Set whether attribute is for variable products.
	 */
	public function setVariable($variable)
	{
		$this->variable->setValue($variable);
	}

	/**
	 * @return bool Whether attribute is used for variations.
	 */
	public function isVariable()
	{
		return (bool)$this->variable->getValue();
	}

	/**
	 * @return int Type of attribute.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * @param mixed $value New value for attribute.
	 */
	public function setValue($value)
	{
		if (is_array($value)) {
			$this->value = $value;
		} else {
			$this->value = array_filter(explode('|', $value));
		}
	}

	/**
	 * @return string Value of attribute to be printed.
	 */
	public function printValue()
	{
		$options = $this->options;
		return join(', ', array_map(function($value) use ($options){
			/** @var Option $option */
			$option = $options[$value];
			return $option->getLabel();
		}, $this->value));
	}

	public function getFieldsToSave()
	{
		$fields = parent::getFieldsToSave();
		$fields[] = $this->variable;

		return $fields;
	}

	public function restoreFields($data)
	{
		parent::restoreFields($data);

		if (isset($data['is_variable'])) {
			$this->variable = $data['is_variable'];
		}
	}
}
