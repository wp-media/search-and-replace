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
	public function test_user_can_access( $user_can, $assert ){

		Brain\Monkey::filters()->expectApplied( 'search_replace_access_capability' )
						->andReturn( 'manage_options' );

		Brain\Monkey::functions()
		            ->expect( 'current_user_can' )
		            ->with( (string) 'manage_options' )
		            ->andReturn( (string) $user_can );

		$object = new SearchReplace\Load();

		$this->assertSame(
			$assert,
			$this->invokeMethod( $object, 'user_can_access' )
		);

	}

	/**
	 * @return array
	 */
	public function test_user_can_access_data() {

		$data = [
			'user_with_well_cap_can_access' => [
				(bool) TRUE,    // $user_can
				(bool) TRUE     // $assert
			],
			'admins_can_access' => [
				(bool) FALSE,  // $user_can
				(bool) FALSE    // $assert
			]

		];

		return $data;
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object $object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	private function invokeMethod( &$object, $methodName, array $parameters = array() ) {
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}
}
