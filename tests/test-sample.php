<?php
/**
 * Class SampleTest
 *
 * @package feature-flags
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	public function test_one() {
		$this->assertFalse( is_enabled( 'foo' ) );
	}
}
