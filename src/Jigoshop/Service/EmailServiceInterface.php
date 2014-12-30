<?php
namespace Jigoshop\Service;

/**
 * Email service.
 *
 * @package Jigoshop\Service
 */
interface EmailServiceInterface extends ServiceInterface
{
	/**
	 * Suppresses sending next email.
	 */
	public function suppressNextEmail();

	/**
	 * @return array List of registered mails with accepted arguments.
	 */
	public function getMails();

	/**
	 * Registers an email action.
	 *
	 * @param $action string Action name.
	 * @param $description string Email description.
	 * @param array $arguments Accepted arguments list.
	 */
	public function register($action, $description, array $arguments);

	/**
	 * @return array List of available actions.
	 */
	public function getAvailableActions();

	/**
	 * @param $postId int Email template to add.
	 * @param $hooks array List of hooks to add to.
	 */
	public function addTemplate($postId, $hooks);

	/**
	 * Sends specified email to specified address.
	 *
	 * @param $hook string Email to send.
	 * @param array $args Arguments to the email.
	 * @param $to string Receiver address.
	 */
	public function send($hook, array $args = array(), $to);
}
