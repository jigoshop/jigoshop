<?php

namespace Jigoshop\Core;

/**
 * Cron test.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class CronTest extends \PHPUnit_Framework_TestCase
{
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $wp;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $options;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	private $orderService;

	public function setUp()
	{
		$this->wp = $this->getMock('\\WPAL\\Wordpress');
		$this->options = $this->getMockBuilder('\\Jigoshop\\Core\\Options')->disableOriginalConstructor()->getMock();
		$this->orderService = $this->getMock('\\Jigoshop\\Service\\OrderServiceInterface');
	}

	public function testConstructing()
	{
		// Given
		$time = time();
		$this->wp->expects($this->at(0))
			->method('nextScheduled')
			->with($this->equalTo('jigoshop\\cron\\pending_orders'))
			->will($this->returnValue(false));
		$this->wp->expects($this->at(1))
			->method('scheduleEvent')
			->with($this->equalTo($time), $this->equalTo('daily'), $this->equalTo('jigoshop\\cron\\pending_orders'));
		$this->wp->expects($this->at(2))
			->method('nextScheduled')
			->with($this->equalTo('jigoshop\\cron\\processing_orders'))
			->will($this->returnValue(false));
		$this->wp->expects($this->at(3))
			->method('scheduleEvent')
			->with($this->equalTo($time), $this->equalTo('daily'), $this->equalTo('jigoshop\\cron\\processing_orders'));

		$this->wp->expects($this->at(4))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\pending_orders'), $this->anything());
		$this->wp->expects($this->at(5))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\processing_orders'), $this->anything());


		// When
		/** @noinspection PhpParamsInspection */
		new Cron($this->wp, $this->options, $this->orderService);

		// Then no errors should arise
	}

	public function testConstructingScheduled()
	{
		// Given
		$this->wp->expects($this->any())
			->method('nextScheduled')
			->with($this->anything())
			->will($this->returnValue(true));
		$this->wp->expects($this->never())
			->method('scheduleEvent');

		$this->wp->expects($this->at(2))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\pending_orders'), $this->anything());
		$this->wp->expects($this->at(3))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\processing_orders'), $this->anything());


		// When
		/** @noinspection PhpParamsInspection */
		new Cron($this->wp, $this->options, $this->orderService);

		// Then no errors should arise
	}

	public function testUpdatePendingOrders()
	{
		// Given
		$this->wp->expects($this->any())
			->method('nextScheduled')
			->with($this->anything())
			->will($this->returnValue(true));
		$this->wp->expects($this->never())
			->method('scheduleEvent');

		$this->wp->expects($this->at(2))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\pending_orders'), $this->anything());
		$this->wp->expects($this->at(3))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\processing_orders'), $this->anything());

		$this->options->expects($this->once())
			->method('get')
			->with($this->equalTo('reset_pending_orders'))
			->will($this->returnValue('yes'));

		$order1 = $this->getMock('\\Jigoshop\\Entity\\Order');
		$order2 = $this->getMock('\\Jigoshop\\Entity\\Order');
		$this->orderService->expects($this->once())
			->method('findOldPending')
			->will($this->returnValue(array($order1, $order2)));
		$order1->expects($this->once())
			->method('updateStatus')
			->with($this->equalTo('on-hold'), $this->anything());
		$order2->expects($this->once())
			->method('updateStatus')
			->with($this->equalTo('on-hold'), $this->anything());

		/** @noinspection PhpParamsInspection */
		$cron = new Cron($this->wp, $this->options, $this->orderService);


		// When
		$cron->updatePendingOrders();

		// Then no errors should arise
	}

	public function testUpdatePendingOrdersWhileDisabled()
	{
		// Given
		$this->wp->expects($this->any())
			->method('nextScheduled')
			->with($this->anything())
			->will($this->returnValue(true));
		$this->wp->expects($this->never())
			->method('scheduleEvent');

		$this->wp->expects($this->at(2))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\pending_orders'), $this->anything());
		$this->wp->expects($this->at(3))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\processing_orders'), $this->anything());

		$this->options->expects($this->once())
			->method('get')
			->with($this->equalTo('reset_pending_orders'))
			->will($this->returnValue('no'));

		$this->orderService->expects($this->never())
			->method('findOldPending');

		/** @noinspection PhpParamsInspection */
		$cron = new Cron($this->wp, $this->options, $this->orderService);


		// When
		$cron->updatePendingOrders();

		// Then no errors should arise
	}

	public function testCompleteProcessingOrders()
	{
		// Given
		$this->wp->expects($this->any())
			->method('nextScheduled')
			->with($this->anything())
			->will($this->returnValue(true));
		$this->wp->expects($this->never())
			->method('scheduleEvent');

		$this->wp->expects($this->at(2))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\pending_orders'), $this->anything());
		$this->wp->expects($this->at(3))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\processing_orders'), $this->anything());

		$this->options->expects($this->once())
			->method('get')
			->with($this->equalTo('complete_processing_orders'))
			->will($this->returnValue('yes'));

		$order1 = $this->getMock('\\Jigoshop\\Entity\\Order');
		$order2 = $this->getMock('\\Jigoshop\\Entity\\Order');
		$this->orderService->expects($this->once())
			->method('findOldProcessing')
			->will($this->returnValue(array($order1, $order2)));
		$order1->expects($this->once())
			->method('updateStatus')
			->with($this->equalTo('completed'), $this->anything());
		$order2->expects($this->once())
			->method('updateStatus')
			->with($this->equalTo('completed'), $this->anything());

		/** @noinspection PhpParamsInspection */
		$cron = new Cron($this->wp, $this->options, $this->orderService);


		// When
		$cron->completeProcessingOrders();

		// Then no errors should arise
	}

	public function testCompleteProcessingOrdersWhileDisabled()
	{
		// Given
		$this->wp->expects($this->any())
			->method('nextScheduled')
			->with($this->anything())
			->will($this->returnValue(true));
		$this->wp->expects($this->never())
			->method('scheduleEvent');

		$this->wp->expects($this->at(2))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\pending_orders'), $this->anything());
		$this->wp->expects($this->at(3))
			->method('addAction')
			->with($this->equalTo('jigoshop\\cron\\processing_orders'), $this->anything());

		$this->options->expects($this->once())
			->method('get')
			->with($this->equalTo('complete_processing_orders'))
			->will($this->returnValue('no'));

		$this->orderService->expects($this->never())
			->method('findOldProcessing');

		/** @noinspection PhpParamsInspection */
		$cron = new Cron($this->wp, $this->options, $this->orderService);


		// When
		$cron->completeProcessingOrders();

		// Then no errors should arise
	}
}