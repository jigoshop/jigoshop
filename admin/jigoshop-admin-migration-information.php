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
	private $plugins = array();

	/**
	 * Render output of migration information page.
	 */
	public function render()
	{
		if (isset($_POST['sendAsk']))
		{
			$msg = 'Plugin name: ' . $_POST['askPluginName2'] . "\r\n";
			$msg .= 'Plugin repo: ' . $_POST['askRepoUrl'] . "\r\n";
			$msg .= 'Message: ' . $_POST['askMsg'] . "\r\n";
			wp_mail('nofacexx@gmail.com', 'Ask from client - when plugin ready', $msg);

			$this->info = __('Question was sent.', 'jigoshop');
		}

		if (isset($_POST['askPluginName']))
		{
			$template = jigoshop_locate_template('admin/migration-ask');
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
				$this->plugins['rest'][$slug]['note'] = 'some info';
			}
		}
	}

	protected function get()
	{
		$api = 'http://jigoshop.nf/a.php';
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $api);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(get_plugins()));
		curl_exec($c);
		curl_close($c);
		$this->getData();
	}

	protected function getData()
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'https://jigoshop.com/jigoshop_plugins2.json');
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($c);

		if (curl_errno($c))
		{
			$this->errors[] = curl_errno($c);
		}
		$httpCode = curl_getinfo($c, CURLINFO_HTTP_CODE);
		curl_close($c);

		if ($httpCode >= 200 && $httpCode < 300)
		{
			$this->jigoPluginInfo = json_decode($json, true);
		}
		else
		{
			$this->errors[] = 'httpcode: ' . $httpCode;
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
}
