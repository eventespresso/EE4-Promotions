<?php
/**
 * This file contains the abstract class for EE_Promotion scopes
 *
 * @since 1.0.0
 * @package EE Promotions
 * @subpackage models
 */
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }

/**
 * Abstract class for EE_Promotion scopes
 *
 * @since 1.0.0
 *
 * @package EE4 Promotions
 * @subpackage models
 * @author Darren Ethier
 */
abstract class EE_Promotion_Scope {

	/**
	 * Localized Labels for this scope.
	 * stdClass that has a singular and plural property.  Set via the _set_main_properties()
	 * method.
	 *
	 * @since 1.0.0
	 * @var stdClass
	 */
	public $label;



	/**
	 * Slug used to identify the scope in the system.
	 * The Promotion db will use this slug to reference the scope in the db and to know
	 * which scope object to instantiate.  This should correspond to the model name for
	 * the related model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $slug;




	/**
	 * This is a cache of all the EE_Base_Class model objects related to this scope that have
	 * been retrieved from the db and are in use.  The are indexed by the model object ID().
	 *
	 * @since 1.0.0
	 * @var EE_Base_Class[]
	 */
	protected $_model_objects;



	/**
	 * Setup the basic structure of the scope class.
	 *
	 * @since 1.0.0
	 *
	 * @return \EE_Promotion_Scope
	 */
	public function __construct() {
		$this->label = new stdClass();
		$this->_set_main_properties();
		$this->_verify_properties_set();

		//todo this would be a good place to hook into (not existing yet) a filter for the EEM_Base_Model relations
	}



	/**
	 * Child classes use this to set the main properties for the scope class.
	 * Main properties are: $label, $slug, and $_model_name.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	abstract protected function _set_main_properties();





	/**
	 * Child scope classes indicate what gets returned when a "name" is requested.
	 *
	 * @since 1.0.0
	 *
	 * @param int|EE_Base_Class    $OBJ_ID ID or model object for the EE_Base_Class object being
	 *                                     		  utilized.
	 * @return string
	 */
	abstract public function name( $OBJ_ID );




	/**
	 * Child scope classes indicate what gets returned when a "description" is requested.
	 *
	 * @since 1.0.0
	 *
	 * @param  int|EE_Base_Class   $OBJ_ID   ID or model object for the EE_Base_Class object being
	 *                                       		    utilized
	 * @return string
	 */
	abstract public function description( $OBJ_ID );




	/**
	 * Child scope classes indicate what gets returned when the admin_url is requested.
	 * Admin url usually points to the details page for the given id.
	 *
	 * @since 1.0.0
	 *
	 * @param  int   $OBJ_ID   ID for the EE_Base_Class object being utilized
	 * @return string
	 */
	abstract public function get_admin_url( $OBJ_ID );




	/**
	 * Child scope classes indicate what gets returned when the frontend_url is requested.
	 * Frontend url usually points to the single page view for the given id.
	 *
	 * @since 1.0.0
	 *
	 * @param  int   $OBJ_ID   ID for the EE_Base_Class object being utilized
	 * @return string
	 */
	abstract public function get_frontend_url( $OBJ_ID );





	/**
	 * Child scope classes use this to return an array of EE_Base_Class objects related to the scope
	 * Note that this is a convenience method when the promotion needs an array of objects
	 * related to the scope without knowing what those objects are.  A use case example is
	 * generating a list of checkboxes for assigning specific items to the applied scope.
	 *
	 * @param  array $limit   this is so that paging can be provided.  Expected value should be an
	 *                       	    array like array( 1,23 ).
	 * @return EE_Base_Class[]
	 */
	abstract public function get_scope_items( $limit = array() );





	/**
	 * This just returns the related model instance as set via the $_slug property.
	 *
	 * @since 1.0.0
	 *
	 * @return  EEM_Base
	 */
	protected function _model() {
		$model = EE_Registry::instance()->load_model( $this->slug );

		//let's verify the model is an instance of the correct model for the slug.
		$expected_model_class = 'EEM_' . $this->slug;
		if ( ! $model instanceof $expected_model_class )
			throw new EE_Error( sprintf( __( 'The loading of a corresponding model for %s failed because there is not a %s instance available.', 'event_espresso' ), get_class($this), $expected_model_class ) );
		return $model;
	}



	/**
	 * This is used to set the model object on the $_model_objects property
	 *
	 * @since 1.0.0
	 *
	 * @param  EE_Base_Class $obj
	 * @return  void
	 */
	protected function _set_model_object( EE_Base_Class $obj ) {
		$this->_model_objects[$obj->ID()] = $obj;
	}




	/**
	 * Gets the model object for the given ID for the scope.
	 * If it isn't cached in $_model_objects then will use the model() method to retrieve the
	 * model object for the given id and then cache it to the $_model_objects property.
	 *
	 * @since 1.0.0
	 * @throws EE_Error If $OBJ_ID does not correspond to a valid model object.
	 *
	 * @param  int     $OBJ_ID ID 	for the object to be retrieved.
	 * @param  bool  $reset_cache Optional. If client wants to reset cache then set to true.
	 *                            		Default false.
	 * @return  EE_Base_Class
	 */
	protected function _get_model_object( $OBJ_ID, $reset_cache = FALSE ) {
		//first check if in cache (and if cache reset not requested.)
		if ( ! empty( $this->_model_objects[$OBJ_ID] ) && ! $reset_cache )
			return $this->_model_objects[$OBJ_ID];

		//attempt to retrieve model object!
		$obj = $this->_model()->get_one_by_ID( $OBJ_ID );

		//verification that EE_Base_Class is of the expected instance.
		$expected_class = 'EE_' . $this->slug;

		if ( ! $obj instanceof $expected_class ) {
			throw new EE_Error(
				sprintf(
					__( 'Unable to retrieve the model object related to the %s with this id: %s.  Maybe it was deleted from the db and the promotion got orphaned.', 'event_espresso' ),
					get_class( $this ),
					$OBJ_ID
				)
			);
		}

		//set to cache
		$this->_set_model_object( $obj );

		//return
		return $obj;
	}



	/**
	 * This verifies that the necessary properties for this class have been set.
	 *
	 * @since 1.0.0
	 * @throws EE_Error
	 */
	private function _verify_properties_set() {
		$classname = get_class( $this );
		//verify label is set and is std_object with two properties, singular and plural.
		if ( ! is_object( $this->label ) || ! isset( $this->label->singular ) || ! isset( $this->label->plural ) )
			throw new EE_Error( sprintf( __('The %s class has not set the $label property correctly.', 'event_espresso'), $classname ) );

		if ( empty( $this->slug ) )
			throw new EE_Error( sprintf( __( 'The %s class has not set the $slug property.  This is used as a identifier for this scope and is necessary.', 'event_espresso'), $classname ) );
	}

}
