<?php

namespace Jigoshop\Core;

use Jigoshop\Core\Types;
use WPAL\Wordpress;

class Permalinks
{
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;

		$wp->addAction('init', array($this, 'initFix'));
		$wp->addFilter('rewrite_rules_array', array($this, 'fix'));
	}

	public function initFix()
	{
		if ($this->options->get('permalinks.verbose')) {
			$this->wp->getRewrite()->use_verbose_page_rules = true;
		}
	}

	public function fix($rules)
	{
		$wp_rewrite = $this->wp->getRewrite();
		$permalink = $this->options->get('permalinks.product');

		// Fix the rewrite rules when the product permalink have %product_category% flag
		if (preg_match('`/(.+)(/%'.Types::PRODUCT_CATEGORY.'%)`', $permalink, $matches)) {
			foreach ($rules as $rule => $rewrite) {

				if (preg_match('`^'.preg_quote($matches[1], '`').'/\(`', $rule) && preg_match('/^(index\.php\?'.Types::PRODUCT_CATEGORY.')(?!(.*'.Types::PRODUCT.'))/', $rewrite)) {
					unset($rules[$rule]);
				}
			}
		}

		// If the shop page is used as the base, we need to enable verbose rewrite rules or sub pages will 404
		if ($this->options->get('permalinks.verbose')) {
			$page_rewrite_rules = $wp_rewrite->page_rewrite_rules();
			$rules = array_merge($page_rewrite_rules, $rules);
		}

		return $rules;
	}
}
