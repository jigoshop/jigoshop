<?php

namespace Jigoshop\Core\Types\Product;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Pages;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Downloadable as Entity;
use Jigoshop\Exception;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\OrderServiceInterface;
use WPAL\Wordpress;

class Downloadable implements Type
{
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

	/**
	 * Returns identifier for the type.
	 *
	 * @return string Type identifier.
	 */
	public function getId()
	{
		return Entity::TYPE;
	}

	/**
	 * Returns human-readable name for the type.
	 *
	 * @return string Type name.
	 */
	public function getName()
	{
		return __('Downloadable', 'jigoshop');
	}

	/**
	 * Returns class name to use as type entity.
	 * This class MUST extend {@code \Jigoshop\Entity\Product}!
	 *
	 * @return string Fully qualified class name.
	 */
	public function getClass()
	{
		return '\Jigoshop\Entity\Product\Downloadable';
	}

	/**
	 * Initializes product type.
	 *
	 * @param Wordpress $wp WordPress Abstraction Layer
	 * @param array $enabledTypes List of all available types.
	 */
	public function initialize(Wordpress $wp, array $enabledTypes)
	{
		$wp->addFilter('jigoshop\cart\add', array($this, 'addToCart'), 10, 2);
		$wp->addFilter('jigoshop\emails\order_item', array($this, 'emailLink'), 10, 3);
		$wp->addAction('template_redirect', array($this, 'downloadFile'), 10, 0);

		$wp->addAction('jigoshop\admin\product\assets', array($this, 'addAssets'), 10, 3);
		$wp->addFilter('jigoshop\admin\product\menu', array($this, 'addProductMenu'));
		$wp->addFilter('jigoshop\admin\product\tabs', array($this, 'addProductTab'), 10, 2);
	}

	/**
	 * @param $value
	 * @param $product
	 * @return null
	 */
	public function addToCart($value, $product)
	{
		if ($product instanceof Entity) {
			$item = new Item();
			$item->setName($product->getName());
			$item->setPrice($product->getPrice());
			$item->setQuantity(1);
			$item->setProduct($product);

			$meta = new Item\Meta('file', $product->getUrl());
			$item->addMeta($meta);
			$meta = new Item\Meta('downloads', $product->getLimit());
			$item->addMeta($meta);

			return $item;
		}

		return $value;
	}

	/**
	 * @param $result string Current email message.
	 * @param $item Order\Item Item to display.
	 * @param $order Order Order being displayed.
	 * @return string
	 */
	public function emailLink($result, $item, $order)
	{
		$product = $item->getProduct();
		if ($product instanceof Product\Downloadable) {
			$url = Api::getEndpointUrl('download-file', $order->getKey().'.'.$order->getId().'.'.$item->getKey());

			$result .= PHP_EOL.__('Your download link for this file is:', 'jigoshop');
			$result .= PHP_EOL.' - '.$url;
		}

		return $result;
	}

	public function downloadFile()
	{
		$data = $this->wp->getQueryParameter('download-file', null);
		if ($data !== null) {
			try {
				$data = explode('.', $data);

				if (count($data) != 3) {
					throw new Exception(__('Invalid download key. Unable to download file.', 'jigoshop'));
				}

				list($key, $id, $itemKey) = $data;
				$order = $this->orderService->find((int)$id);

				if ($order->getKey() !== $key) {
					throw new Exception(__('Invalid security key. Unable to download file.', 'jigoshop'));
				}

				if (!in_array($order->getStatus(), array(Order\Status::COMPLETED, Order\Status::PROCESSING))) {
					throw new Exception(__('Invalid order.', 'jigoshop'));
				}

				$item = $order->getItem($itemKey);
				if ($item->getType() !== Product\Downloadable::TYPE) {
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

	/**
	 * @param Wordpress $wp
	 * @param Styles $styles
	 * @param Scripts $scripts
	 */
	public function addAssets(Wordpress $wp, Styles $styles, Scripts $scripts)
	{
//		$scripts->add('jigoshop.admin.product.downloadable', JIGOSHOP_URL.'/assets/js/admin/product/downloadable.js', array('jquery'));
	}

	/**
	 * Updates product menu.
	 *
	 * @param $menu array
	 * @return array
	 */
	public function addProductMenu($menu)
	{
		$menu['downloads'] = array('label' => __('Downloads', 'jigoshop'), 'visible' => array(Product\Downloadable::TYPE));
		$menu['advanced']['visible'][] = Product\Downloadable::TYPE;
		$menu['stock']['visible'][] = Product\Downloadable::TYPE;
		$menu['sales']['visible'][] = Product\Downloadable::TYPE;
		return $menu;
	}

	/**
	 * Updates product tabs.
	 *
	 * @param $tabs array
	 * @param $product Product
	 * @return array
	 */
	public function addProductTab($tabs, $product)
	{
		$tabs['downloads'] = array(
			'product' => $product,
		);
		return $tabs;
	}
}
