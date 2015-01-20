<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin\Migration\Tool;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

/**
 * Migration tool page.
 *
 * @package Jigoshop\Admin
 */
class Migration implements PageInterface
{
	const NAME = 'jigoshop_migration';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Messages */
	private $messages;
	private $tools = array();

	public function __construct(Wordpress $wp, Options $options, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;

		$wp->addAction('current_screen', array($this, 'action'));
		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php', 'options.php'))) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if ($screen->base != 'jigoshop_page_'.Migration::NAME) {
				return;
			}

			// TODO: Migration styles
			Styles::add('jigoshop.admin.migration', JIGOSHOP_URL.'/assets/css/admin/settings.css');
		});
	}

	/**
	 * @param $tool Tool Migration tool to add.
	 */
	public function addTool(Tool $tool) {
		if (!in_array($tool, $this->tools)) {
			$this->tools[$tool->getId()] = $tool;
		}
	}

	/** @return string Title of page. */
	public function getTitle()
	{
		return __('Migration tool', 'jigoshop');
	}

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return Dashboard::NAME;
	}

	/** @return string Required capability to view the page. */
	public function getCapability()
	{
		return 'manage_jigoshop';
	}

	/** @return string Menu slug. */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/**
	 * Action method to run tools.
	 */
	public function action()
	{
		if (!isset($_GET['tool'])) {
			return;
		}

		$id = trim(htmlspecialchars(strip_tags($_GET['tool'])));
		if (isset($this->tools[$id])) {
			/** @var Tool $tool */
			$tool = $this->tools[$id];
			$this->wp->doAction('jigoshop\migration\before', $tool);
			$tool->migrate();
			$this->messages->addNotice(__('Migration complete', 'jigoshop'));
			$this->wp->wpRedirect($this->wp->adminUrl('admin.php?page='.self::NAME));
		}
	}

	/** Displays the page. */
	public function display()
	{
		Render::output('admin/migration', array(
			'messages' => $this->messages,
			'tools' => $this->tools,
		));
	}
}
