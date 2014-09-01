<?php

namespace Jigoshop\Entity;

/**
 * Entity interface.
 *
 * @package Jigoshop\Entity
 * @author Amadeusz Starzykiewicz
 */
interface EntityInterface
{
	/**
	 * @return int Entity ID.
	 */
	public function getId();

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave();

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state);
}
