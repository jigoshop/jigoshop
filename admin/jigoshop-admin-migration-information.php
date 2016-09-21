<?php
if (!defined('ABSPATH'))
{
	exit;
}

class JigoshopMigrationInformation
{
	private $errors = array();
	private $info = '';
	private $jigoPluginInfo = array();
	private $pluginsRepoUrl = array();
	private $plugins = ['jigoshop' => [], 'rest' => []];

	/**
	 * Render output of migration information page.
	 */
	public function render()
	{
		if (isset($_POST['migration_terms_accept']))
		{
			update_option('jigoshop_accept_migration_terms', 'yes');
		}

		if (!get_option('jigoshop_accept_migration_terms'))
		{
			$template = jigoshop_locate_template('admin/migration-terms');
			/** @noinspection PhpIncludeInspection */
			include($template);

			return;
		}

		if (isset($_POST['sendAsk']))
		{
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$headers[] = 'Reply-To: ' . esc_attr($_POST['askEmail']);

			$msg = 'Plugin name: ' . $_POST['askPluginName2'] . "\r\n" . '<br />';
			$msg .= 'Plugin repo: ' . $_POST['askRepoUrl'] . "\r\n" . '<br />';
			$msg .= 'Client e-mail: ' . $_POST['askEmail'] . "\r\n" . '<br />';
			$msg .= 'Message: ' . $_POST['askMsg'] . "\r\n" . '<br />';
			if (wp_mail('Martin.Czyz@jigoshop.com', 'Query from client - plugin availability', $msg, $headers))
			{
				$this->info = __('Question was sent.', 'jigoshop');
			}
			else
			{
				$this->errors[] = __('The message has not been sent due to misconfiguration of the server\'s SMTP settings. Check your server settings.', 'jigoshop');
			}
		}

		if (isset($_POST['sendFeedback']))
		{
			$headers[] = 'Content-Type: text/html; charset=UTF-8';

			$msg = 'Plugin name: ' . $_POST['feedbackPluginName'] . "\r\n" . '<br />';
			$msg .= 'Plugin slug: ' . $_POST['feedbackSlug'] . "\r\n" . '<br />';
			$msg .= 'Message: ' . $_POST['askMsg'] . "\r\n" . '<br />';
			if (wp_mail('Martin.Czyz@jigoshop.com', 'Report - Jigoshop Plugin!', $msg, $headers))
			{
				$this->info = __('Message was sent.', 'jigoshop');
			}
			else
			{
				$this->errors[] = __('The message has not been sent due to misconfiguration of the server\'s SMTP settings. Check your server settings.', 'jigoshop');
			}
		}

		if (isset($_POST['askPluginName']))
		{
			$template = jigoshop_locate_template('admin/migration-ask');
			/** @noinspection PhpIncludeInspection */
			include($template);

			return;
		}

		if (isset($_POST['prepareFeedback']))
		{
			$template = jigoshop_locate_template('admin/migration-feedback');
			/** @noinspection PhpIncludeInspection */
			include($template);

			return;
		}

		$this->checkRequirements();
		$this->getInformation();

		if (count($this->errors) > 0)
		{
			$this->showErrors();

			return;
		}

		$isAllReady = $this->isAllReady();

		$info = $this->info;
		extract($this->plugins);
		$template = jigoshop_locate_template('admin/migration-information');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	private function checkRequirements()
	{
		if (!function_exists('curl_version'))
		{
			$this->errors[] = __('Curl support is not enabled on this server. It is necessary to enable it in order to check your plugins compatibility with Jigoshop 2.', 'jigoshop');
		}
	}

	private function showErrors()
	{
		$errors = join('</li><li>', $this->errors);
		$errors = __('<b>Errors found:</b>', 'jigoshop') . '<br /><ul style="list-style-type:circle; margin-left: 16px;"><li>' . $errors . '</li></ul>';

		$template = jigoshop_locate_template('admin/migration-information');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	private function getInformation()
	{
		$this->get();

		if (count($this->errors) > 0)
		{
			return;
		}

		$sitePlugins = get_plugins();

		$this->pluginRepoUrl();

		foreach ($sitePlugins as $slug => $plugin)
		{
			if (strpos($slug, '/jigoshop.php') !== false)
			{
				continue;
			}
			if ($pluginData = $this->checkJigoPlugin($slug))
			{
				$this->plugins['jigoshop'][$slug]['name'] = $plugin['Name'];
				$this->plugins['jigoshop'][$slug]['js2Compatible'] = $this->jigoPluginInfo[$pluginData]['js2_compatible'];
				$this->plugins['jigoshop'][$slug]['repoUrl'] = $this->jigoPluginInfo[$pluginData]['repo_url'];
				$this->plugins['jigoshop'][$slug]['note'] = isset($this->jigoPluginInfo[$pluginData]['note']) ? $this->jigoPluginInfo[$pluginData]['note'] : '';
			}
			else
			{
				$this->plugins['rest'][$slug]['name'] = $plugin['Name'];
				$this->plugins['rest'][$slug]['slug'] = $slug;
			}
		}
	}

	private function get()
	{
		if (!get_option('jigoshop_check_plugins'))
		{
			$api = 'https://www.jigoshop.com/wp-content/plugins/jigoshop-plugins-statistic/listening.php';
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $api);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($this->getInfo()));
			@curl_exec($c);
			curl_close($c);
			update_option('jigoshop_check_plugins', 1);
		}
		$this->getData();
	}

	private function getInfo()
	{
		$info = array();
		foreach (get_plugins() as $k => $v)
		{
			$info[$k]['name'] = $v['Name'];
			$info[$k]['pluginUrl'] = $v['PluginURI'];
		}

		return $info;
	}

	private function getData()
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'https://www.jigoshop.com/wp-content/plugins/jigoshop-plugins-statistic/jigoshopPlugins.php');
//		curl_setopt($c, CURLOPT_URL, 'http://www.martindev.pl/js1/wp-content/plugins/jigoshop-plugins-statistic/jigoshopPlugins.php');
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

		$json = curl_exec($c);

		if (curl_errno($c))
		{
			$this->errors[] = 'We weren\'t able to receive the information about our plugins\' newest available release from a remote server. Please contact our support team. (Error: ' . curl_errno($c) . ')';
		}
		$httpCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);

		if ($httpCode >= 200 && $httpCode < 300)
		{
			$this->jigoPluginInfo = json_decode($json, true);

			if (count($this->jigoPluginInfo) < 1)
			{
				$this->errors[] = 'We weren\'t able to receive the information about our plugins\' newest available release from a remote server. Please contact our support team. (No data feed)';
			}
		}
		else
		{
			$this->errors[] = 'We weren\'t able to receive the information about our plugins\' newest available release from a remote server. Please contact our support team. (httpcode: ' . $httpCode . ')';
		}
	}

	private function pluginRepoUrl()
	{
		$w = array();
		foreach ($this->jigoPluginInfo as $k => $v)
		{
			$w[$k] = $v['repo_url'];
		}

		$this->pluginsRepoUrl = $w;
	}

	/**
	 * @param $slug
	 *
	 * @return mixed
	 */
	private function checkJigoPlugin($slug)
	{
		foreach ($this->pluginsRepoUrl as $k => $v)
		{
			if (!empty($v))
			{
				if (strpos($slug, $v) !== false)
				{
					return $k;
				}
			}
		}

		return false;
	}

	public function isAllReady()
	{
		$iAll = count($this->plugins['jigoshop']);

		$iStable = 0;

		foreach ($this->plugins['jigoshop'] as $plugin)
		{
			if ($plugin['js2Compatible'] == 'Yes')
			{
				$iStable++;
			}
		};

		if ($iStable == $iAll)
		{
			return true;
		}

		return false;
	}
}
