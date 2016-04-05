<?php
namespace Inpsyde\SearchReplace\Tests\Service;

use Inpsyde\SearchReplace\Service;

/**
 * Class ExecutionTime - set the service time out up to 0
 *
 * @package Inpsyde\SearchReplace\Service
 */
class MaxExecutionTimeTest extends \PHPUnit_Framework_TestCase{

	public $testee;

	public function setUp() {

		parent::setUp();

	}

	public function test_set(){

		$mock = $this->getMockBuilder('Service\MaxExecutionTime')
			 ->setMethods(array('set_time_limit'))
		     ->getMock();

		$mock->expects( $this->once() )
			->method('set_time_limit')
			->with()
			->with( $this->equalTo( NULL ) );

		$testee = new Service\MaxExecutionTime;
		$testee->set( $mock );


	}

	public function test_restore(){

    }

}