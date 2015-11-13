<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }
/**
 * EE_Promotions_Config
 *
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
	 * @type EE_Promotion_Scope[] $scopes
	 */
	public $scopes = array();

	/**
	 * what to call promo codes on the frontend. ie: Promo codes, coupon codes, etc
	 *
	 * @since 1.0.0
	 * @type stdClass $label
	 */
	public $label;

	/**
	 * @type string $banner_template
	 */
	public $banner_template = 'promo-banner-ribbon.template.php';

	/**
	 * @type string $ribbon_banner_color
	 */
	public $ribbon_banner_color = 'lite-blue';

	/**
	 * @type boolean $_affects_tax
	 */
	protected $_affects_tax = false;



	/**
	 * 	constructor
	 * @return EE_Promotions_Config
	 */
	public function __construct() {
		add_action( 'AHEE__EE_Config___load_core_config__end', array( $this, 'init' ));
	}



	/**
	 * 	init
	 * @return void
	 */
	public function init() {
		static $initialized = false;
		if ( $initialized ) {
			return;
		}
		$this->scopes = $this->_get_scopes();
		$this->label = new stdClass();
		$this->label->singular = apply_filters( 'FHEE__EE_Promotions_Config____construct__label_singular', __( 'Promotion Code', 'event_espresso' ));
		$this->label->plural = apply_filters( 'FHEE__EE_Promotions_Config____construct__label_plural', __( 'Promotion Codes', 'event_espresso' ));
	}



	/**
	 * 	_get_scopes
	 * @return array
	 */
	private function _get_scopes() {
		static $scopes = array();
		$scopes_to_register = apply_filters( 'FHEE__EE_Promotions_Config___get_scopes__scopes_to_register', glob( EE_PROMOTIONS_PATH.'lib/scopes/*.lib.php' ) );
		foreach ( $scopes_to_register as $scope ) {
			$class_name = EEH_File::get_classname_from_filepath_with_standard_filename( $scope );
			// if parent let's skip - it's already been required.
			if ( $class_name == 'EE_Promotion_Scope' ) {
				continue;
			}
			$loaded = require_once( $scope );
			// avoid instantiating classes twice by checking whether file has already been loaded
			// ( first load returns (int)1, subsequent loads return (bool)true )
			if ( $loaded === 1 ) {
				if ( class_exists( $class_name ) ) {
					$reflector = new ReflectionClass( $class_name );
					$sp = $reflector->newInstance();
					$scopes[ $sp->slug ] = $sp;
				}
			}
		}
		return $scopes;
	}



	/**
	 * __wakeup
	 */
	public function __wakeup() {
		$this->init();
	}



	/**
	 * @return boolean
	 */
	public function affects_tax() {
		return $this->_affects_tax;
	}



	/**
	 * @param boolean $affects_tax
	 */
	public function set_affects_tax( $affects_tax ) {
		$this->_affects_tax = filter_var( $affects_tax, FILTER_VALIDATE_BOOLEAN );
	}



}
// End of file EE_Promotions_Config.php
// Location: /wp-content/plugins/espresso-promotions/EE_Promotions_Config.php