<?php # -*- coding: utf-8 -*-

namespace Requisite;

/**
 * Class SPLAutoLoader
 *
 * @package Requisite
 */
class SPLAutoLoader implements AutoLoaderInterface {

	/**
	 * @type array
	 */
	private $rules = array();

	/**
	 * add a new instance to the spl load stack
	 *
	 * @param bool $append (Optional, default TRUE)
	 * @param bool $throws (Optional, default FALSE)
	 */
	public function __construct( $append = TRUE, $throws = FALSE ) {

		spl_autoload_register( array( $this, 'load' ), $throws, $append );
	}

	/**
	 * @param string $class
	 * @return bool
	 */
	public function load( $class ) {

		foreach ( $this->rules as $rule )
			if ( $rule->loadClass( (string) $class ) )
				return TRUE;

		return FALSE;
	}

	/**
	 * @param Rule\AutoLoadRuleInterface $rule
	 * @return void
	 */
	public function addRule( Rule\AutoLoadRuleInterface $rule ) {

		$this->rules[] = $rule;
	}

	/**
	 * remove this instance from the spl load stack
	 */
	public function unregister() {

		spl_autoload_unregister( array( $this, 'load' ) );
	}
} 