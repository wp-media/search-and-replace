<?php


use Inpsyde\SearchReplace\inc\Autoloader;

class AutoloaderTest extends \PHPUnit_Framework_TestCase {

	/**
	 *expected: return false
	 */
	function test_file_does_not_belong_to_namespace() {

		$namespace = "Inpsyde";

		//this class has not "Inpsyde" in its path
		$class = "foo";

		$extension = "php";
		$path      = "/";

		$testee = new Autoloader( $namespace, $path, $extension );
		$result = $testee->autoload( $class );

		$this->assertEquals( FALSE, $result );

	}

	/**
	 * A non-existent Class with correct namespace is called
	 *
	 * @expectedException Exception
	 */
	function test_file_does_not_exist() {

		$namespace = "Inpsyde";
		//this class has "Inspyde" in its path, but does not exist
		$class = "Inpsyde\\foo";

		$extension = "php";
		$path      = "/";

		$testee = new Autoloader( $namespace, $path, $extension );
		$result = $testee->autoload( $class );

		$this->assertEquals( FALSE, $result );

	}

}
