<?php

namespace Inpsyde\searchReplace\inc;

/**
 * PSR-4 Autoloader Implementation
 * Usage:
 * $autoloader = new \Inpsyde\[Core | inc | YourClassDirectory ]\Autoloader( __NAMESPACE__, __DIR__ );
 * $autoloader->register();
 * To function Classes and Files need to share the exact same name,
 * e.g. The file for the class "FooBar" must be named "FooBar.php"
 *
 * @author  Andre Peiffer, Sven Hinse
 * @version 2016-01-28
 * @package inc
 */

class Autoloader {

	/**
	 * Base namespace
	 *
	 * @access private
	 * @var string
	 */
	private $_namespace;

	/**
	 * location to load classes from
	 *
	 * @access private
	 * @var string
	 */
	private $_basepath;

	/**
	 * the file extension to load
	 *
	 * @access private
	 * @var string
	 */
	private $_extension;

	/**
	 * Creates a new Autoloader Instance.
	 *
	 * @param string $namespace basenamespace
	 * @param string $path      basepath
	 * @param string $extension file extension to load
	 */
	public function __construct( $namespace, $path, $extension = '.php' ) {

		//Normalize basenamespace and basepath
		$this->_namespace = trim( $namespace, '\\' ) . '\\';
		$this->_basepath  = rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
		$this->_extension = $extension;
	}

	/**
	 * Registers the autoloader.
	 *
	 * @return $this
	 */
	public function register() {

		spl_autoload_register( array( $this, 'autoload' ) );
		return $this;
	}

	/**
	 * Unregisters the autoloader.
	 *
	 * @return $this instance
	 */
	public function unregister() {

		spl_autoload_unregister( array( $this, 'autoload' ) );
		return $this;
	}

	/**
	 * @param $class
	 *
	 * @return bool|string Path to class on success, false on failure
	 * @throws \Exception  if Class does not exist in Namespace
	 */
	public function autoload( $class ) {

		//only include plugin classes at this point
		if ( strpos( $class, $this->_namespace ) === FALSE ) {
			return FALSE;
		} else {

			//cut off the Base Namespace including the backslash
			$pos   = strpos( $this->_namespace, '\\' );
			$class = substr( $class, $pos + 1 );

			//cut off the second part of namespace before the backslash, plugin directory may have another name
			$pos   = strpos( $class, '\\' );
			$class = substr( $class, $pos );

			//build path
			$filename = $this->_basepath . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . $this->_extension;
			if ( file_exists( $filename ) ) {
				require_once( $filename );

				return $filename;
			} else {
				throw new \Exception( 'Class ' . $class . ' not found in ' . $filename );

			}

		}
	}

}