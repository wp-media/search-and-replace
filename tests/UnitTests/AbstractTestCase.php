<?php

namespace Inpsyde\SearchReplace\Tests;

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * This abstract class will contain some helper methods to easily create mocks.
 */
abstract class AbstractTestCase extends TestCase {

	protected function assertMysqli() {

		if ( ! extension_loaded( 'mysqli' ) ) {

			$this->markTestSkipped(
				'The MySQLi extension is not available.'
			);
		}
	}

	protected function get_max_exec_mock() {

		$stub = $this->getMockBuilder( '\Inpsyde\SearchReplace\Service\MaxExecutionTime' )
			->getMock();
		$stub->expects( $this->any() )
			->method( 'set' );
		$stub->expects( $this->any() )
			->method( 'restore' );

		return $stub;
	}

	protected function setUp() {

		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown() {

		Monkey\tearDown();
		parent::tearDown();
	}

	public function is_serialized_string( $data ) {

		// if it isn't a string, it isn't a serialized string.
		if ( ! is_string( $data ) ) {
			return false;
		}
		$data = trim( $data );
		if ( strlen( $data ) < 4 ) {
			return false;
		} elseif ( ':' !== $data[ 1 ] ) {
			return false;
		} elseif ( ';' !== substr( $data, - 1 ) ) {
			return false;
		} elseif ( $data[ 0 ] !== 's' ) {
			return false;
		} elseif ( '"' !== substr( $data, - 2, 1 ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function is_serialized( $data, $strict = true ) {

		// if it isn't a string, it isn't serialized.
		if ( ! is_string( $data ) ) {
			return false;
		}
		$data = trim( $data );
		if ( 'N;' == $data ) {
			return true;
		}
		if ( strlen( $data ) < 4 ) {
			return false;
		}
		if ( ':' !== $data[ 1 ] ) {
			return false;
		}
		if ( $strict ) {
			$lastc = substr( $data, - 1 );
			if ( ';' !== $lastc && '}' !== $lastc ) {
				return false;
			}
		} else {
			$semicolon = strpos( $data, ';' );
			$brace     = strpos( $data, '}' );
			// Either ; or } must exist.
			if ( false === $semicolon && false === $brace ) {
				return false;
			}
			// But neither must be in the first X characters.
			if ( false !== $semicolon && $semicolon < 3 ) {
				return false;
			}
			if ( false !== $brace && $brace < 4 ) {
				return false;
			}
		}
		$token = $data[ 0 ];
		switch ( $token ) {
			case 's' :
				if ( $strict ) {
					if ( '"' !== substr( $data, - 2, 1 ) ) {
						return false;
					}
				} elseif ( false === strpos( $data, '"' ) ) {
					return false;
				}
			// or else fall through
			case 'a' :
			case 'O' :
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b' :
			case 'i' :
			case 'd' :
				$end = $strict ? '$' : '';

				return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
		}

		return false;
	}

	public function maybe_serialize( $data ) {

		if ( is_array( $data ) || is_object( $data ) ) {
			return serialize( $data );
		}

		// Double serialization is required for backward compatibility.
		// See https://core.trac.wordpress.org/ticket/12930
		// Also the world will end. See WP 3.6.1.
		if ( is_serialized( $data, false ) ) {
			return serialize( $data );
		}

		return $data;
	}

	public function maybe_unserialize( $data ) {

		if ( is_serialized( $data ) ) {
			return @unserialize( $data );
		}

		return $data;
	}
}
