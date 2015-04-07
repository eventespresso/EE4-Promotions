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
class EE_Promotion_Test_tests extends EE_UnitTestCase {


	/**
	 * @since 1.0.0
	 */
	public function test_status() {

		$promotions_to_test = array(
			'upcoming_start_no_end' => EE_Promotion::new_instance( array( 'PRO_start' => time() + 86400 ) ),
			'upcoming_start_upcoming_end' => EE_Promotion::new_instance( array( 'PRO_start' => time() + 86400, 'PRO_end' => time() + 96000 ) ),
			'past_start_no_end' => EE_Promotion::new_instance( array( 'PRO_start' => time() - 86400 ) ),
			'past_start_upcoming_end' => EE_Promotion::new_instance( array( 'PRO_start' => time() - 86400, 'PRO_end' => time() + 96000 ) ),
			'past_start_past_end' => EE_Promotion::new_instance( array( 'PRO_start' => time() - 86400, 'PRO_end' => time() + -56000 ) ),
			'no_start_upcoming_end' => EE_Promotion::new_instance( array( 'PRO_end' => time() + 96000 ) ),
			'no_start_past_end' => EE_Promotion::new_instance( array( 'PRO_end' => time() - 96000 ) )
			);

		foreach ( $promotions_to_test as $expected_test =>  $promotion ) {
			$promotion->save();

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
}
