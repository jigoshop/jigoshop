<?php

namespace Jigoshop\Entity\Product\Attributes;

/**
 * Product sales data.
 *
 * @package Jigoshop\Entity\Product\Attributes
 * @author Amadeusz Starzykiewicz
 */
class Sales implements \Serializable
{
	/** @var boolean */
	private $enabled = false;
	/** @var \DateTime */
	private $from;
	/** @var \DateTime */
	private $to;
	/** @var float */
	private $price = '';

	public function __construct()
	{
		$this->from = new \DateTime();
		$this->to = new \DateTime();
	}

	/**
	 * @param boolean $enabled Enable sales?
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
	}

	/**
	 * @param \DateTime $from New start sales date.
	 */
	public function setFrom(\DateTime $from)
	{
		$this->from = $from;
	}

	/**
	 * @param int $from New start sales date.
	 */
	public function setFromTime($from)
	{
		if(!is_numeric($from)){
			$from = strtotime($from);
		}
		$this->from->setTimestamp($from);
	}

	/**
	 * Sets new price discount.
	 *
	 * Can be either value or percentage (i.e. 10.00 or 10%)
	 *
	 * @param string $price New price on sales.
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @param \DateTime $to New end sales date.
	 */
	public function setTo(\DateTime $to)
	{
		$this->to = $to;
	}

	/**
	 * @param int $to New end sales date.
	 */
	public function setToTime($to)
	{
		if(!is_numeric($to)){
			$to = strtotime($to);
		}
		$this->to->setTimestamp($to);
	}

	/**
	 * @return boolean Whether the sales are enabled.
	 */
	public function isEnabled()
	{
		$time = time();
		return $this->enabled &&
			($this->getFrom()->getTimestamp() == 0 || $this->getFrom()->getTimestamp() <= $time) &&
			($this->getTo()->getTimestamp() == 0 || $this->getTo()->getTimestamp() >= $time);
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
	 * @return string
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
			'enabled' => $this->enabled && !empty($this->price),
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
		$this->enabled = (bool)$data['enabled'];
		$this->from = new \DateTime();
		$this->from->setTimestamp((int)$data['from']);
		$this->to = new \DateTime();
		$this->to->setTimestamp((int)$data['to']);
		$this->price = $data['price'];
	}
}
