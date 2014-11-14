<?php
/**
 * This file contains the definition of the Promotions Config
 *
 * @since 1.0.0
 * @package EE Promotions
 * @subpackage config
 */
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }

/**
 * Class defining the Promotions Config object stored on EE_Registry::instance->CFG
 *
 * @since 1.0.0
 *
 * @package EE4 Promotions
 * @subpackage config
 * @author Darren Ethier
 */
class EE_Promotions_Config extends EE_Config_Base {

	/**
	 * Holds all the EE_Promotion_Scope objects that are registered for promotions.
	 *
	 * @since 1.0.0
	 * @var EE_Promotion_Scope[]
	 */
	public $scopes;

	/**
	 * what to call promo codes on the frontend. ie: Promo codes, coupon codes, etc
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $public_name_singular;
	/**
	 * @var string
	 */
	public $public_name_plural;
	/**
	 * @var string
	 */
	public $public_short_name_singular;
	/**
	 * @var string
	 */
	public $public_short_name_plural;



	/**
	 * 	constructor
	 * @return EE_Promotions_Config
	 */
	public function __construct() {
		$this->scopes = $this->_get_scopes();
		$this->public_name_singular = apply_filters( 'FHEE__EE_Promotions_Config____construct__public_name_singular', __( 'Promotion Code', 'event_espresso' ));
		$this->public_name_plural = apply_filters( 'FHEE__EE_Promotions_Config____construct__public_name_plural', __( 'Promotion Codes', 'event_espresso' ));
		$this->public_short_name_singular = apply_filters( 'FHEE__EE_Promotions_Config____construct__public_short_name_singular', __( 'Promo Code', 'event_espresso' ));
		$this->public_short_name_plural = apply_filters( 'FHEE__EE_Promotions_Config____construct__public_short_name_plural', __( 'Promo Codes', 'event_espresso' ));
	}



	/**
	 * 	_get_scopes
	 * @return array
	 */
	private function _get_scopes() {
		$scopes = array();
		//first we require the promotion scope parent.
		require_once( EE_PROMOTIONS_PATH . 'lib/scopes/EE_Promotion_Scope.lib.php');
		$scopes_to_register = apply_filters( 'FHEE__EE_Promotions_Config___get_scopes__scopes_to_register', glob( EE_PROMOTIONS_PATH.'lib/scopes/*.lib.php' ) );
		foreach ( $scopes_to_register as $scope ) {
			$class_name = EEH_File::get_classname_from_filepath_with_standard_filename( $scope );
			//if parent let's skip - it's already been required.
			if ( $class_name == 'EE_Promotion_Scope' )
				continue;
			require_once $scope;

			if ( class_exists( $class_name ) )
				$reflector = new ReflectionClass( $class_name );
				$sp = $reflector->newInstance();
				$scopes[ $sp->slug ] = $sp;
		}
		return $scopes;
	}



	public function __wakeup() {
		$this->scopes = $this->_get_scopes();
	}



	/**
	 * Use to designate what properties get serialized with object.
	 *
	 * @since 1.0.0
	 * @return array   Values represent properties to serialize
	 */
	public function __sleep() {
		return array();
	}

}



// End of file EE_Promotions_Config.php
// Location: /wp-content/plugins/espresso-promotions/EE_Promotions_Config.php
