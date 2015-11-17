<?php


use Inpsyde\SearchReplace\inc\Replace;

/**
 * Class ReplaceTest
 * Test Class for Replace in SearchReplace Plugin
 */
class ReplaceTest extends \PHPUnit_Framework_TestCase {

	//test if exception is thrown when no array is provided
	function test_setup_no_array() {

		$args = "noArray";
		$this->setExpectedException( "InvalidArgumentException" );
		$testee = new Replace( $args );

	}

	//test if exception is thrown if wrong array is provided
	function test_setup_wrong_array() {

		$args = Array( 1, 2 );
		$this->setExpectedException( "InvalidArgumentException" );
		$testee = new Replace( $args );
	}

	function test_empty_search_string() {

		$dbm = $this->getMock( '\Inpsyde\searchReplace\inc\DatabaseManager' );
		$this->assertTrue( $dbm instanceof \Inpsyde\searchReplace\inc\DatabaseManager );
		$testee = new Replace( $dbm );

		$result = $testee->replace_values( '', '', '' );
		$this->assertContains( 'Search string is empty', $result );

	}

	function test_string_replace() {

		$columns       = array(
			0 => 'comment_ID',
			1 =>
				array(
					0 => 'comment_ID',
					1 => 'comment_post_ID',
					2 => 'comment_author',

				),
		);
		$table_content = array(
			0 =>
				array(
					'comment_ID'      => '1',
					'comment_post_ID' => '1',
					'comment_author'  => 'Mr Wordpress',

				),
		);

		$dbm_mock = $this->getMock( '\Inpsyde\searchReplace\inc\DatabaseManager',
		                            array( 'get_columns', 'get_rows', 'get_table_content', 'flush' ) );

		$dbm_mock->expects( $this->any() )
		         ->method( 'get_columns' )
		         ->will( $this->returnValue( $columns ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'get_rows' )
		         ->will( $this->returnValue( 1 ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'get_table_content' )
		         ->will( $this->returnValue( $table_content ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'flush' );

		$testee = new Replace( $dbm_mock );
		$result = $testee->replace_values( 'Mr Wordpress', 'Mr. Drupal', 'wp_plugin_test_comments' );
		$this->assertEquals( $result[ 'changes' ][ 0 ][ 'to' ], 'Mr. Drupal' );
	}

	function test_substring_replace() {

		$columns       = array(
			0 => 'comment_ID',
			1 =>
				array(
					0 => 'comment_ID',
					1 => 'comment_post_ID',
					2 => 'comment_author',

				),
		);
		$table_content = array(
			0 =>
				array(
					'comment_ID'      => '1',
					'comment_post_ID' => '1',
					'comment_author'  => 'Mr Wordpress',

				),
		);

		$dbm_mock = $this->getMock( '\Inpsyde\searchReplace\inc\DatabaseManager',
		                            array( 'get_columns', 'get_rows', 'get_table_content', 'flush' ) );

		$dbm_mock->expects( $this->any() )
		         ->method( 'get_columns' )
		         ->will( $this->returnValue( $columns ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'get_rows' )
		         ->will( $this->returnValue( 1 ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'get_table_content' )
		         ->will( $this->returnValue( $table_content ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'flush' );

		$testee = new Replace( $dbm_mock );
		$result = $testee->replace_values( 'Mr', 'Mrs', 'wp_plugin_test_comments' );
		$this->assertEquals( $result[ 'changes' ][ 0 ][ 'to' ], 'Mrs Wordpress' );
	}

	function test_object_replace() {

		$columns       = array(
			0 => 'comment_ID',
			1 =>
				array(
					0 => 'comment_ID',
					1 => 'comment_post_ID',
					2 => 'comment_author',

				),
		);
		$table_content = array(
			0 =>
				array(
					'comment_ID'      => '1',
					'comment_post_ID' => '1',
					'comment_author'  => 'a:1:{s:4:"Test";s:12:"Mr Wordpress";}',

				),
		);

		$dbm_mock = $this->getMock( '\Inpsyde\searchReplace\inc\DatabaseManager',
		                            array( 'get_columns', 'get_rows', 'get_table_content', 'flush' ) );

		$dbm_mock->expects( $this->any() )
		         ->method( 'get_columns' )
		         ->will( $this->returnValue( $columns ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'get_rows' )
		         ->will( $this->returnValue( 1 ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'get_table_content' )
		         ->will( $this->returnValue( $table_content ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'flush' );

		$testee = new Replace( $dbm_mock );
		$result = $testee->replace_values( 'Mr Wordpress', 'Mr Drupal', 'wp_plugin_test_comments' );
		$this->assertEquals( $result[ 'changes' ][ 0 ][ 'to' ], 'a:1:{s:4:"Test";s:9:"Mr Drupal";}' );
	}


	function test_umlaut_replace() {

		$columns       = array(
			0 => 'comment_ID',
			1 =>
				array(
					0 => 'comment_ID',
					1 => 'comment_post_ID',
					2 => 'comment_author',

				),
		);
		$table_content = array(
			0 =>
				array(
					'comment_ID'      => '1',
					'comment_post_ID' => '1',
					'comment_author'  => 'Mr Wordpress',

				),
		);

		$dbm_mock = $this->getMock( '\Inpsyde\searchReplace\inc\DatabaseManager',
		                            array( 'get_columns', 'get_rows', 'get_table_content', 'flush' ) );

		$dbm_mock->expects( $this->any() )
		         ->method( 'get_columns' )
		         ->will( $this->returnValue( $columns ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'get_rows' )
		         ->will( $this->returnValue( 1 ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'get_table_content' )
		         ->will( $this->returnValue( $table_content ) );

		$dbm_mock->expects( $this->once() )
		         ->method( 'flush' );

		$testee = new Replace( $dbm_mock );
		$result = $testee->replace_values( 'Mr Wordpress', 'Mr. Drüpal', 'wp_plugin_test_comments' );
		$this->assertEquals( $result[ 'changes' ][ 0 ][ 'to' ], 'Mr. Drüpal' );
	}
}