<?php

namespace Jigoshop\Frontend\Page\Account;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Frontend\Page\PageInterface;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CustomerServiceInterface;
use WPAL\Wordpress;

class EditAddress implements PageInterface
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
		$styles->add('jigoshop.user.account.edit_address', JIGOSHOP_URL.'/assets/css/user/account/edit_address.css');
		$styles->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/css/vendors.min.css');
		$scripts->add('jigoshop.vendors', JIGOSHOP_URL.'/assets/js/vendors.min.js');
//		$scripts->add('jigoshop.account', JIGOSHOP_URL.'/assets/js/account.js', array('jquery', 'jigoshop.vendors'));
		$this->wp->doAction('jigoshop\account\assets', $wp, $styles, $scripts);
	}


	public function action()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'save_address') {
			$customer = $this->customerService->getCurrent();
			switch ($this->wp->getQueryParameter('edit-address')) {
				case 'billing':
					$address = $customer->getBillingAddress();
					break;
				case 'shipping':
					$address = $customer->getShippingAddress();
					break;
			}

			$errors = array();
			if ($address instanceof CompanyAddress) {
				$address->setCompany(trim(htmlspecialchars(strip_tags($_POST['address']['company']))));
				$address->setVatNumber(trim(htmlspecialchars(strip_tags($_POST['address']['vat_number']))));
			}

			$address->setPhone(trim(htmlspecialchars(strip_tags($_POST['address']['phone']))));
			$address->setFirstName(trim(htmlspecialchars(strip_tags($_POST['address']['first_name']))));
			$address->setLastName(trim(htmlspecialchars(strip_tags($_POST['address']['last_name']))));
			$address->setAddress(trim(htmlspecialchars(strip_tags($_POST['address']['address']))));
			$address->setCity(trim(htmlspecialchars(strip_tags($_POST['address']['city']))));
			// TODO: Zip validation
			$address->setPostcode(trim(htmlspecialchars(strip_tags($_POST['address']['postcode']))));

			$country = trim(htmlspecialchars(strip_tags($_POST['address']['country'])));
			if (!Country::exists($country)) {
				$errors[] = sprintf(__('Country "%s" does not exists.', 'jigoshop'), $country);
			} else {
				$address->setCountry($country);
			}

			$state = trim(htmlspecialchars(strip_tags($_POST['address']['state'])));
			if (Country::hasStates($address->getCountry()) && !Country::hasState($address->getCountry(), $state)) {
				$errors[] = sprintf(__('Country "%s" does not have state "%s".', 'jigoshop'), Country::getName($address->getCountry()), $state);
			} else {
				$address->setState($state);
			}

			$email = trim(htmlspecialchars(strip_tags($_POST['address']['email'])));
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			if ($email === false) {
				$errors[] = __('Invalid email address', 'jigoshop');
			} else {
				$address->setEmail($email);
			}

			if (!empty($errors)) {
				$this->messages->addError(join('<br/>', $errors), false);
			} else {
				$this->customerService->save($customer);
				$this->messages->addNotice(__('Address saved.', 'jigoshop'));
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
		switch ($this->wp->getQueryParameter('edit-address')) {
			case 'billing':
				$address = $customer->getBillingAddress();
				break;
			case 'shipping':
				$address = $customer->getShippingAddress();
				break;
		}
		return Render::get('user/account/edit_address', array(
			'messages' => $this->messages,
			'customer' => $customer,
			'address' => $address,
			'myAccountUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::ACCOUNT)),
		));
	}
}
