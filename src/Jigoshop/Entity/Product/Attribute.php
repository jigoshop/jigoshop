<?php

namespace Jigoshop\Entity\Product;

/**
 * Product's attribute.
 *
 * TODO: Implement
 *
 * @package Jigoshop\Entity\Product
 * @author Amadeusz Starzykiewicz
 */
class Attribute implements \Serializable
{
	private $name;
	private $label;
	private $value;

	/**
	 * @return string Attribute name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * String representation of object.
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		if(empty($this->name))
		{
			return '';
		}

		return serialize(array(
			'name' => $this->name,
			'label' => $this->label,
			'value' => $this->value,
		));
	}

	/**
	 * Constructs the object.
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized The string representation of the object.
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->name = $data['name'];
		$this->label = $data['label'];
		$this->value = $data['value'];
	}
}