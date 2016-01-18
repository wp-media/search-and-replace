<?php

use Inpsyde\SearchReplace\inc\DatabaseManager;

class DatabaseImporterTest extends \WP_UnitTestCase {

	public $testee;

	public function setUp() {

		parent::setUp();

	}
	//wrong sql code should throw an error
	public function test_import_invalid_sql_() {

		$testee = new \Inpsyde\SearchReplace\inc\DatabaseImporter();
		$error = new WP_Error();
		$result = $testee->import_sql("",$error);
		$this->assertEquals (-1, $result);



	}
	//function should return the number of sql queries performed
	public function test_import_valid_sql() {

		$testee = new \Inpsyde\SearchReplace\inc\DatabaseImporter();
		$error = new WP_Error();
		$result = $testee->import_sql('create table if not exists  some_table (PersonID int, SomeValue VARCHAR(255));
',$error);
		$this->assertEquals (1, $result);



	}

	public function tearDown() {

	}

}