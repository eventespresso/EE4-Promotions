<?php
/**
 * Contains test class for espresso_promotions.php
 *
 * @since  		1.0.0
 * @package 		EE4 Promotions
 * @subpackage 	tests
 */


/**
 * Test class for espresso_promotions.php
 *
 * @since 		1.0.0
 * @package 		EE4 Promotions
 * @subpackage 	tests
 */
class espresso_promotions_tests extends EE_UnitTestCase {

	/**
	 * Tests the loading of the main promotions file
	 *
	 * @since 1.0.0
	 */
	function test_loading_promotions() {
		$this->assertEquals( has_action('AHEE__EE_System__load_espresso_addons', 'load_espresso_promotions'), 5 );
		$this->assertTrue( class_exists( 'EE_Promotions' ) );
	}
}
