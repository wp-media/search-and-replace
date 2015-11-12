<?php

namespace Inpsyde\searchReplace\inc;

/**
 * PSR-4 Autoloader Implementation
 *
 * Usage:
 * $autoloader = new \Inpsyde\[Core | inc | YourClassDirectory ]\Autoloader( __NAMESPACE__, __DIR__ );
 * $autoloader->register();
 *
 * To function Classes and Files need to share the exact same name,
 * e.g. The file for the class "FooBar" must be named "FooBar.php"
 *
 * @author  Andre Peiffer, Sven Hinse
 * @version 0.2
 * @package inc
 */

class Autoloader {

	/**
	 * Base namespace
	 *
	 * @access private
	 * @var string
	 */
	private $_namespace = NULL;

	/**
	 * location to load classes from
	 *
	 * @access private
	 * @var string
	 */
	private $_basepath = NULL;

	/**
	 * the file extension to load
	 *
	 * @access private
	 * @var string
	 */
	private $_extension = NULL;

	/**
	 * Creates a new Autoloader Instance
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

		return $this;
	}

	/**
	 * registers the autoloader
	 *
	 * @return this instance
	 */
	public function register() {

		spl_autoload_register( array( $this, 'autoload' ) );

		return $this;
	}

	/**
	 * unregisters the autoloader
	 *
	 * @return this instance
	 */
	public function unregister() {

		spl_autoload_unregister( array( $this, 'autoload' ) );

		return $this;
	}

	/**
	 * @param $class
	 *
	 * @return path to class on success, false on failure
	 * @throws \Exception , if Class does not exist in Namespace
	 */
	public function autoload( $class ) {

		//only include plugin classes at this point
		if ( strpos( $class, $this->_namespace ) === FALSE ) {
			return false;
		} else {
			//get position of first backslash
			$pos = strpos( $this->_namespace, '\\' );
			//cut off the Base Namespace before the backslash
			$class = substr( $class, $pos );

			//strip plugin dir, it is already part of $class
			$plugin_dir = dirname( $this->_basepath );
			$parent_dir = str_replace( $this->_namespace, '', $plugin_dir );
			//build path
			$filename = $parent_dir . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . $this->_extension;
			if ( file_exists( $filename ) ) {
				require_once( $filename );
				return $filename;
			}


			else {
					throw new \Exception( 'Class ' . $class . ' not found in ' . $filename );

			}

		}
	}



}

