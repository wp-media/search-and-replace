<?php

namespace Inpsyde\SearchReplace\Tests\Page;

use Inpsyde\SearchReplace\Page\SearchReplace;
use Inpsyde\SearchReplace\Tests\AbstractTestCase;

class SearchReplaceTest extends AbstractTestCase {

	/**
	 * @dataProvider selected_tables_provider
	 *
	 * @param mixed $submitted
	 * @param array $expected
	 */
	public function test_selected_tables_only_returns_available_tables( $submitted, array $expected ) {

		global $_POST;

		$_POST = [ 'select_tables' => $submitted ];

		$dbm = \Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Manager' );
		$dbm->shouldReceive( 'get_tables' )
			->andReturn( [ 'wp_2_posts', 'wp_2_options' ] );

		$instance = new SearchReplace(
			$dbm,
			\Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Replace' ),
			\Mockery::mock( 'Inpsyde\\SearchReplace\\Database\\Exporter' ),
			\Mockery::mock( 'Inpsyde\\SearchReplace\\FileDownloader' )
		);

		$method = new \ReflectionMethod( $instance, 'selected_tables' );
		$method->setAccessible( true );

		$this->assertSame( $expected, $method->invoke( $instance ) );
	}

	public function selected_tables_provider() {

		return [
			'sequential keys'                      => [
				[ 'wp_2_posts', 'wp_2_options' ],
				[ 'wp_2_posts', 'wp_2_options' ],
			],
			'unavailable table is dropped'         => [
				[ 'wp_2_posts', 'wp_users' ],
				[ 'wp_2_posts' ],
			],
			'unavailable table under a custom key' => [
				[ 'wp_users', 9 => 'wp_users' ],
				[],
			],
			'string keys are validated too'        => [
				[ 'a' => 'wp_users', 'b' => 'wp_2_options' ],
				[ 'wp_2_options' ],
			],
			'non-string entries are dropped'       => [
				[ [ 'wp_2_posts' ], 'wp_2_posts' ],
				[ 'wp_2_posts' ],
			],
			'not an array'                         => [
				'wp_2_posts',
				[],
			],
		];
	}
}
