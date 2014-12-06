<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Email as Entity;
use WPAL\Wordpress;

/**
 * Email factory.
 *
 * @package Jigoshop\Factory
 */
class Email implements EntityFactoryInterface
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;
	}

	/**
	 * Creates new product properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 * @return \Jigoshop\Entity\Product
	 */
	public function create($id)
	{
		$email = new Entity();
		$email->setId($id);

		if (!empty($_POST)) {
			$helpers = $this->wp->getHelpers();
			$email->setTitle($helpers->sanitizeTitle($_POST['post_title']));
			$email->setText($helpers->parsePostBody($_POST['content']));

			// TODO: Replace emails.templates with proper email fetching so that available actions will be always good
//			$availableActions = $this->getAvailableActions();
//			$_POST['jigoshop_email']['actions'] = array_intersect($_POST['jigoshop_email']['actions'], $availableActions);
			$email->restoreState($_POST['jigoshop_email']);
		}

		return $email;
	}

	/**
	 * Fetches product from database.
	 *
	 * @param $post \WP_Post Post to fetch product for.
	 * @return \Jigoshop\Entity\Product
	 */
	public function fetch($post)
	{
		$email = new Entity();
		$state = array();

		if($post){
			$state = array_map(function ($item){
				return $item[0];
			}, $this->wp->getPostMeta($post->ID));

			$email->setId($post->ID);
			$email->setTitle($post->post_title);
			$email->setText($post->post_content);
			$state['actions'] = unserialize($state['actions']);

			$email->restoreState($state);
		}

		return $this->wp->applyFilters('jigoshop\find\email', $email, $state);
	}

	/**
	 * @return array List of available actions.
	 */
	public function getAvailableActions()
	{
		// TODO: Replace emails.templates with proper email fetching so that available actions will be always good
		$templates = $this->options->get('emails.templates', array());
		return array_keys($templates);
	}
}
