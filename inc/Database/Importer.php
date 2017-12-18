<?php
namespace Inpsyde\SearchReplace\Database;

use Inpsyde\SearchReplace\Service\MaxExecutionTime;

/**
 * Class Importer
 *
 * @package Inpsyde\SearchReplace\Database
 */
class Importer {

	/**
	 * @var MaxExecutionTime
	 */
	private $max_execution;

	/**
	 * Importer constructor.
	 *
	 * @param MaxExecutionTime $max_execution
	 */
	public function __construct( MaxExecutionTime $max_execution ) {
		$this->max_execution = $max_execution;
	}

	/**
	 * Imports a sql file via mysqli
	 *
	 * @param  string $sql
	 *
	 * @return int  Number of Sql queries made, -1 if error
	 */
	public function import_sql( $sql ) {

		$this->max_execution->set();

		// connect via mysqli for easier db import
		$mysqli = new \mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

		// Run the SQL
		$i = 1;
		if ( $mysqli->multi_query( $sql ) ) {
			do {
				if ( $mysqli->more_results() ) {
					$mysqli->next_result();

					$i ++;
				}
			} while ( $mysqli->more_results() );
		}

		if ( $mysqli->errno ) {
			return - 1;
		}

		mysqli_close( $mysqli );

		$this->max_execution->restore();

		return $i;
	}

}
