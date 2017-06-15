<?php

namespace Inpsyde\SearchReplace\Tests;

/**
 * This abstract class will contain some helper methods to easily create mocks.
 */
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase {

	protected function assertMysqli() {

		if ( ! extension_loaded( 'mysqli' ) ) {

			$this->markTestSkipped(
				'The MySQLi extension is not available.'
			);
		}
	}

	protected function get_max_exec_mock() {

		$stub = $this->getMockBuilder( '\Inpsyde\SearchReplace\Service\MaxExecutionTime' )
			->getMock();
		$stub->expects( $this->any() )
			->method( 'set' );
		$stub->expects( $this->any() )
			->method( 'restore' );

		return $stub;
	}
}
