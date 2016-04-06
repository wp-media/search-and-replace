<?php
namespace Inpsyde\SearchReplace\Service;

/**
 * Class RunTime - set the service time out up to 0
 *
 * @package Inpsyde\SearchReplace\Service
 */
class Notifyer implements NotifyerInterface {

	private $msg;

	public function __construct( $msg ){

		$this->msg = $msg;

	}

	public function render(){

	}

	public function display(){

	}

}