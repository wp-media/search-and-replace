<?php

namespace Inpsyde\SearchReplace\Service;

/**
 * Interface NotifyerInterface
 *
 * @package Inpsyde\SearchReplace\Service
 */
interface NotifyerInterface {

	/**
	 * @param string $msg
	 */
	public function display();
	public function render();

}