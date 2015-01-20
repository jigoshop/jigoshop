<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Exception;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Service\EmailServiceInterface as Service;
use WPAL\Wordpress;

class Email
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Service */
	private $emailService;

	public function __construct(Wordpress $wp, Options $options, Service $emailService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->emailService = $emailService;

		add_action('wp_ajax_jigoshop.admin.email.update_variable_list', array($this, 'ajaxVariables'));

		$that = $this;
		$wp->addAction('add_meta_boxes_'.Types::EMAIL, function() use ($wp, $that){
			$wp->addMetaBox('jigoshop-email-data', __('Email Data', 'jigoshop'), array($that, 'box'), Types::EMAIL, 'normal', 'default');
			$wp->addMetaBox('jigoshop-email-variable', __('Email Variables', 'jigoshop'), array($that, 'variablesBox'), Types::EMAIL, 'normal', 'default');
		});

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			if ($wp->getPostType() == Types::EMAIL) {
				Scripts::add('jigoshop.admin.email', JIGOSHOP_URL.'/assets/js/admin/email.js', array(
					'jquery',
					'jigoshop.helpers'
				));
				Scripts::localize('jigoshop.admin.email', 'jigoshop_admin_email', array(
					'ajax' => $wp->getAjaxUrl(),
				));

				$wp->doAction('jigoshop\admin\email\assets', $wp);
			}
		});
	}

	/**
	 * Displays the product data box, tabbed, with several panels covering price, stock etc
	 *
	 * @since 		1.0
	 */
	public function ajaxVariables()
	{
		try {
			/** @var \Jigoshop\Entity\Email $email */
			$email = $this->emailService->find((int)$_POST['email']);

			if ($email->getId() === null) {
				throw new Exception(__('Email not found.', 'jigoshop'));
			}

			$availableActions = $this->emailService->getAvailableActions();
			$actions = array_intersect($_POST['actions'], $availableActions);
			$email->setActions($actions);

			$result = array(
				'success' => true,
				'html' => Render::get('admin/email/variables', array(
					'email' => $email,
					'emails' => $this->emailService->getMails(),
				))
			);
		} catch (Exception $e) {
			$result = array(
				'success' => false,
				'error' => $e->getMessage(),
			);
		}

		echo json_encode($result);
		exit;
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
