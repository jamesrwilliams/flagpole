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

	/**
	 * Example test function.
	 */
	public function test_one() {
		$this->assertFalse( flagpole_flag_enabled( 'foo' ) );
	}
}
