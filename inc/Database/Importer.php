<?php
namespace Inpsyde\SearchReplace\Database;

/**
 * Class Importer
 *
 * @package Inpsyde\SearchReplace\Database
 */
class Importer {

	/**
	 * imports a sql file via mysqli
	 *
	 * @param  string $sql
	 *
	 * @return int  Number of Sql queries made, -1 if error
	 */
	public function import_sql( $sql ) {

		//connect via mysqli for easier db import
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

		return $i;
	}

}