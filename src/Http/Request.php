<?php

namespace Inpsyde\SearchAndReplace\Http;

/**
 * Class Request
 *
 * @package Inpsyde\SearchAndReplace\Http
 */
class Request {

	/**
	 * Query string parameters ($_GET).
	 *
	 * @var ParameterBag
	 */
	private $query;

	/**
	 * Request body parameters ($_POST).
	 *
	 * @var ParameterBag
	 */
	private $data;

	/**
	 * Cookies ($_COOKIES)
	 *
	 * @var ParameterBag
	 */
	private $cookies = [];

	/**
	 * Server and execution environment parameters ($_SERVER).
	 *
	 * @var ParameterBag
	 */
	private $server = [];

	/**
	 * Files ($_FILES)
	 *
	 * @var ParameterBag
	 */
	private $files = [];

	/**
	 * Request constructor.
	 *
	 * @param array $query
	 * @param array $data
	 * @param array $cookies
	 * @param array $server
	 * @param array $files
	 */
	public function __construct( array $query = array(), array $data = array(), array $cookies = array(), array $server = array(), array $files = array() ) {

		$this->query   = new ParameterBag( $query );
		$this->data    = new ParameterBag( $data );
		$this->cookies = new ParameterBag( $cookies );
		$this->server  = new ParameterBag( $server );
		$this->files   = new ParameterBag( $files );
	}

	/**
	 * Creates a new instance from globals.
	 *
	 * @return static
	 */
	public static function from_globals() {

		$server = static::normalize_server( $_SERVER );

		// phpcs:disable
		return new static(
			$_GET,
			$_POST,
			$_COOKIE,
			$server,
			$_FILES
		);
		// phpcs:enable
	}

	/**
	 *
	 * With the php's bug #66606, the php's built-in web server
	 * stores the Content-Type and Content-Length header values in
	 * HTTP_CONTENT_TYPE and HTTP_CONTENT_LENGTH fields.
	 *
	 * @param array $server
	 *
	 * @return array
	 */
	private static function normalize_server( array $server = [] ) {

		if ( 'cli-server' === PHP_SAPI ) {
			if ( array_key_exists( 'HTTP_CONTENT_LENGTH', $_SERVER ) ) {
				$server[ 'CONTENT_LENGTH' ] = $_SERVER[ 'HTTP_CONTENT_LENGTH' ];
			}
			if ( array_key_exists( 'HTTP_CONTENT_TYPE', $_SERVER ) ) {
				$server[ 'CONTENT_TYPE' ] = $_SERVER[ 'HTTP_CONTENT_TYPE' ];
			}
		}

		return $server;
	}

	/**
	 * @return ParameterBag
	 */
	public function cookies() {

		return $this->cookies;
	}

	/**
	 * @return ParameterBag
	 */
	public function server() {

		return $this->server;
	}

	/**
	 * @return ParameterBag
	 */
	public function files() {

		return $this->files;
	}

	/**
	 * @return ParameterBag
	 */
	public function query() {

		return $this->query;
	}

	/**
	 * @return ParameterBag
	 */
	public function data() {

		return $this->data;
	}

}