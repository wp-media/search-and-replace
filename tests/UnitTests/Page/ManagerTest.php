<?php

namespace Inpsyde\SearchReplace\Tests\Page;

use Brain\Monkey\Functions;
use Inpsyde\SearchReplace\Page\Manager;
use Inpsyde\SearchReplace\Tests\AbstractTestCase;

class ManagerTest extends AbstractTestCase {

	protected function setUp() {

		parent::setUp();

		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'check_admin_referer' )->justReturn( true );
	}

	private function get_page( $class, $slug ) {

		$page = \Mockery::mock( 'Inpsyde\\SearchReplace\\Page\\' . $class );
		$page->shouldReceive( 'get_slug' )->andReturn( $slug );
		$page->shouldReceive( 'get_page_title' )->andReturn( $class );
		$page->shouldReceive( 'get_menu_title' )->andReturn( $class );

		return $page;
	}

	private function save_page( Manager $manager, $slug ) {

		$method = new \ReflectionMethod( $manager, 'save_page' );
		$method->setAccessible( true );

		return $method->invoke( $manager, $slug );
	}

	public function test_save_page_denies_sql_import_without_network_capability_on_multisite() {

		$page = $this->get_page( 'SqlImport', 'sql-import' );
		$page->shouldReceive( 'save' )->never();

		$manager = new Manager();
		$manager->add_page( $page );

		Functions\when( 'is_multisite' )->justReturn( true );
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'manage_network_options' )
			->andReturn( false );
		Functions\expect( 'wp_die' )
			->once()
			->andReturnUsing(
				function () {
					throw new \Exception( 'WP DIE' );
				}
			);

		$this->setExpectedException( '\Exception', 'WP DIE' );

		$this->save_page( $manager, 'sql-import' );
	}

	public function test_save_page_runs_sql_import_with_network_capability_on_multisite() {

		$page = $this->get_page( 'SqlImport', 'sql-import' );
		$page->shouldReceive( 'save' )->once();

		$manager = new Manager();
		$manager->add_page( $page );

		Functions\when( 'is_multisite' )->justReturn( true );
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'manage_network_options' )
			->andReturn( true );

		$this->save_page( $manager, 'sql-import' );
	}

	public function test_save_page_runs_sql_import_with_manage_options_on_single_site() {

		$page = $this->get_page( 'SqlImport', 'sql-import' );
		$page->shouldReceive( 'save' )->once();

		$manager = new Manager();
		$manager->add_page( $page );

		Functions\when( 'is_multisite' )->justReturn( false );
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'manage_options' )
			->andReturn( true );

		$this->save_page( $manager, 'sql-import' );
	}

	public function test_save_page_denies_users_without_capability_for_other_pages() {

		$page = $this->get_page( 'SearchReplace', 'search-replace' );
		$page->shouldReceive( 'save' )->never();

		$manager = new Manager();
		$manager->add_page( $page );

		Functions\when( 'is_multisite' )->justReturn( true );
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'manage_options' )
			->andReturn( false );
		Functions\expect( 'wp_die' )
			->once()
			->andReturnUsing(
				function () {
					throw new \Exception( 'WP DIE' );
				}
			);

		$this->setExpectedException( '\Exception', 'WP DIE' );

		$this->save_page( $manager, 'search-replace' );
	}

	public function test_register_pages_requires_network_capability_for_sql_import_on_multisite() {

		$manager = new Manager();
		$manager->add_page( $this->get_page( 'SearchReplace', 'search-replace' ) );
		$manager->add_page( $this->get_page( 'SqlImport', 'sql-import' ) );

		Functions\when( 'is_multisite' )->justReturn( true );
		Functions\expect( 'add_submenu_page' )
			->once()
			->with( 'tools.php', \Mockery::any(), \Mockery::any(), 'manage_options', 'search-replace', \Mockery::any() );
		Functions\expect( 'add_submenu_page' )
			->once()
			->with( 'tools.php', \Mockery::any(), \Mockery::any(), 'manage_network_options', 'sql-import', \Mockery::any() );

		$manager->register_pages();
	}
}
