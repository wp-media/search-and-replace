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

		$dbm_mock = \Mockery::mock( '\Inpsyde\SearchReplace\Database\Manager' );
		$dbm_mock->shouldReceive( 'get_table_structure' );

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( '', '', '' );

		$this->assertContains( 'Search string is empty', $result );
	}

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

		$dbm_mock = \Mockery::mock(
			'\Inpsyde\SearchReplace\Database\Manager',
			[ \Mockery::mock( '\wpdb' ) ]
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

		$dbm_mock = \Mockery::mock(
			'\Inpsyde\SearchReplace\Database\Manager',
			[ \Mockery::mock( '\wpdb' ) ]
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

		$dbm_mock = \Mockery::mock(
			'\Inpsyde\SearchReplace\Database\Manager',
			[ \Mockery::mock( '\wpdb' ) ]
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

	function objectProvider() {

		return [
			[
				'serialized' => 's:12:"Mr WordPress";',
				'expected'   => 's:9:"Mr Drupal";',
				'search'     => 'Mr WordPress',
				'replace'    => 'Mr Drupal',
			],
			[
				'serialized' => 'a:1:{s:4:"Test";s:12:"Mr WordPress";}',
				'expected'   => 'a:1:{s:4:"Test";s:9:"Mr Drupal";}',
				'search'     => 'Mr WordPress',
				'replace'    => 'Mr Drupal',
			],
			[
				'serialized' => serialize(
					$nestedObjects = (object) [
						'types' => [
							'text'     => 'This is a simple name',
							'url'      => 'http://www.wordpress.org',
							'stdClass' => new \stdClass(),
							'array'    => [
								'simple element indexed',
							],
						],
					]
				),
				'expected'   => 'O:8:"stdClass":1:{s:5:"types";a:4:{s:4:"text";s:21:"This is a simple name";s:3:"url";s:21:"http://www.drupal.org";s:8:"stdClass";O:8:"stdClass":0:{}s:5:"array";a:1:{i:0;s:22:"simple element indexed";}}}',
				'search'     => '.wordpress.',
				'replace'    => '.drupal.',
			],
			[
				'serialized' => serialize(
					(object) [
						'types' => [
							'text'     => 'This is a simple name',
							'url'      => 'http://www.wordpress.org',
							'stdClass' => (object) [
								'property' => 'An inner property text',
							],
							'array'    => [
								'simple element indexed',
							],
						],
					]
				),
				'expected'   => 'O:8:"stdClass":1:{s:5:"types";a:4:{s:4:"text";s:21:"This is a simple name";s:3:"url";s:24:"http://www.wordpress.org";s:8:"stdClass";O:8:"stdClass":1:{s:8:"property";s:19:"Replaced inner text";}s:5:"array";a:1:{i:0;s:22:"simple element indexed";}}}',
				'search'     => 'An inner property text',
				'replace'    => 'Replaced inner text',
			],
			[
				'serialized' => serialize(
					(object) [
						'types' => [
							'text'     => 'This is a simple name',
							'url'      => 'http://www.wordpress.org',
							'stdClass' => (object) [
								'property' => 'An inner property text',
							],
							'array'    => [
								'types' => [
									'text'     => 'This is a simple name',
									'url'      => 'http://www.wordpress.org',
									'stdClass' => (object) [
										'property' => 'An inner property text',
									],
									'array'    => [
										'simple element indexed',
									],
								],
							],
						],
					]
				),
				'expect'     => 'O:8:"stdClass":1:{s:5:"types";a:4:{s:4:"text";s:21:"This is a simple name";s:3:"url";s:24:"http://www.wordpress.org";s:8:"stdClass";O:8:"stdClass":1:{s:8:"property";s:22:"This has been replaced";}s:5:"array";a:1:{s:5:"types";a:4:{s:4:"text";s:21:"This is a simple name";s:3:"url";s:24:"http://www.wordpress.org";s:8:"stdClass";O:8:"stdClass":1:{s:8:"property";s:22:"This has been replaced";}s:5:"array";a:1:{i:0;s:22:"simple element indexed";}}}}}',
				'search'     => 'An inner property text',
				'replace'    => 'This has been replaced',
			],
		];
	}

	/**
	 * @dataProvider objectProvider
	 */
	function test_object_replace( $serialized, $expected, $search, $replace ) {

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

		\Brain\Monkey\Functions\when( 'is_serialized_string' )
			->alias( [ $this, 'is_serialized_string' ] );

		\Brain\Monkey\Functions\when( 'is_serialized' )
			->alias( [ $this, 'is_serialized' ] );

		\Brain\Monkey\Functions\when( 'maybe_serialize' )
			->alias( [ $this, 'maybe_serialize' ] );

		\Brain\Monkey\Functions\when( 'maybe_unserialize' )
			->alias( [ $this, 'maybe_unserialize' ] );

		$dbm_mock = \Mockery::mock(
			'\Inpsyde\SearchReplace\Database\Manager',
			[ \Mockery::mock( '\wpdb' ) ]
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
		$result = $testee->replace_values( $search, $replace, 'wp_plugin_test_comments' );

		$this->assertEquals( $expected, $result[ 'changes' ][ 0 ][ 'to' ] );
	}

	/**
	 * @dataProvider serializedDataProvider
	 */
	function test_recursive_unserialize_replace( $from, $to, $data, $expected ) {

		\Brain\Monkey\Functions\when( 'is_serialized_string' )
			->alias( [ $this, 'is_serialized_string' ] );

		\Brain\Monkey\Functions\when( 'is_serialized' )
			->alias( [ $this, 'is_serialized' ] );

		\Brain\Monkey\Functions\when( 'maybe_serialize' )
			->alias( [ $this, 'maybe_serialize' ] );

		\Brain\Monkey\Functions\when( 'maybe_unserialize' )
			->alias( [ $this, 'maybe_unserialize' ] );

		$manager_mock       = \Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Manager' );
		$max_exec_time_mock = \Mockery::mock( 'Inpsyde\\SearchReplace\\Service\\MaxExecutionTime' );

		$sut      = new Replace( $manager_mock, $max_exec_time_mock);
		$response = $sut->recursive_unserialize_replace( $from, $to, $data );

		$this->assertSame( $expected, $response );
	}

	function serializedDataProvider() {

		return [
			[
				'from'     => '1',
				'two'      => '2',
				'data'     => 'a:0:{}',
				'expected' => 'a:0:{}',
			],
			[
				'from'     => 'count',
				'two'      => 'new_count',
				'data'     => 'a:2:{i:2;s:5:"count";s:12:"_multiwidget";i:1;}',
				'expected' => 'a:2:{i:2;s:9:"new_count";s:12:"_multiwidget";i:1;}',
			],
		];
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
