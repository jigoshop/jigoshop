<?php
if (!defined('ABSPATH'))
{
	exit;
}

class JigoshopMigrationInformation
{
	private $errors = array();
	private $jigoPluginInfo = array();

	/**
	 * Render output of migration information page.
	 */
	public function render()
	{
		$this->checkRequirements();
		$this->getInformation();

		if (count($this->errors) > 0)
		{
			$this->showErrors();

			return;
		}

		$template = jigoshop_locate_template('admin/migration-information');
		/** @noinspection PhpIncludeInspection */
		include($template);
	}

	private function checkRequirements()
	{
		if (!function_exists('curl_version'))
		{
			$this->errors[] = __('Curl support is not enabled on this server. It is necessary to enable it in order to check your plugins compatibility with Jigoshop 2.', 'jigoshop');

			return false;
		}

		return true;
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

		if(count($this->errors) > 0)
		{
			return;
		}

		$plugins = get_plugins();

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
	}

	protected function getData()
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, 'https://www.jigoshop.com/jigoshop_plugins.json');
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
}
