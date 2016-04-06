<?php
namespace Inpsyde\SearchReplace\Tests\Service;

use Brain;
use Inpsyde\SearchReplace\Service;
use MonkeryTestCase;

/**
 * Class ExecutionTime - set the service time out up to 0
 *
 * @package Inpsyde\SearchReplace\Service
 */
class MaxExecutionTimeTest extends MonkeryTestCase\TestCase{

	public $testee;

	/**
	 * @dataProvider default_test_data
	 */
	public function test_set( $time, $max_execution_time ){

		// set set_time_limit up default_test_data - phpUnit will set it ever to 0
		\set_time_limit( $max_execution_time );

		$this->assertSame( $max_execution_time, ini_get( 'max_execution_time' ) );

		$this->assertInternalType( 'int', (int) $time );

		$testee = new Service\MaxExecutionTime;
		$testee->set( $time );

		$this->assertSame( $time, ini_get( 'max_execution_time' ) );

	}

	public function test_restore(){

		$stub = $this->getMockBuilder('Inpsyde\SearchReplace\Service\MaxExecutionTime')
					 ->disableOriginalConstructor()
		             ->getMock();

		$stub->expects( $this->once() )
			 ->method('set')
		     ->willReturn('foo');

		$testee = new Service\MaxExecutionTime;
		$testee->restore();

		$this->markTestIncomplete( 'This test has to add.' );

	}

	/**
	 * @return array
	 */
	public function default_test_data() {

		$data = [ ];

		# 1. Set timeout to 0
		$data[ 'test_1' ] = [ (string) 0, (string) 40 ];

		# 2. restore timeout
		$data[ 'test_2' ] = [ (string) 40, (string) 0 ];

		return $data;
	}

}