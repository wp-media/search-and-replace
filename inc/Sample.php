<?php
/**
 *  User: S Hinse
 * Date: 20.10.2015
 * Time: 10:58
 */

namespace Inpsyde\searchReplace;

/**
 * Class Sample
 *
 * A Class to play around with PHPunittests, has no other function
 * @package Inpsyde\searchReplace
 */
class Sample {
	public static function returnProduct ( $a, $b) {
		return $a * $b;
	}

	public  function returnSum ($a, $b) {
		return $a + $b;
	}

	public function special_the_content($content) {
		$the_post = \get_post(42);
		$sp = $the_post->special_meta;
		$sp = $content.$sp;

		return $sp;
	}
}