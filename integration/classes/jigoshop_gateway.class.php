<?php

class jigoshop_gateway extends jigoshop_payment_gateway
{
	/** @var \Jigoshop\Payment\Method */
	private $__gateway;

	public function __construct(\Jigoshop\Payment\Method $gateway)
	{
		$this->__gateway = $gateway;
	}

	public function is_available()
	{
		return $this->__gateway->isEnabled();
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param $order_id int Order ID to process.
	 * @return array
	 */
	public function process_payment($order_id)
	{
		/** @var \Jigoshop\Entity\Order $order */
		$order = \Jigoshop\Integration::getOrderService()->find($order_id);
		$url = $this->__gateway->process($order);

		return array(
			'success' => true,
			'redirect' => $url,
		);
	}
}
