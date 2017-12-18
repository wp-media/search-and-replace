<?php

use Inpsyde\SearchReplace\Database\Replace;
use Inpsyde\SearchReplace\Tests\AbstractTestCase;
use \Mockery as m;
use \Brain\Monkey as bm;

/**
 * Class ReplaceTest
 * Test Class for Replace in SearchReplace Plugin
 */
class ReplaceTest extends AbstractTestCase {

	function test_empty_search_string() {

		$dbm_mock = m::mock( '\Inpsyde\SearchReplace\Database\Manager' );
		$dbm_mock->shouldReceive( 'get_table_structure' );

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( '', '', '' );

		$this->assertContains( 'Search string is empty', $result );
	}

	/**
	 * Pay attention to the code sniffer regarding the WordPress spell check.
	 */
	function test_string_replace() {

		$columns = [
			0 => 'comment_ID',
			1 => [
				0 => 'comment_ID',
				1 => 'comment_post_ID',
				2 => 'comment_author',
			],
		];

		$table_content = [
			0 => [
				'comment_ID'      => '1',
				'comment_post_ID' => '1',
				'comment_author'  => 'Mr WordPress',
			],
		];

		$table_structure = [
			(object) [
				'Field' => 'comment_author',
				'Type'  => 'tinytext',
			],
		];

		$dbm_mock = m::mock(
			'\Inpsyde\SearchReplace\Database\Manager',
			[ m::mock( '\wpdb' ) ]
		);

		$dbm_mock->shouldReceive( 'get_table_structure' )
			->once()
			->andReturn( $table_structure );

		$dbm_mock->shouldReceive( 'get_columns' )
			->andReturn( $columns );

		$dbm_mock->shouldReceive( 'get_rows' )
			->once()
			->andReturn( 1 );

		$dbm_mock->shouldReceive( 'get_table_content' )
			->once()
			->andReturn( $table_content );

		$dbm_mock->shouldReceive( 'flush' )
			->once();

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( 'Mr WordPress', 'Mr. Drupal', 'wp_plugin_test_comments' );

		$this->assertEquals( 'Mr. Drupal', $result[ 'changes' ][ 0 ][ 'to' ] );
	}

	/**
	 * Pay attention to the code sniffer regarding the WordPress spell check.
	 */
	function test_substring_replace() {

		$columns = [
			0 => 'comment_ID',
			1 => [
				0 => 'comment_ID',
				1 => 'comment_post_ID',
				2 => 'comment_author',
			],
		];

		$table_content = [
			0 => [
				'comment_ID'      => '1',
				'comment_post_ID' => '1',
				'comment_author'  => 'Mr WordPress',
			],
		];

		$table_structure = [
			(object) [
				'Field' => 'comment_author',
				'Type'  => 'tinytext',
			],
		];

		$dbm_mock = m::mock(
			'\Inpsyde\SearchReplace\Database\Manager',
			[ m::mock( '\wpdb' ) ]
		);

		$dbm_mock->shouldReceive( 'get_table_structure' )
			->once()
			->andReturn( $table_structure );

		$dbm_mock->shouldReceive( 'get_columns' )
			->once()
			->andReturn( $columns );

		$dbm_mock->shouldReceive( 'get_rows' )
			->once()
			->andReturn( 1 );

		$dbm_mock->shouldReceive( 'get_table_content' )
			->once()
			->andReturn( $table_content );

		$dbm_mock->shouldReceive( 'flush' )
			->once();

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( 'Mr', 'Mrs', 'wp_plugin_test_comments' );

		$this->assertEquals( $result[ 'changes' ][ 0 ][ 'to' ], 'Mrs WordPress' );
	}

	function test_object_replace() {

		$serialized = 'a:1:{s:4:"Test";s:12:"Mr WordPress";}';

		$columns = [
			0 => 'comment_ID',
			1 => [
				0 => 'comment_ID',
				1 => 'comment_post_ID',
				2 => 'comment_author',
			],
		];

		$table_content = [
			0 => [
				'comment_ID'      => '1',
				'comment_post_ID' => '1',
				'comment_author'  => $serialized,
			],
		];

		$table_structure = [
			(object) [
				'Field' => 'comment_author',
				'Type'  => 'longtext',
			],
		];

		bm\Functions\when( 'is_serialized_string' )
			->alias(
				function ( $data ) {

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
			);

		bm\Functions\when( 'is_serialized' )
			->alias(
				function ( $data, $strict = true ) {

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
			);

		bm\Functions\when( 'maybe_serialize' )
			->alias(
				function ( $data ) {

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
			);

		$dbm_mock = m::mock(
			'\Inpsyde\SearchReplace\Database\Manager',
			[ m::mock( '\wpdb' ) ]
		);

		$dbm_mock->shouldReceive( 'get_table_structure' )
			->once()
			->andReturn( $table_structure );

		$dbm_mock->shouldReceive( 'get_columns' )
			->once()
			->andReturn( $columns );

		$dbm_mock->shouldReceive( 'get_rows' )
			->once()
			->andReturn( 1 );

		$dbm_mock->shouldReceive( 'get_table_content' )
			->once()
			->andReturn( $table_content );

		$dbm_mock->shouldReceive( 'flush' )
			->once();

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( 'Mr WordPress', 'Mr Drupal', 'wp_plugin_test_comments' );

		$this->assertEquals( 'a:1:{s:4:"Test";s:9:"Mr Drupal";}', $result[ 'changes' ][ 0 ][ 'to' ] );
	}

	function test_umlaut_replace() {

		$columns = [
			0 => 'comment_ID',
			1 => [
				0 => 'comment_ID',
				1 => 'comment_post_ID',
				2 => 'comment_author',
			],
		];

		$table_content = [
			0 => [
				'comment_ID'      => '1',
				'comment_post_ID' => '1',
				'comment_author'  => 'Mr Wordpress',
			],
		];

		$table_structure = [
			(object) [
				'Field' => 'comment_author',
				'Type'  => 'tinytext',
			],
		];

		$dbm_mock = m::mock(
			'\Inpsyde\SearchReplace\Database\Manager',
			[ m::mock( '\wpdb' ) ]
		);

		$dbm_mock->shouldReceive( 'get_table_structure' )
			->once()
			->andReturn( $table_structure );

		$dbm_mock->shouldReceive( 'get_columns' )
			->once()
			->andReturn( $columns );

		$dbm_mock->shouldReceive( 'get_rows' )
			->once()
			->andReturn( 1 );

		$dbm_mock->shouldReceive( 'get_table_content' )
			->once()
			->andReturn( $table_content );

		$dbm_mock->shouldReceive( 'flush' )
			->once();

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( 'Mr Wordpress', 'Mr. Drüpal', 'wp_plugin_test_comments' );

		$this->assertEquals( 'Mr. Drüpal', $result[ 'changes' ][ 0 ][ 'to' ] );
	}

}