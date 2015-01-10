<?php

namespace Jigoshop;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Template;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Tax;
use WPAL\Wordpress;

class Core
{
	const VERSION = '2.0-beta10';
	const WIDGET_CACHE = 'jigoshop_widget_cache';

	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Core\Messages */
	private $messages;
	/** @var \Jigoshop\Core\Template */
	private $template;
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var array */
	private $widgets;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, Template $template, $widgets = array())
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->template = $template;
		$this->widgets = $widgets;

		$wp->wpEnqueueScript('jquery');
	}

	/**
	 * Starts Jigoshop extensions and Jigoshop itself.
	 *
	 * @param \JigoshopContainer $container
	 */
	public function run(\JigoshopContainer $container)
	{
		$wp = $this->wp;
		$wp->addFilter('template_include', array($this->template, 'process'));
		$wp->addFilter('template_redirect', array($this->template, 'redirect'));
		$wp->addAction('jigoshop\shop\content\before', array($this, 'displayCustomMessage'));
		$wp->addAction('wp_head', array($this, 'googleAnalyticsTracking'), 9990);
		// Action for limiting WordPress feed from using order notes.
		$wp->addAction('comment_feed_where', function($where){
			return $where." AND comment_type <> 'order_note'";
		});

		$container->get('jigoshop.permalinks');

		/** @var \Jigoshop\Api $api */
		$api = $container->get('jigoshop.api');
		$api->run();

		/** @var \Jigoshop\Service\TaxServiceInterface $tax */
		$tax = $container->get('jigoshop.service.tax');
		$tax->register();
		Tax::setService($tax);

		$container->get('jigoshop.emails');
		$container->get('jigoshop.web.optimization');

		$widgets = $this->widgets;
		$wp->addAction('widgets_init', function() use ($wp, $container, $widgets) {
			foreach ($widgets as $widget) {
				$class = $widget['class'];
				$wp->registerWidget($class);
				if (isset($widget['calls'])) {
					foreach ($widget['calls'] as $call) {
						list($method, $argument) = $call;
						$class::$method($container->get($argument));
					}
				}
			}
		});

		// TODO: Why this is required? :/
		$this->wp->flushRewriteRules(false);
		$this->wp->doAction('jigoshop\run', $container);
	}

	/**
	 * Displays Google Analytics tracking code in the header as the LAST item before closing </head> tag
	 */
	public function googleAnalyticsTracking()
	{
		// Do not track admin pages
		if ($this->wp->isAdmin()) {
			return;
		}

		// Do not track shop owners
		if ($this->wp->currentUserCan('manage_jigoshop')) {
			return;
		}

		$trackingId = $this->options->get('advanced.integration.google_analytics');

		if (empty($trackingId)) {
			return;
		}

		$userId = '';
		if ($this->wp->isUserLoggedIn()) {
			$userId = $this->wp->getCurrentUserId();
		}
		?>
		<script type="text/javascript">
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window, document, 'script', '//www.google-analytics.com/analytics.js', 'jigoshopGoogleAnalytics');
			jigoshopGoogleAnalytics('create', '<?php echo $trackingId; ?>', { 'userId': '<?php echo $userId; ?>' });
			jigoshopGoogleAnalytics('send', 'pageview');
		</script>
	<?php
	}

	/**
	 * Adds a custom store banner to the site.
	 */
	public function displayCustomMessage()
	{
		if ($this->options->get('general.show_message') && Frontend\Pages::isJigoshop()){
			Render::output('shop/custom_message', array(
				'message' => $this->options->get('general.message'),
			));
		}
	}
}
