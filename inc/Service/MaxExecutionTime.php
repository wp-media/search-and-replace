<?php
namespace Inpsyde\SearchReplace\Service;

/**
 * Class RunTime - set the service time out up to 0
 *
 * @package Inpsyde\SearchReplace\Service
 */
class MaxExecutionTime {

	/**
	 * @var int
	 */
	private $met;

	/**
	 * Store current timelimit and set a limit
	 *
	 * @param int $time
	 */
	public function set( $time = 0 ) {

		if ( 0 === $time ) {
			$this->store();
		}

		@set_time_limit( $time );

	}

	/**
	 * Restore timelimit.
	 */
	public function restore() {

		$this->set( $this->met );

	}

	/**
	 * Fetch the max_execution_time from php.ini.
	 */
	public function store() {

		$this->met = (int) ini_get( 'max_execution_time' );

	}

}
