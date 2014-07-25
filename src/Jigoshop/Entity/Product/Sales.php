<?php

namespace Jigoshop\Entity\Product;

/**
 * Product sales data.
 *
 * @package Jigoshop\Entity\Product
 * @author Amadeusz Starzykiewicz
 */
class Sales implements \Serializable
{
	/** @var \DateTime */
	private $from;
	/** @var \DateTime */
	private $to;
	/** @var float */
	private $price = '';

	public function __construct()
	{
		$this->from = $this->to = new \DateTime();
	}

	/**
	 * @param \DateTime $from New start sales date.
	 */
	public function setFrom(\DateTime $from)
	{
		$this->from = $from;
	}

	/**
	 * @param float $price New price on sales.
	 */
	public function setPrice($price)
	{
		$this->price = floatval($price);
	}

	/**
	 * @param \DateTime $to New end sales date.
	 */
	public function setTo(\DateTime $to)
	{
		$this->to = $to;
	}

	/**
	 * @return \DateTime
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @return \DateTime
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * String representation of object.
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return serialize(array(
			'from' => $this->from->getTimestamp(),
			'to' => $this->to->getTimestamp(),
			'price' => $this->price,
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
		$this->from = new \DateTime($data['from']);
		$this->to = new \DateTime($data['to']);
		$this->price = floatval($data['price']);
	}
}
