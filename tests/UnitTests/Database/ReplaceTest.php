<?php

use Inpsyde\SearchReplace\Database\Replace;
use Inpsyde\SearchReplace\Tests\AbstractTestCase;

/**
 * Class ReplaceTest
 * Test Class for Replace in SearchReplace Plugin
 */
class ReplaceTest extends AbstractTestCase {

	function test_empty_search_string() {

		$this->markTestIncomplete( 'This test has refactored.' );

		$dbm    = $this->getMock( '\Inpsyde\SearchReplace\Database\Manager' );
		$testee = new Replace( $dbm, $this->get_max_exec_mock() );

		$result = $testee->replace_values( '', '', '' );
		$this->assertContains( 'Search string is empty', $result );

	}

	function test_string_replace() {

		$this->markTestIncomplete( 'This test has refactored.' );

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

		$dbm_mock = $this->getMock(
			'\Inpsyde\SearchReplace\Database\Manager',
			array( 'get_columns', 'get_rows', 'get_table_content', 'flush' )
		);

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

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( 'Mr Wordpress', 'Mr. Drupal', 'wp_plugin_test_comments' );
		$this->assertEquals( $result[ 'changes' ][ 0 ][ 'to' ], 'Mr. Drupal' );
	}

	function test_substring_replace() {

		$this->markTestIncomplete( 'This test has refactored.' );

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

		$dbm_mock = $this->getMock(
			'\Inpsyde\searchReplace\inc\Database\Manager',
			array( 'get_columns', 'get_rows', 'get_table_content', 'flush' )
		);

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

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( 'Mr', 'Mrs', 'wp_plugin_test_comments' );
		$this->assertEquals( $result[ 'changes' ][ 0 ][ 'to' ], 'Mrs Wordpress' );
	}

	function test_object_replace() {

		$this->markTestIncomplete( 'This test has refactored.' );

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

		$dbm_mock = $this->getMock(
			'\Inpsyde\searchReplace\inc\Database\Manager',
			array( 'get_columns', 'get_rows', 'get_table_content', 'flush' )
		);

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

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( 'Mr Wordpress', 'Mr Drupal', 'wp_plugin_test_comments' );
		$this->assertEquals( $result[ 'changes' ][ 0 ][ 'to' ], 'a:1:{s:4:"Test";s:9:"Mr Drupal";}' );
	}

	function test_umlaut_replace() {

		$this->markTestIncomplete( 'This test has refactored.' );

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

		$dbm_mock = $this->getMock(
			'\Inpsyde\searchReplace\inc\Database\Manager',
			array( 'get_columns', 'get_rows', 'get_table_content', 'flush' )
		);

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

		$testee = new Replace( $dbm_mock, $this->get_max_exec_mock() );
		$result = $testee->replace_values( 'Mr Wordpress', 'Mr. Drüpal', 'wp_plugin_test_comments' );
		$this->assertEquals( $result[ 'changes' ][ 0 ][ 'to' ], 'Mr. Drüpal' );
	}


}