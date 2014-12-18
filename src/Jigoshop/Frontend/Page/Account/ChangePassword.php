<?php

namespace Jigoshop\Frontend\Page\Account;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Frontend\Page\PageInterface;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CustomerServiceInterface;
use WPAL\Wordpress;

class ChangePassword implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages  */
	private $messages;
	/** @var CustomerServiceInterface */
	private $customerService;

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService, Messages $messages, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->messages = $messages;

		$styles->add('jigoshop.user.account', JIGOSHOP_URL.'/assets/css/user/account.css');
		$styles->add('jigoshop.user.account.change_password', JIGOSHOP_URL.'/assets/css/user/account/change_password.css');
		$styles->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
		$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
		$this->wp->doAction('jigoshop\account\assets', $wp, $styles, $scripts);
	}


	public function action()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'change_password') {
			$errors = array();
			$user = $this->wp->wpGetCurrentUser();

			/** @noinspection PhpUndefinedFieldInspection */
			if (!$this->wp->wpCheckPassword($_POST['password'], $user->user_pass, $user->ID)) {
				$errors[] = __('Current password is invalid.', 'jigoshop');
			}

			if (empty($_POST['new-password'])) {
				$errors[] = __('Please enter new password.', 'jigoshop');
			}	else if ($_POST['new-password'] != $_POST['new-password-2']) {
				$errors[] = __('Passwords do not match.', 'jigoshop');
			}

			if (!empty($errors)) {
				$this->messages->addError(join('<br/>', $errors), false);
			} else {
				$this->wp->wpUpdateUser(array('ID' => $user->ID, 'user_pass' => $_POST['new-password']));
				$this->messages->addNotice(__('Password changed.', 'jigoshop'));
				$this->wp->redirectTo($this->options->getPageId(Pages::ACCOUNT));
			}
		}
	}

	public function render()
	{
		if (!$this->wp->isUserLoggedIn()) {
			return Render::get('user/login', array());
		}

		$customer = $this->customerService->getCurrent();
		return Render::get('user/account/change_password', array(
			'messages' => $this->messages,
			'customer' => $customer,
			'myAccountUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::ACCOUNT)),
		));
	}
}
