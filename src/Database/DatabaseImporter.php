<?php

namespace Inpsyde\SearchAndReplace\Database;

use Inpsyde\SearchAndReplace\Core\PluginConfig;
use Inpsyde\SearchAndReplace\Service\MaxExecutionTime;

/**
 * Class DatabaseImporter
 *
 * @package Inpsyde\SearchAndReplace\Database
 */
class DatabaseImporter {

	/**
	 * @var PluginConfig
	 */
	private $config;

	/**
	 * @var MaxExecutionTime
	 */
	private $max_execution;

	/**
	 * Importer constructor.
	 *
	 * @param MaxExecutionTime $max_execution
	 */
	public function __construct( PluginConfig $config, MaxExecutionTime $max_execution ) {

		$this->config        = $config;
		$this->max_execution = $max_execution;
	}

	/**
	 * Imports a sql file via mysqli
	 *
	 * @param  string $sql
	 *
	 * @return int  Number of Sql queries made, -1 if error
	 */
	public function import( $sql ) {

		$this->max_execution->set();

		// connect via mysqli for easier db import
		$mysqli = new \mysqli(
			$this->config->get( 'db.host' ),
			$this->config->get( 'db.user' ),
			$this->config->get( 'db.password' ),
			$this->config->get( 'db.name' )
		);

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
