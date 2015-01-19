<?php

namespace Jigoshop\Api;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Product\Downloadable;
use Jigoshop\Exception;
use Jigoshop\Frontend\Pages;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class DownloadFile implements Processable
{
	const NAME = 'download-file';

	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var Messages */
	private $messages;
	/** @var OrderServiceInterface */
	private $orderService;

	public function __construct(Wordpress $wp, Options $options, Messages $messages, OrderServiceInterface $orderService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->messages = $messages;
		$this->orderService = $orderService;
	}

	public function processResponse()
	{
		if (isset($_GET['file'])) {
			try {
				$data = explode('.', $_GET['file']);

				if (count($data) != 3) {
					throw new Exception(__('Invalid download key. Unable to download file.', 'jigoshop'));
				}

				list($key, $id, $itemKey) = $data;
				$order = $this->orderService->find((int)$id);

				/** @var $order Order */
				if ($order->getKey() !== $key) {
					throw new Exception(__('Invalid security key. Unable to download file.', 'jigoshop'));
				}

				if (!in_array($order->getStatus(), array(Order\Status::COMPLETED, Order\Status::PROCESSING))) {
					throw new Exception(__('Invalid order.', 'jigoshop'));
				}

				$item = $order->getItem($itemKey);

				if ($item === null) {
					throw new Exception(__('Product not found.', 'jigoshop'));
				}

				if ($item->getType() !== Downloadable::TYPE) {
					throw new Exception(__('Invalid file to download.', 'jigoshop'));
				}

				$downloads = $item->getMeta('downloads')->getValue();
				if (!empty($downloads) && $downloads == 0) {
					throw new Exception(__('Sorry, you have reached your download limit for this file.', 'jigoshop'));
				}

				if ($this->options->get('shopping.login_for_downloads')) {
					if (!$this->wp->isUserLoggedIn()) {
						throw new Exception(__('You have to log in before you can download a file.', 'jigoshop'));
					} else if ($order->getCustomer()->getId() != $this->wp->getCurrentUserId()) {
						throw new Exception(__('This is not your download link.', 'jigoshop'));
					}
				}

				$file = $item->getMeta('file')->getValue();
				if (!$file) {
					throw new Exception(__('File not found.', 'jigoshop'));
				}

				if (!empty($downloads)) {
					$item->getMeta('downloads')->setValue($downloads - 1);
					$this->orderService->saveItemMeta($item, $item->getMeta('downloads'));
				}

				if (!$this->wp->isMultisite()) {
					$site_url = $this->wp->siteUrl();
					$site_url = str_replace('https:', 'http:', $site_url);
					$file = str_replace($this->wp->getHelpers()->trailingslashit($site_url), ABSPATH, $file);
				} else {
					$network_url = $this->wp->networkAdminUrl();
					$network_url = str_replace('https:', 'http:', $network_url);
					$upload_dir = $this->wp->wpUploadDir();

					// Try to replace network url
					$file = str_replace($this->wp->getHelpers()->trailingslashit($network_url), ABSPATH, $file);

					// Now try to replace upload URL
					$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $file);
				}

				$file = $this->wp->applyFilters('jigoshop\downloadable\file_path', $file, $itemKey, $order);

				// See if its local or remote
				if (strstr($file, 'http:') || strstr($file, 'https:') || strstr($file, 'ftp:')) {
					$isRemote = true;
				} else {
					$isRemote = false;
					$file = realpath($file);
				}

				// Download the file
				$extension = strtolower(substr(strrchr($file, '.'), 1));

				switch ($extension) {
					case 'pdf':
						$type = 'application/pdf';
						break;
					case 'exe':
						$type = 'application/octet-stream';
						break;
					case 'zip':
						$type = 'application/zip';
						break;
					case 'doc':
						$type = 'application/msword';
						break;
					case 'xls':
						$type = 'application/vnd.ms-excel';
						break;
					case 'ppt':
						$type = 'application/vnd.ms-powerpoint';
						break;
					case 'gif':
						$type = 'image/gif';
						break;
					case 'png':
						$type = 'image/png';
						break;
					case 'jpe':
					case 'jpeg':
					case 'jpg':
						$type = 'image/jpg';
						break;
					default:
						$type = 'application/force-download';
				}

				$this->wp->doAction('jigoshop\downloadable\before_download', $file, $order);

				@session_write_close();
				@set_time_limit(0);
				@ob_end_clean();

				// required for IE, otherwise Content-Disposition may be ignored
				if (ini_get('zlib.output_compression')) {
					ini_set('zlib.output_compression', 'Off');
				}

				header('Pragma: no-cache');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Robots: none');
				header('Content-Type: '.$type);
				header('Content-Description: File Transfer');
				header('Content-Transfer-Encoding: binary');

				if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
					// workaround for IE filename bug with multiple periods / multiple dots in filename
					header('Content-Disposition: attachment; filename="'.preg_replace('/\./', '%2e', basename($file), substr_count(basename($file), '.') - 1).'";');
				} else {
					header('Content-Disposition: attachment; filename="'.basename($file).'";');
				}

				if ($isRemote) {
					header('Location: '.$file);
				} else if (file_exists($file)) {
					header('Content-Length: '.filesize($file));
					readfile($file);
				} else {
					throw new Exception(__('File not found.', 'jigoshop'));
				}
			} catch(Exception $e) {
				$this->messages->addError($e->getMessage());
				$this->wp->redirectTo($this->options->getPageId(Pages::SHOP));
			}

			exit;
		}
	}
}
