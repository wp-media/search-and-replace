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

	public function test_empty_search_string() {

		$dbm_mock = \Mockery::mock( '\Inpsyde\SearchReplace\Database\Manager' );
		$dbm_mock->shouldReceive( 'get_table_structure' );

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( '', '', '' );

		$this->assertContains( 'Search string is empty', $result );
	}

	public function test_string_replace() {

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

		$this->assertEquals( 'Mr. Drupal', $result['changes'][0]['to'] );
	}

	public function test_umlaut_replace() {

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

		$this->assertEquals( 'Mr. Drüpal', $result['changes'][0]['to'] );
	}

	public function test_substring_replace() {

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

		$this->assertEquals( $result['changes'][0]['to'], 'Mrs WordPress' );
	}

	public function objectProvider() {

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
	public function test_object_replace( $serialized, $expected, $search, $replace ) {

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

		$this->assertEquals( $expected, $result['changes'][0]['to'] );
	}

	/**
	 * @dataProvider serializedDataProvider
	 */
	public function test_recursive_unserialize_replace( $from, $to, $data, $expected, $serialized ) {

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

		$sut      = new Replace( $manager_mock, $max_exec_time_mock );
		$response = $sut->recursive_unserialize_replace( $from, $to, $data, $serialized );

		$this->assertSame( $expected, $response );
	}

	public function serializedDataProvider() {

		return [
			[
				'from'       => '1',
				'to'         => '2',
				'data'       => 'a:0:{}',
				'expected'   => 'a:0:{}',
				'serialized' => true,
			],
			[
				'from'       => 'count',
				'to'         => 'new_count',
				'data'       => 'a:2:{i:2;s:5:"count";s:12:"_multiwidget";i:1;}',
				'expected'   => 'a:2:{i:2;s:9:"new_count";s:12:"_multiwidget";i:1;}',
				'serialized' => true,
			],
			[
				'from'       => 'grr',
				'to'         => 'grra',
				'data'       => 's:+3:\"grr\";',
				'expected'   => 's:+3:\"grra\";',
				'serialized' => false,
			],
			[
				'from'       => 'grr',
				'to'         => 'grra',
				'data'       => 'a:+3:\"grr\";',
				'expected'   => 'a:+3:\"grra\";',
				'serialized' => false,
			],
			[
				'from'       => 'grr',
				'to'         => 'grra',
				'data'       => 'O:+3:\"grr\";',
				'expected'   => 'O:+3:\"grra\";',
				'serialized' => false,
			],
			[
				'from'       => 'grr',
				'to'         => 'grra',
				'data'       => 'C:+3:\"grr\";',
				'expected'   => 'C:+3:\"grra\";',
				'serialized' => false,
			],
			[
				'from'       => 'grr',
				'to'         => 'grra',
				'data'       => 'o:+3:\"grr\";',
				'expected'   => 'o:+3:\"grra\";',
				'serialized' => false,
			],
			[
				'from'       => 'grr',
				'to'         => 'grra',
				'data'       => 'S:+3:\"grr\";',
				'expected'   => 'S:+3:\"grra\";',
				'serialized' => false,
			],
			[
				'from'       => 'a',
				'to'         => 'b',
				'data'       => 'O:3:%22foo%22:2:{s:4:%22file%22;s:9:%22shell.php%22;s:4:%22data%22;s:5:%22aaaa%22;}',
				'expected'   => 'O:3:%22foo%22:2:{s:4:%22file%22;s:9:%22shell.php%22;s:4:%22d|bt|b%22;s:5:%22|b|b|b|b%22;}',
				'serialized' => false,
			],
			[
				'from'       => 'l',
				'to'         => 'x',
				'data'       => 's:8:"last_log";s:19:"making a test entry";',
				'expected'   => 's:8:"xast_xog";',
				'serialized' => true,
			],
			[
				'from'       => '0',
				'to'         => '1',
				'data'       => 's:11:"\x00*\x00log_date";s:8:"07-09-17";',
				'expected'   => 's:0:"";',
				'serialized' => true,
			],
			[
				'from'       => '0',
				'to'         => '1',
				'data'       => 'a:+2:{i:1;s:3:"key";i:0;o:1:"s:2:"ID";s:1:"1";}}',
				'expected'   => 'a:+2:{i:1;s:3:"key";i:1;o:1:"s:2:"ID";s:1:"1";}}',
				'serialized' => false,
			],
		];
	}
}
