<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Exception;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Payment\Method;
use Jigoshop\Service\CouponServiceInterface as Service;
use Jigoshop\Service\PaymentServiceInterface;
use WPAL\Wordpress;

class Coupon
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Service */
	private $couponService;
	/** @var PaymentServiceInterface */
	private $paymentService;

	public function __construct(Wordpress $wp, Options $options, Service $couponService, PaymentServiceInterface $paymentService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->couponService = $couponService;
		$this->paymentService = $paymentService;

		$that = $this;
		$wp->addAction('wp_ajax_jigoshop.admin.coupon.find_category', array($this, 'ajaxFindCategory'), 10, 0);
		$wp->addAction('add_meta_boxes_'.Types::COUPON, function() use ($wp, $that){
			$wp->addMetaBox('jigoshop-coupon-data', __('Coupon Data', 'jigoshop'), array($that, 'box'), Types::COUPON, 'normal', 'default');
		});

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles, $scripts){
			if ($wp->getPostType() == Types::COUPON) {
				$styles->add('jigoshop.admin.coupon', JIGOSHOP_URL.'/assets/css/admin/coupon.css');
				$scripts->add('jigoshop.admin.coupon', JIGOSHOP_URL.'/assets/js/admin/coupon.js', array('jquery', 'jigoshop.helpers'));
				$scripts->localize('jigoshop.admin.coupon', 'jigoshop_admin_coupon', array(
					'ajax' => $wp->getAjaxUrl(),
				));

				$wp->doAction('jigoshop\admin\coupon\assets', $wp, $styles, $scripts);
			}
		});
	}

	public function ajaxFindCategory()
	{
		try {
			if (isset($_POST['query'])) {
				$query = trim(htmlspecialchars(strip_tags($_POST['query'])));
				$categories = array();
				if (!empty($query)) {
					$categories = $this->wp->getCategories(array(
						'taxonomy' => Types\ProductCategory::NAME,
						'name__like' => $query,
					));
				}
			} else if (isset($_POST['value'])) {
				$query = explode(',', trim(htmlspecialchars(strip_tags($_POST['value']))));
				foreach ($query as $id) {
					$categories[] = $this->wp->getTerm($id, Types\ProductCategory::NAME);
				}
			} else {
				throw new Exception(__('Neither query nor value is provided to find categories.', 'jigoshop'));
			}

			$result = array(
				'success' => true,
				'results' => array_map(function ($item){
					/** @var $item \stdClass */
					return array(
						'id' => $item->term_id,
						'text' => $item->name,
					);
				}, $categories),
			);
		} catch(Exception $e) {
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
		$coupon = $this->couponService->findForPost($post);
		$methods = array();
		foreach ($this->paymentService->getAvailable() as $method) {
			/** @var $method Method */
			$methods[$method->getId()] = $method->getName();
		}

		Render::output('admin/coupon/box', array(
			'coupon' => $coupon,
			'types' => $this->couponService->getTypes(),
			'paymentMethods' => $methods,
		));
	}
}
