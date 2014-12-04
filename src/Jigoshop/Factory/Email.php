<?php

namespace Jigoshop\Factory;

use Jigoshop\Entity\Email as Entity;
use WPAL\Wordpress;

class Email implements EntityFactoryInterface
{
	/** @var Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
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

			// TODO: Check if actions are valid
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
			$state['actions'] = unserialize($state['actions']);

			$email->restoreState($state);
		}

		return $this->wp->applyFilters('jigoshop\find\email', $email, $state);
	}
}
