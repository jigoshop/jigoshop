<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\Email as Service;
use WPAL\Wordpress;

class Email
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Service */
	private $emailService;

	public function __construct(Wordpress $wp, Options $options, Service $emailService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->emailService = $emailService;

		// TODO: Ajax action for updating variables

		$that = $this;
		$wp->addAction('add_meta_boxes_'.Types::EMAIL, function() use ($wp, $that){
			$wp->addMetaBox('jigoshop-email-data', __('Email Data', 'jigoshop'), array($that, 'box'), Types::EMAIL, 'normal', 'default');
			$wp->addMetaBox('jigoshop-email-variable', __('Email Variables', 'jigoshop'), array($that, 'variablesBox'), Types::EMAIL, 'normal', 'default');
		});

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles, $scripts){
			if ($wp->getPostType() == Types::EMAIL) {
				$styles->add('jigoshop.admin', JIGOSHOP_URL.'/assets/css/admin.css');
				$scripts->add('jigoshop.helpers', JIGOSHOP_URL.'/assets/js/helpers.js');

				$wp->doAction('jigoshop\admin\email\assets', $wp, $styles, $scripts);
			}
		});
	}

	/**
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since 		1.0
	 */
	public function box()
	{
		$post = $this->wp->getGlobalPost();
		$email = $this->emailService->findForPost($post);

		$emails = array();
		foreach ($this->emailService->getMails() as $hook => $details) {
			$emails[$hook] = $details['description'];
		}

		Render::output('admin/email/box', array(
			'email' => $email,
			'emails' => $emails,
		));
	}

	/**
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since 		1.0
	 */
	public function variablesBox()
	{
		$post = $this->wp->getGlobalPost();
		$email = $this->emailService->findForPost($post);

		Render::output('admin/email/variablesBox', array(
			'email' => $email,
			'emails' => $this->emailService->getMails(),
		));
	}
}
