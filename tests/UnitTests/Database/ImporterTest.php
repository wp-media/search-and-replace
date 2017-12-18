<?php

namespace Inpsyde\SearchReplace\Tests\Database;

use Inpsyde\SearchReplace\Database\Importer;
use Inpsyde\SearchReplace\Tests\AbstractTestCase;
use \Mockery as m;
use Brain\Monkey as bm;

class ImporterTest extends AbstractTestCase {

	public function test_import_invalid_sql() {

		$this->assertMysqli();

		$testee = new Importer( $this->get_max_exec_mock() );
		$result = $testee->import_sql( '' );

		$this->assertEquals( - 1, $result );
	}

	public function test_import_valid_sql() {

		$this->assertMysqli();

		$testee = new Importer( $this->get_max_exec_mock() );
		$result = $testee->import_sql(
			'create table if not exists  some_table (PersonID int, SomeValue VARCHAR(255));'
		);

		$this->assertEquals( 1, $result );
	}
}
