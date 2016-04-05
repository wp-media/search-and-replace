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

	public function __construct(){

		$this->store();

	}

	public function set( $time = 0 ){
	    set_time_limit( $time );
	}

	public function restore(){

    }

	public function store(){

		$this->met = ini_get('max_execution_time');

	}

}