<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Formatter;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\CouponServiceInterface;
use WPAL\Wordpress;

class Coupons
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Service\CouponServiceInterface */
	private $couponService;

	public function __construct(Wordpress $wp, Options $options, CouponServiceInterface $couponService, Styles $styles, Scripts $scripts)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->couponService = $couponService;

		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::COUPON), array($this, 'columns'));
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::COUPON), array($this, 'displayColumn'), 2);

		$wp->addAction('admin_enqueue_scripts', function() use ($wp, $styles, $scripts){
			if ($wp->getPostType() == Types::COUPON) {
				$wp->doAction('jigoshop\admin\coupons\assets', $wp, $styles, $scripts);
			}
		});
	}

	public function columns() {
		return $this->wp->applyFilters('jigoshop\admin\coupons\columns', array(
			'cb' => '<input type="checkbox" />',
			'title' => _x('Title', 'coupon', 'jigoshop'),
			'code' => _x('Code', 'coupon', 'jigoshop'),
			'type' => _x('Type', 'coupon', 'jigoshop'),
			'amount' => _x('Amount', 'coupon', 'jigoshop'),
			'usage_limit' => _x('Usage limit', 'coupon', 'jigoshop'),
			'usage' => _x('Used', 'coupon', 'jigoshop'),
			'from' => _x('From', 'coupon', 'jigoshop'),
			'to' => _x('To', 'coupon', 'jigoshop'),
			'is_individual' => _x('For individual use?', 'coupon', 'jigoshop'),
		));
	}

	public function displayColumn($column)
	{
		$post = $this->wp->getGlobalPost();
		if($post === null){
			return;
		}

		/** @var \Jigoshop\Entity\Coupon $coupon */
		$coupon = $this->couponService->find($post->ID);
		switch ($column) {
			case 'code':
				echo $coupon->getCode();
				break;
			case 'type':
				echo $this->couponService->getType($coupon);
				break;
			case 'amount':
				echo ProductHelper::formatNumericPrice($coupon->getAmount());
				break;
			case 'usage_limit':
				echo $coupon->getUsageLimit();
				break;
			case 'usage':
				echo $coupon->getUsage();
				break;
			case 'from':
				$from = $coupon->getFrom();
				if ($from) {
					echo Formatter::date($from->getTimestamp());
				}
				break;
			case 'to':
				$to = $coupon->getTo();
				if ($to) {
					echo Formatter::date($to->getTimestamp());
				}
				break;
			case 'is_individual':
				echo sprintf(
					'<span class="glyphicon %s" aria-hidden="true"></span> <span class="sr-only">%s</span>',
					$coupon->isIndividualUse() ? 'glyphicon-ok' : 'glyphicon-remove',
					$coupon->isIndividualUse() ? __('Yes', 'jigoshop') : __('No', 'jigoshop')
				);
				break;
		}
	}
}
