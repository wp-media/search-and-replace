<?php

namespace Inpsyde\SearchAndReplace\Http;

use Inpsyde\SearchAndReplace\File\UploadedFile;

/**
 * @package Inpsyde\SearchAndReplace\Http
 */
class FileBag extends ParameterBag {

	/**
	 * Parameter storage.
	 *
	 * @var array
	 */
	protected $parameters;

	/**
	 * ParameterBag constructor.
	 *
	 * @param array $parameters
	 */
	public function __construct( array $parameters = array() ) {

		$this->add( $parameters );
	}

	/**
	 * @param array $files An array of files.
	 */
	public function add( array $files = array() ) {

		foreach ( $files as $key => $file ) {
			$this->set( $key, $file );
		}
	}

	/**
	 * Sets a parameter by name.
	 *
	 * @param string $key
	 * @param mixed  $file
	 */
	public function set( $key, $file ) {

		if ( ! $file instanceof UploadedFile ) {
			$this->parameters[ $key ] = $file;

			return;
		}

		$this->parameters[ $key ] = new UploadedFile( $file );
	}

}
