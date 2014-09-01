<?php

namespace Jigoshop\Core;

use Mockery as m;

/**
 * Cron test.
 *
 * @package Jigoshop\Core
 * @author Amadeusz Starzykiewicz
 */
class CronTest extends \TestCase
{
	/** @var m\MockInterface */
	private $wp;
	/** @var m\MockInterface */
	private $options;
	/** @var m\MockInterface */
	private $orderService;

	/** @before */
	public function prepare()
	{
		$this->wp = m::mock('WPAL\Wordpress');
		$this->options = m::mock('Jigoshop\Core\Options');
		$this->orderService = m::mock('Jigoshop\Service\OrderServiceInterface');
	}

	/** @test */
	public function constructing()
	{
		// Given
		$time = time();
		$this->wp->shouldReceive('nextScheduled')->withArgs(array('jigoshop\cron\pending_orders'))->once()->andReturn(false);
		$this->wp->shouldReceive('scheduleEvent')->withArgs(array($time, 'daily', 'jigoshop\cron\pending_orders'))->once();
		$this->wp->shouldReceive('nextScheduled')->withArgs(array('jigoshop\cron\processing_orders'))->once()->andReturn(false);
		$this->wp->shouldReceive('scheduleEvent')->withArgs(array($time, 'daily', 'jigoshop\cron\processing_orders'))->once();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\pending_orders', m::any()))->once();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\processing_orders', m::any()))->once();

		// When
		/** @noinspection PhpParamsInspection */
		new Cron($this->wp, $this->options, $this->orderService);

		// Then no errors should arise
	}

	/** @test */
	public function constructingScheduled()
	{
		// Given
		$this->wp->shouldReceive('nextScheduled')->withArgs(array(m::any()))->andReturn(true);
		$this->wp->shouldReceive('scheduleEvent')->never();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\pending_orders', m::any()))->once();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\processing_orders', m::any()))->once();

		// When
		/** @noinspection PhpParamsInspection */
		new Cron($this->wp, $this->options, $this->orderService);

		// Then no errors should arise
	}

	/** @test */
	public function updatePendingOrders()
	{
		// Given
		$this->wp->shouldReceive('nextScheduled')->withArgs(array(m::any()))->andReturn(true);
		$this->wp->shouldReceive('scheduleEvent')->never();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\pending_orders', m::any()))->once();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\processing_orders', m::any()))->once();

		$this->options->shouldReceive('get')->withArgs(array('reset_pending_orders'))->andReturn('yes');

		$order1 = m::mock('Jigoshop\Entity\Order');
		$order2 = m::mock('Jigoshop\Entity\Order');
		$this->orderService->shouldReceive('findOldPending')->once()->andReturn(array($order1, $order2));
		$order1->shouldReceive('updateStatus')->withArgs(array('on-hold', m::any()))->once();
		$order2->shouldReceive('updateStatus')->withArgs(array('on-hold', m::any()))->once();

		/** @noinspection PhpParamsInspection */
		$cron = new Cron($this->wp, $this->options, $this->orderService);

		// When
		$cron->updatePendingOrders();

		// Then no errors should arise
	}

	/** @test */
	public function updatePendingOrdersWhileDisabled()
	{
		// Given
		$this->wp->shouldReceive('nextScheduled')->withArgs(array(m::any()))->andReturn(true);
		$this->wp->shouldReceive('scheduleEvent')->never();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\pending_orders', m::any()))->once();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\processing_orders', m::any()))->once();

		$this->options->shouldReceive('get')->withArgs(array('reset_pending_orders'))->andReturn('no');

		$this->orderService->shouldReceive('findOldPending')->never();

		/** @noinspection PhpParamsInspection */
		$cron = new Cron($this->wp, $this->options, $this->orderService);

		// When
		$cron->updatePendingOrders();

		// Then no errors should arise
	}

	/** @test */
	public function completeProcessingOrders()
	{
		// Given
		$this->wp->shouldReceive('nextScheduled')->withArgs(array(m::any()))->andReturn(true);
		$this->wp->shouldReceive('scheduleEvent')->never();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\pending_orders', m::any()))->once();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\processing_orders', m::any()))->once();

		$this->options->shouldReceive('get')->withArgs(array('complete_processing_orders'))->andReturn('yes');

		$order1 = m::mock('Jigoshop\Entity\Order');
		$order2 = m::mock('Jigoshop\Entity\Order');
		$this->orderService->shouldReceive('findOldProcessing')->once()->andReturn(array($order1, $order2));
		$order1->shouldReceive('updateStatus')->withArgs(array('completed', m::any()))->once();
		$order2->shouldReceive('updateStatus')->withArgs(array('completed', m::any()))->once();

		/** @noinspection PhpParamsInspection */
		$cron = new Cron($this->wp, $this->options, $this->orderService);

		// When
		$cron->completeProcessingOrders();

		// Then no errors should arise
	}

	/** @test */
	public function completeProcessingOrdersWhileDisabled()
	{
		// Given
		$this->wp->shouldReceive('nextScheduled')->withArgs(array(m::any()))->andReturn(true);
		$this->wp->shouldReceive('scheduleEvent')->never();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\pending_orders', m::any()))->once();
		$this->wp->shouldReceive('addAction')->withArgs(array('jigoshop\cron\processing_orders', m::any()))->once();

		$this->options->shouldReceive('get')->withArgs(array('complete_processing_orders'))->andReturn('no');

		$this->orderService->shouldReceive('findOldProcessing')->never();

		/** @noinspection PhpParamsInspection */
		$cron = new Cron($this->wp, $this->options, $this->orderService);

		// When
		$cron->completeProcessingOrders();

		// Then no errors should arise
	}
}
