<?php
/**
 * Contains test class for EEM_Promotion_test.php
 *
 * @since  		1.0.0
 * @package 		EE4 Promotions
 * @subpackage 	tests
 */


/**
 * Test class for EEM_Promotion_test.php
 *
 * @since 		1.0.0
 * @package 		EE4 Promotions
 * @subpackage 	tests
 */
class EEM_Promotion_Test extends EE_Promotions_UnitTestCase {



	/**
	 * @since 1.0.0
	 *
	 */
	public function test_get_promotion_details_via_code() {
		$promotions = $this->_demo_promotions();
		reset( $promotions );
		next( $promotions );
		// had to move the code to a valid promotion (active within current date)
		// so now we want promo 3
		$expected_promotion = next( $promotions );
		$actual_promotion = EEM_Promotion::instance()->get_promotion_details_via_code( 'test_code_for_promotions' );
		$this->assertEquals( $expected_promotion->name(), $actual_promotion->name() );
	}




	/**
	 * @since 1.0.0
	 */
	public function test_get_all_active_codeless_promotions() {
		$this->_demo_promotions();
		$active_codeless_promos = EEM_Promotion::instance()->get_all_active_codeless_promotions();
		$this->assertEquals( 2, count( $active_codeless_promos ) );
	}



	/**
	 * @since 1.0.0
	 *
	 */
	public function test_get_upcoming_codeless_promotions() {
		$this->_demo_promotions();
		$upcoming_codeless_promos = EEM_Promotion::instance()->get_upcoming_codeless_promotions();
		$this->assertEquals( 1, count( $upcoming_codeless_promos ) );
	}




	/**
	 * @since 1.0.0
	 *
	 */
	public function test_get_active_and_upcoming_codeless_promotions_in_range() {
		$this->_demo_promotions();
		$upcoming_codeless_promos = EEM_Promotion::instance()->get_active_and_upcoming_codeless_promotions_in_range();
		$this->assertEquals( 3, count( $upcoming_codeless_promos ) );
	}



}

// Location: wp-content/plugins/eea-promotions/tests/testcases/core/db_models/EEM_Promotion_Test.php
