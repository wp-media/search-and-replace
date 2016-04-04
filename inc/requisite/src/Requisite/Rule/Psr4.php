<?php # -*- coding: utf-8 -*-

namespace Requisite\Rule;

use
	Requisite\Loader;

/**
 * Class Psr4
 *
 * Mapping a namespace structure to a directory structure
 * following the Psr4 standard
 *
 * @package Requisite\Rule
 */
class Psr4 implements AutoLoadRuleInterface {

	/**
	 * @type Loader\FileLoaderInterface
	 */
	private $file_loader;

	/**
	 * @type string
	 */
	private $base_ns;

	/**
	 * @type string
	 */
	private $base_dir;

	/**
	 * @param string $base_dir
	 * @param string $base_ns
	 * @param Loader\FileLoaderInterface $file_loader $file_loader (Optional)
	 */
	function __construct( $base_dir, $base_ns = '', Loader\FileLoaderInterface $file_loader = NULL ) {

		// trim potential trailing slashes
		$this->base_dir = rtrim( (string) $base_dir, '\\/' );

		// always absolute namespaces with trailing slash
		// trim slashes AND spaces
		$base_ns  = trim( $base_ns, '\\ ' );
		if ( ! empty( $base_ns ) )
			$base_ns = '\\' . $base_ns . '\\';
		else
			$base_ns = '\\';
		$this->base_ns = $base_ns;

		if ( ! $file_loader )
			$this->file_loader = new Loader\DirectoryCacheFileLoader( $this->base_dir );
		else
			$this->file_loader = $file_loader;
	}

	/**
	 * @param string $class
	 * @return bool
	 */
	public function loadClass( $class ) {

		//make sure the class name is absolute
		if ( 0 !== strpos( $class, '\\' ) )
			$class = '\\' . $class;

		// check if the namespace matches the class
		if ( 0 !== strpos( $class, $this->base_ns ) )
			return FALSE;

		// strip the base namespace from the beginning of the class name
		if ( $this->base_ns === substr( $class, 0, strlen( $this->base_ns ) ) )
			$class = substr( $class, strlen( $this->base_ns ) );

		$class = ltrim( $class, '\\' );
		$class = str_replace( '\\', '/', $class );
		$file = $this->base_dir . '/' . $class . '.php';

		return $this->file_loader->loadFile( $file );
	}
}
