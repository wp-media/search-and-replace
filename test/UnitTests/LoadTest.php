<?php
namespace Inpsyde\SearchReplace\Tests;

use
	Inpsyde\SearchReplace,
	Brain,
	Mockery,
	MonkeryTestCase;


/**
 * Class Load handle plugin loading
 *
 * @since 3.1.1
 *
 * @package Inpsyde\SearchReplace
 */
class LoadTest extends MonkeryTestCase\BrainMonkeyWpTestCase {

	/**
	 * @dataProvider test_user_can_access_data
	 */
	public function test_user_can_access( $is_admin, $user_can, $assert ){

		Brain\Monkey::filters()->expectApplied( 'search_replace_access_capability' )
						->andReturn( 'manage_options' );

		Brain\Monkey::functions()
		            ->expect( 'is_admin' )
		            ->andReturn( $is_admin );


		Brain\Monkey::functions()
		            ->expect( 'current_user_can' )
		            ->with( 'manage_options' )
		            ->andReturn( $user_can );

		$load = new SearchReplace\Load();

		$this->assertSame(
			$assert,
			$this->invokeMethod( $load, 'user_can_access' )
		);

	}

	/**
	 * @return array
	 */
	public function test_user_can_access_data() {

		$data = [
			'user_with_well_cap_can_access' => [
				(bool) FALSE,   // $is_admin
				(bool) TRUE,    // $user_can
				(bool) TRUE     // $assert
			],
			'admins_can_access' => [
				(bool) TRUE,   // $is_admin
				(bool) FALSE,  // $user_can
				(bool) TRUE    // $assert
			],
			'no_one_can_access' => [
				(bool) FALSE,   // $is_admin
				(bool) FALSE,   // $user_can
				(bool) FALSE    // $assert
			]

		];

		return $data;
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	private function invokeMethod(&$object, $methodName, array $parameters = array()) {
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}
}
