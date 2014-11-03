<?php
/**
 * This file contains the  class for EEI_Plugin_API implementation for registering promotion scopes.
 *
 * @since 1.0.0
 * @package EE Promotions
 * @subpackage plugin api
 */
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }

/**
 * EE_Register_Promotion_Scope
 *
 * @since 1.0.0
 *
 * @package EE Promotions
 * @subpackage plugin api
 * @author Darren Ethier
 */
class EE_Register_Promotion_Scope implements EEI_Plugin_API {


	protected static $_registry;



	/**
	 * Used to register a new promotion scope with the EE Promotions addon.
	 *
	 * @since 1.0.0
	 * @throws EE_Error
	 *
	 * @param string $promotion_id  	A unique ID for this promotion scope.
	 * @param array  $config 		array {
	 *                         A configuration array in the format:
	 *                         @type string $scope_path	A full server path to the EE_Promotion_Scope
	 *                               				child class being registered. Required.
	 *                         @type array $model_extension_paths	@see EE_Register_Model_Extensions
	 *                         @type array $class_extension_paths		@see EE_Register_Model_Extensions
	 * }
	 *
	 * @return void
	 */
	public static function register( $promotion_id = NULL, $config = array() ) {
		//required fields must be present, so let's make sure they are.
		if ( empty( $promotion_id ) ) {
			throw new EE_Error( __('Any client code calling EE_Register_Promotion_Scope must set a unique string for the $promotion_id argument.  None was given.', 'event_espresso') );
		}
		if ( ! is_array( $config ) || empty( $config['scope_path'] ) || empty( $config['model_extension_paths'] ) || empty( $config['class_extension_paths'] ) ) {
			throw new EE_Error( __('In order to register a new promotion scope via EE_Register_Promotion_Scope, the caller must include an array for the configuration that contains the follow keys: "scope_path" (a string containing the full server path to the child class extending EE_Promotion_Scope), "model_extension_paths" (an array of full server paths to folders that contain model extensions), and "class_extension_paths" (an array of full server paths to folders that contain class extensions)', 'event_espresso' ) );
		}

		//check correct loading
		//check correct loading
		if ( ! did_action( 'AHEE__EE_System__load_espresso_addons' ) || did_action( 'AHEE__EE_Admin__loaded' )) {
			EE_Error::doing_it_wrong(
				__METHOD__,
				sprintf(
					__('An attempt was made to register "%s" as a promotion scope has failed because it was not registered at the correct time.  Please use the "AHEE__EE_System__load_espresso_addons" hook at a priority level higher than 5, to register promotion scopes.','event_espresso'),
					$promotion_id
				),
				'1.0.0'
			);
		}

		//set config to registry
		self::$_registry[$promotion_id] = $config;

		//add new scope path to scopes loader
		add_filter( 'FHEE__EE_Promotions_Config___get_scopes__scopes_to_register', array( 'EE_Register_Promotion_Scope', 'register_path' ) );

		//use EE_Register_Model_Extensions with the config.
		unset( $config['scope_path'] );
		EE_Register_Model_Extensions::register( $promotion_id, $config );
	}



	/**
	 * @param array $scope_paths
	 * @return array
	 */
	public static function register_path( $scope_paths = array() ) {
		foreach ( self::$_registry as $config ) {
			$scope_paths[] = $config['scope_path'];
		}
		return $scope_paths;
	}



	/**
	 * @param mixed $promotion_id
	 */
	public static function deregister( $promotion_id = NULL ) {
		if ( isset( self::$_registry[$promotion_id] ) ) {
			unset( self::$_registry[$promotion_id] );
		}
	}
}
