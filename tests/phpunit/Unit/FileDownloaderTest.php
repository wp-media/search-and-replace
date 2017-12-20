<?php

namespace Inpsyde\SearchReplace\Tests\Unit;

use Inpsyde\SearchReplace\FileDownloader;

class FileDownloaderTest extends AbstractTestCase {

	public function test_deliver_backup_file() {

		global $_POST;

		$_POST = [
			'insr_nonce_download' => true,
			'action'              => 'download_file',
			'sql_file'            => 'stub.sql',
			'compressed'          => false,
		];

		\Brain\Monkey\Functions\when( 'wp_verify_nonce' )
			->justReturn( true );
		\Brain\Monkey\Functions\when( 'sanitize_file_name' )
			->returnArg( 1 );

		$exporter_mock      = \Mockery::spy( 'Inpsyde\\SearchReplace\\Database\\Exporter' );
		$max_exec_time_mock = \Mockery::mock( 'Inpsyde\\SearchReplace\\Service\\MaxExecutionTime' );

		$max_exec_time_mock->shouldReceive( 'set' )
			->once();
		$max_exec_time_mock->shouldReceive( 'restore' )
			->once();

		$instance = new FileDownloader( $exporter_mock, $max_exec_time_mock );
		$instance->deliver_backup_file();

		$exporter_mock->shouldHaveReceived( 'deliver_backup' )
			->once();
	}

	/**
	 * @expectedException \Exception
	 */
	public function test_deliver_that_invalid_nonce_die() {

		global $_POST;

		$_POST = [
			'insr_nonce_download' => true,
			'action'              => 'download_file',
			'sql_file'            => 'stub.sql',
			'compressed'          => false,
		];

		\Brain\Monkey\Functions\when( 'wp_verify_nonce' )
			->justReturn( false );

		\Brain\Monkey\Functions\when( 'esc_html__' )
			->returnArg( 1 );

		\Brain\Monkey\Functions\expect( 'wp_die' )
			->once()
			->with( 'Cheating Uh?' )
			->andReturnUsing(
				function () {
					throw new \Exception('WP DIE');
				}
			);

		$exporter_mock      = \Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Exporter' );
		$max_exec_time_mock = \Mockery::mock( 'Inpsyde\\SearchReplace\\Service\\MaxExecutionTime' );

		$instance = new FileDownloader( $exporter_mock, $max_exec_time_mock );
		$instance->deliver_backup_file();
	}
}
