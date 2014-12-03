<?php

namespace Jigoshop\Entity;

class Email implements EntityInterface
{
	/** @var int */
	private $id;
	/** @var string */
	private $title;
	/** @var string */
	private $subject;
	/** @var string */
	private $text;
	/** @var array */
	private $actions = array();

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}

	/**
	 * @return array
	 */
	public function getActions()
	{
		return $this->actions;
	}

	/**
	 * @param array $actions
	 */
	public function setActions($actions)
	{
		$this->actions = $actions;
	}

	/**
	 * @param string $action
	 */
	public function addAction($action)
	{
		$this->actions[] = $action;
	}

	/**
	 * @param string $action
	 */
	public function removeAction($action)
	{
		$key = array_search($action, $this->actions);
		if ($key !== false) {
			unset($this->actions[$key]);
		}
	}

	public function getStateToSave()
	{
		return array(
			'subject' => $this->subject,
			'actions' => $this->actions,
		);
	}

	public function restoreState(array $state)
	{
		if (isset($state['subject'])) {
			$this->subject = $state['subject'];
		}
		if (isset($state['actions'])) {
			$this->actions = $state['actions'];
		}
	}
}
