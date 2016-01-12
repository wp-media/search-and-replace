<?php
/**
 *  User: S Hinse
 * Date: 20.10.2015
 * Time: 11:24
 */

use Inpsyde\searchReplace;
use Inpsyde\searchReplace\Sample;

class SampleTest extends PHPUnit_Framework_TestCase {

	public function setUp() {

		\WP_Mock::setUp();
	}

	public function tearDown() {

		\WP_Mock::tearDown();
	}



	public function test_returnSum() {

		$mySample = new Sample();
		//this should pass
		$result = $mySample->returnSum( 3, 4 );
		$this->assertEquals( $result, 7 );
	}

	//this should fail
	public function test_returnProduct() {

		$result = Sample::returnProduct( 3, 4 );
		$this->assertEquals( $result, 11 );

	}

	public function test_uses_get_post() {
		$testee = new Sample();

		global $post;
		$post               = new \stdClass;
		$post->ID           = 42;
		$post->special_meta = '<p>Hallo Welt</p>';

		\WP_Mock::wpFunction( 'get_post', array(
			'times'  => 1,
			'args'   => array( $post->ID ),
			'return' => $post
		) );

		$results = $testee->special_the_content ('<p>some content</p>');

		$this ->assertEquals('<p>some content</p><p>Hallo Welt</p>', $results);

	}
}