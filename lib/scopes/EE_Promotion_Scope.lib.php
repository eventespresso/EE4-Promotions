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
		$this->_set_main_properties_and_hooks();
		$this->_verify_properties_set();

		//common ajax for admin
		add_action('wp_ajax_promotion_scope_items', array( $this, 'ajax_get_applies_to_items_to_select'), 10 );

		//hook into promotion details insert/update method
		add_action( 'AHEE__Promotions_Admin_Page___insert_update_promotion__after', array( $this, 'handle_promotion_update' ), 10, 2 );
	}



	/**
	 * Child classes use this to set the main properties and any hooks for the scope class.
	 * Main properties that should be set are: $label, $slug, and $_model_name.  This method is
	 * called when scopes are instantiated and added to the EE_Registry::instance()->CFG->
	 * addons->promotions->scopes property.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	abstract protected function _set_main_properties_and_hooks();





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
	 * Returns query args for use in model queries on the
	 * EEM model related to scope.  Note this also should
	 * consider any filters present.
	 *
	 * @since 1.0.0
	 * @see EEM_Base::get_all() for documentation related to
	 *      	 what $query_args can be used.
	 *
	 * @return array of query args
	 */
	abstract public function get_query_args();




	/**
	 * This returns the selector for this scope that is used in the promotions details page.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $PRO_ID The promotion ID for the applies to selector we are retreiving.
	 * @return string html content.
	 */
	abstract public function get_admin_applies_to_selector( $PRO_ID );



	/**
	 * This is the callback for the AHEE__Promotions_Admin_Page___insert_update_promotion__after action which makes sure scope applies to are attached properly to the promotion.
	 *
	 * @since   1.0.0
	 *
	 * @param EE_Promotion $promotion the updated/inserted EE_Promotion object.
	 * @param array              $data the incoming form data.
	 * @return void
	 */
	abstract public function handle_promotion_update( EE_Promotion $promotion, $data );





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
	 * Returns a total count of items matching the
	 * query_args.
	 *
	 * @since 1.0.0
	 *
	 * @param  array     $query_args array of query args to
	 *                               		   filter the count by.
	 * @return int                 	   count of items.
	 */
	protected function _get_total_items() {
		$query_args = $this->get_query_args();
		return $this->_model()->count( $query_args, NULL, TRUE );
	}





	/**
	 * This returns an array of EE_Base_Class items for the
	 * scope.
	 *
	 * @since 1.0.0
	 *
	 * @param  boolean   $paging     whether to include any paging paramaters that might be in the _REQUEST or not.
	 * @return  EE_Base_Class[]
	 * @access protected
	 */
	public function get_scope_items( $paging = TRUE ) {
		$query_args = $this->get_query_args();
		if ( $paging ) {
			$current_page = !empty( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;
			$per_page = !empty( $_REQUEST['perpage'] ) ? $_REQUEST['perpage'] : 20;
			$offset = ( $current_page -1 ) * $per_page;
			$query_args['limit'] = array( $offset, $per_page );
		}

		//only display selected items toggle set?
		if ( !empty( $_REQUEST['PRO_display_only_selected'] ) ) {
			$selected_items = ! empty( $_REQUEST['selected_items'] ) ? explode(',',$_REQUEST['selected_items'] ) : array();
			if ( !empty( $selected_items ) ) {
				$query_args[0][$this->_model()->primary_key_name()] = array('IN', $selected_items);
			}
		}
		return $this->_model()->get_all( $query_args );
	}





	/**
	 * Generates an array of obj_ids for the EE_Base_Class objects related to this scope that the promotion matching the given ID is applied to.
	 *
	 * @since 1.0.0
	 *
	 * @param  int    $PRO_ID Promotion that is applied.
	 * @return  array               array of ids matching the items related to the scope.
	 */
	protected function _get_applied_to_items( $PRO_ID ) {
		$selected = array();
		//with the PRO_ID we can get the PRO_OBJ items related to this scope.
		$PRO_OBJs = EEM_Promotion_Object::instance()->get_all( array( array( 'PRO_ID' => $PRO_ID, 'POB_type' => $this->slug ) ) );
		foreach( $PRO_OBJs as $PRO_OBJ ) {
			$selected[] = $PRO_OBJ->OBJ_ID();
		}
		return $selected;
	}



	/**
	 * This is the ajax callback for modifying the applies_to_items_to_select results displayed
	 * in the dom.
	 * It may be called when new filters are requested for results or when paging is used.
	 *
	 * @since  1.0.0
	 *
	 * @return json json object with results.
	 */
	public function ajax_get_applies_to_items_to_select() {
		$selected_items = ! empty( $_REQUEST['selected_items'] ) ? explode( ',', $_REQUEST['selected_items'] ) : array();
		$requested_items = $this->get_scope_items();

		$response['content'] = ! empty( $requested_items ) ? $this->_get_applies_to_items_to_select( $requested_items, $selected_items ) : __('<ul class="promotion-applies-to-items-ul"><li>No results for the given query</li></ul>', 'event_espresso');
		$response['success'] = TRUE;
		// make sure there are no php errors or headers_sent.  Then we can set correct json header.
		if ( NULL === error_get_last() || ! headers_sent() )
			header('Content-Type: application/json; charset=UTF-8');

		echo json_encode( $response );
		exit();
	}




	/**
	 * By default promotion scopes can use this method to return a list of checkboxes for
	 * selecting what scope items are applied to the promotion.  Scopes can override this
	 * however they want tho.
	 *
	 * @since 1.0.0
	 * @throws EE_Error
	 *
	 * @param  EE_Base_Class[]    		$items_to_select
	 * @param  array    $selected_items  	Should be an array of existing applied to items IDs (
	 *                                    		EE_Base_Class ids)
	 * @return string                     		unordered list of checkboxes.
	 */
	protected function _get_applies_to_items_to_select( $items_to_select, $selected_items ) {
		$selected_items = (array) $selected_items;
		//verification
		if ( empty( $items_to_select ) || ! is_array( $items_to_select) || ! $items_to_select[key($items_to_select)] instanceof EE_Base_Class )
			return sprintf( __('There are no active %s to assign to this scope.  You will need to create some first.', 'event_espresso'), $this->label->plural );
		$checkboxes = '<ul class="promotion-applies-to-items-ul">';
		foreach( $items_to_select as $id => $obj ) {
			$checked = in_array($id, $selected_items) ? ' checked=checked' : '';
			$checkboxes .= '<li><input type="checkbox" id="PRO_applied_to_selected['.$id.']" name="PRO_applied_to_selected['.$id.']" value="' . $id . '"'. $checked . '>';
			$checkboxes .= '<label class="pro-applied-to-selector-checkbox-label" for="PRO_applied_to_selected['.$id.']">' . $this->name($obj) . '</label>';
		}
		$checkboxes .= '</ul>';
		return $checkboxes;
	}




	/**
	 * Get paging for the selector
	 *
	 * @since 1.0.0
	 *
	 * @param int     $total_items count of total items retrieved in the query.
	 * @return string Paging html
	 */
	protected function _get_applies_to_items_paging( $total_items ) {
		EE_Registry::instance()->load_helper('Template');
		$perpage_default = apply_filters( 'FHEE__EE_Promotion_Scope___get_applies_to_items_paging__perpage_default', 10 );
		$current_page = isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;
		$perpage = isset( $_REQUEST['perpage'] ) ? $_REQUEST['perpage'] : $perpage_default;
		$url = isset( $_REQUEST['redirect_url'] ) ? $_REQUEST['redirect_url'] : $_SERVER['REQUEST_URI'];
		return EEH_Template::get_paging_html( $total_items, $current_page, $perpage, $url  );
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
