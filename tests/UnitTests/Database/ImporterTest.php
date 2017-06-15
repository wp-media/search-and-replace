<?php

namespace Inpsyde\SearchReplace\Tests\Database;

use Inpsyde\SearchReplace\Database\Importer;
use Inpsyde\SearchReplace\Tests\AbstractTestCase;

class ImporterTest extends AbstractTestCase {

	//wrong sql code should throw an error
	public function test_import_invalid_sql_() {

		//$this->assertMysqli();
		$mysqli = $this->getMockBuilder( '\mysqli' )
			->getMock();

		$mysqli->expects( $this->once() )
			->method( 'multi_query' );

		$testee = new Importer( $this->get_max_exec_mock() );
		$result = $testee->import_sql( "" );
		$this->assertEquals( - 1, $result );
	}

	//function should return the number of sql queries performed
	public function test_import_valid_sql() {

		$this->assertMysqli();

		$testee = new Importer( $this->get_max_exec_mock() );
		$result = $testee->import_sql(
			'create table if not exists  some_table (PersonID int, SomeValue VARCHAR(255));'
		);
		$this->assertEquals( 1, $result );
	}

}