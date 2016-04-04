<?php # -*- coding: utf-8 -*-

namespace Requisite;

/**
 * Interface AutoLoaderInterface
 *
 * @package Requisite
 */
interface AutoLoaderInterface {

	/**
	 * @param Rule\AutoLoadRuleInterface $rule
	 * @return void
	 */
	public function addRule( Rule\AutoLoadRuleInterface $rule );
}