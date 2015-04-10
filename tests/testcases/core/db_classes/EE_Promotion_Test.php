<?php
/**
 * Contains test class for EE_Promotion_Test.php
 *
 * @since  		1.0.0
 * @package 		EE4 Promotions
 * @subpackage 	tests
 */


/**
 * Test class for EE_Promotion_Test.php
 *
 * @since 		1.0.0
 * @package 		EE4 Promotions
 * @subpackage 	tests
 */
class EE_Promotion_Test_tests extends EE_Promotions_UnitTestCase {


	/**
	 * @since 1.0.0
	 */
	public function test_status() {

		foreach ( $this->_demo_promotions() as  $expected_test =>  $promotion ) {

			switch ( $expected_test ) {
				case 'upcoming_start_no_end' :
					$this->assertEquals( EE_Promotion::upcoming, $promotion->status() );
					break;
				case 'upcoming_start_upcoming_end' :
					$this->assertEquals( EE_Promotion::upcoming, $promotion->status() );
					break;
				case 'past_start_no_end' :
					$this->assertEquals( EE_Promotion::active, $promotion->status() );
					break;
				case 'past_start_upcoming_end' :
					$this->assertEquals( EE_Promotion::active, $promotion->status() );
					break;
				case 'past_start_past_end' :
					$this->assertEquals( EE_Promotion::expired, $promotion->status() );
					break;
				case 'no_start_upcoming_end' :
					$this->assertEquals( EE_Promotion::active, $promotion->status() );
					break;
				case 'no_start_past_end' :
					$this->assertEquals( EE_Promotion::expired, $promotion->status() );
					break;
			}
		}

	}




	/**
	 * @since 1.0.0
	 */
	public function test_promotion_date_range() {
		$full_day_promo = EE_Promotion::new_instance( array(
			'PRO_start' => '2015-12-24 00:00:00',
			'PRO_end' => '2015-12-25 00:00:00'
			), 'America/Toronto', array( 'Y-m-d',  'H:i:s' ) );
		$multiple_day_promo = EE_Promotion::new_instance( array(
			'PRO_start' => '2015-12-24 00:00:00',
			'PRO_end' => '2015-12-26 00:00:00'
			), 'America/Toronto', array( 'Y-m-d',  'H:i:s' ) );
		$null_start_promo = EE_Promotion::new_instance( array(
			'PRO_end' => '2015-12-25 00:00:00'
			), 'America/Toronto', array( 'Y-m-d',  'H:i:s' ) );
		$null_end_promo = EE_Promotion::new_instance( array(
			'PRO_start' => '2015-12-24 00:00:00',
			), 'America/Toronto', array( 'Y-m-d',  'H:i:s' ) );

		$this->assertEquals( '2015-12-24', $full_day_promo->promotion_date_range() );
		$this->assertEquals( '2015-12-24 00:00:00 - 2015-12-26 00:00:00', $multiple_day_promo->promotion_date_range() );
		$this->assertEquals( 'Ends: 2015-12-25 00:00:00', $null_start_promo->promotion_date_range() );
		$this->assertEquals( 'Starts: 2015-12-24 00:00:00', $null_end_promo->promotion_date_range() );
	}
}
