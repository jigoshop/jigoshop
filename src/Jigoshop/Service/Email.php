<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\EntityInterface;
use Jigoshop\Factory\Email as Factory;
use WPAL\Wordpress;

/**
 * Email service.
 *
 * TODO: Add caching.
 *
 * @package Jigoshop\Service
 */
class Email implements EmailServiceInterface
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Factory */
	private $factory;
	/** @var bool */
	private $suppress = false;

	public function __construct(Wordpress $wp, Options $options, Factory $factory)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->factory = $factory;
		$wp->addAction('save_post_'.Types\Email::NAME, array($this, 'savePost'), 10);
	}

	/**
	 * Suppresses sending next email.
	 */
	public function suppressNextEmail()
	{
		$this->suppress = true;
	}

	/**
	 * Finds item specified by ID.
	 *
	 * @param $id int The ID.
	 * @return \Jigoshop\Entity\Email The item.
	 */
	public function find($id)
	{
		$post = null;

		if ($id !== null) {
			$post = $this->wp->getPost($id);
		}

		return $this->factory->fetch($post);
	}

	/**
	 * Finds item for specified WordPress post.
	 *
	 * @param $post \WP_Post WordPress post.
	 * @return \Jigoshop\Entity\Email Item found.
	 */
	public function findForPost($post)
	{
		return $this->factory->fetch($post);
	}

	/**
	 * Finds items specified using WordPress query.
	 *
	 * @param $query \WP_Query WordPress query.
	 * @return array Collection of found items.
	 */
	public function findByQuery($query)
	{
		$results = $query->get_posts();
		$emails = array();

		// TODO: Maybe it is good to optimize this to fetch all found products data at once?
		foreach ($results as $email) {
			$emails[] = $this->findForPost($email);
		}

		return $emails;
	}

	/**
	 * Saves entity to database.
	 *
	 * @param $object EntityInterface Entity to save.
	 */
	public function save(EntityInterface $object)
	{
		if (!($object instanceof \Jigoshop\Entity\Email)) {
			throw new Exception('Trying to save not an email!');
		}

		// TODO: Support for transactions!

		$fields = $object->getStateToSave();

		if (isset($fields['id']) || isset($fields['title']) || isset($fields['text'])) {
			// We do not need to save ID, title and text (content) as they are saved by WordPress itself.
			unset($fields['id'], $fields['title'], $fields['text']);
		}

		foreach ($fields as $field => $value) {
			$this->wp->updatePostMeta($object->getId(), $field, $value);
		}

		$this->addTemplate($object->getId(), $object->getActions());
		$this->wp->doAction('jigoshop\service\email\save', $object);
	}

	/**
	 * Save the email data upon post saving.
	 *
	 * @param $id int Post ID.
	 */
	public function savePost($id)
	{
		$email = $this->factory->create($id);
		$this->save($email);
	}

	/**
	 * @return array List of registered mails with accepted arguments.
	 */
	public function getMails()
	{
		return $this->factory->getActions();
	}

	/**
	 * Registers an email action.
	 *
	 * @param $action string Action name.
	 * @param $description string Email description.
	 * @param array $arguments Accepted arguments list.
	 */
	public function register($action, $description, array $arguments)
	{
		$this->factory->register($action, $description, $arguments);
	}

	/**
	 * @return array List of available actions.
	 */
	public function getAvailableActions()
	{
		return array_keys($this->factory->getActions());
	}

	/**
	 * @param $postId int Email template to add.
	 * @param $hooks array List of hooks to add to.
	 */
	public function addTemplate($postId, $hooks)
	{
		$templates = $this->options->get('emails.templates');

		if(is_array($templates)) {
			$templates = array_map(function ($template) use ($postId){
				return array_filter($template, function ($templatePostId) use ($postId){
					return $templatePostId != $postId;
				});
			}, $templates);
		}

		foreach ($hooks as $hook) {
			$templates[$hook][] = $postId;
		}

		$this->options->update('emails.templates', $templates);
	}

	/**
	 * Sends specified email to specified address.
	 *
	 * @param $hook string Email to send.
	 * @param array $args Arguments to the email.
	 * @param $to string Receiver address.
	 */
	public function send($hook, array $args = array(), $to)
	{
		if ($this->suppress) {
			$this->suppress = false;
			return;
		}

		$templates = $this->options->get('emails.templates');
		if (!$templates[$hook]) {
			return;
		}
		foreach ($templates[$hook] as $postId) {
			$post = $this->wp->getPost($postId);

			if (!empty($post) && $post->post_status == 'publish') {
				$subject = $this->wp->getPostMeta($postId, 'subject', true);
				$post->post_title = empty($subject) ? $post->post_title : $subject;
				$post = $this->filterPost($post, $args);
				$headers = array(
					'MIME-Version: 1.0',
					'Content-Type: text/html; charset=UTF-8',
					'From: "'.$this->options->get('general.emails.from').'" <'.$this->options->get('general.email').'>',
				);
				$footer = $this->options->get('general.emails.footer');
				$post->post_content = $footer ? $post->post_content.'<br/><br/>'.$footer : $post->post_content;

				$this->wp->wpMail(
					$to,
					$post->post_title,
					nl2br($post->post_content),
					$headers
				);
			}
		}
	}

	private function filterPost(\WP_Post $post, array $args)
	{
		if (empty($args)) {
			return $post;
		}
		foreach ($args as $key => $value) {
			$post->post_title = str_replace('['.$key.']', $value, $post->post_title);
			if(empty($value)){
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[else\](.*?)\[\/'.$key.'\]#si', '$2', $post->post_content);
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[\/'.$key.'\]#si', '', $post->post_content);
				$post->post_content = str_replace('['.$key.']', '', $post->post_content);
			} else {
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[value\](.*?)\[else\](.*?)\[\/'.$key.'\]#si', '$1'.'['.$key.']'.'$2', $post->post_content);
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[else\](.*?)\[\/'.$key.'\]#si', '$1', $post->post_content);
				$post->post_content = preg_replace('#\['.$key.'\](.*?)\[value\](.*?)\[\/'.$key.'\]#si', '$1'.'['.$key.']'.'$2', $post->post_content);
				$post->post_content = str_replace('['.$key.']', $value, $post->post_content);
			}
		}
		return $post;
	}
}
