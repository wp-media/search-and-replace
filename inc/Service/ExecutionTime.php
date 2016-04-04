<?php
namespace Inpsyde\SearchReplace\Service;

/**
 * Class ExecutionTime - set the service time out up to 0
 *
 * @package Inpsyde\SearchReplace\Service
 */
class ExecutionTime{

	/**
	 * @var max_execution_time
	 */
	private $met;

	public function __construct(){

		$this->met = ini_get('max_execution_time');

        print_r( $max_execution_time );
        echo "\n";

        set_time_limit(0);

        $max_execution_time = ini_get('max_execution_time');

        print_r( $max_execution_time );
        echo "\n";

        die();

	}

}