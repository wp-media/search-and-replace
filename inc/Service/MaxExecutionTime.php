<?php
namespace Inpsyde\SearchReplace\Service;

/**
 * Class RunTime - set the service time out up to 0
 *
 * @package Inpsyde\SearchReplace\Service
 */
class MaxExecutionTime {

	/**
	 * @var max_execution_time
	 */
	private $met;

	/**
	 * Store current timelimit and set a limit
	 *
	 * @param int $time
	 */
	public function set( $time = 0 ){

		if( $time == 0 ){
			$this->store();
		}

	    set_time_limit( $time );

	}

	/**
	 * Restor timelimit
	 */
	public function restore(){

		$this->set( $this->met );

    }

	/**
	 *
	 */
	public function store(){

		$this->met = (int) ini_get('max_execution_time');

	}

}