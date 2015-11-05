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
	 * The primary key name for this scope's model
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $_model_pk_name;

	/**
	 * This is a cache of all the EE_Base_Class model objects related to this scope that have
	 * been retrieved from the db and are in use.  The are indexed by the model object ID().
	 *
	 * @since 1.0.0
	 * @var EE_Base_Class[]
	 */
	protected $_model_objects;

	/**
	 * This is the default per page amount when no perpage value is set.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $_per_page;

	/**
	 * used for setting LIN_order
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected static $_counter;



	/**
	 * Setup the basic structure of the scope class.
	 *
	 * @since 1.0.0
	 *
	 * @return \EE_Promotion_Scope
	 */
	public function __construct() {
		$this->init();
	}



	/**
	 * init
	 * Setup the basic structure of the scope class.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		static $initialized = false;
		if ( $initialized ) {
			return;
		}
		EE_Promotion_Scope::$_counter = 1000;
		$this->label = new stdClass();
		$this->_set_main_properties_and_hooks();
		$this->_verify_properties_set();
		$this->set_model_pk_name( $this->_model()->get_primary_key_field()->get_name() );
		//set (and filter ) the per_page default.
		$this->_per_page = apply_filters( 'FHEE__EE_Promotion_Scope___get_applies_to_items_paging__perpage_default', 10, $this->slug );
		//common ajax for admin
		add_action('wp_ajax_promotion_scope_items', array( $this, 'ajax_get_applies_to_items_to_select'), 10 );
		//hook into promotion details insert/update method
		add_action( 'AHEE__Promotions_Admin_Page___insert_update_promotion__after', array( $this, 'handle_promotion_update' ), 10, 2 );
		$initialized = true;
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
	 * @param int|EE_Base_Class 	$OBJ_ID ID or model object for the EE_Base_Class object being utilized
	 * @param bool|string 		$link 	  If false just return name, if 'admin' return name
	 *                               			  wrapped in link to admin details.  If 'front' return
	 *                               			  name wrapped in link to frontend details.
	 * @param int     		$PRO_ID  Optional. Include the promo ID for potential
	 *                           			    downstream code use.
	 * @return string
	 */
	abstract public function name( $OBJ_ID, $link = FALSE, $PRO_ID = 0 );




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
	 * @param integer $PRO_ID The promotion ID for the applies to selector we are retrieving.
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
	 * returns one of the EEM_Line_Item line type constants that should be used when generating a promotion line item for this scope
	 *
	 * @since   1.0.0
	 *
	 * @return string
	 */
	abstract public function get_promotion_line_item_type();



	/**
	 * @return string
	 */
	public function model_pk_name() {
		return $this->_model_pk_name;
	}



	/**
	 * @param string $model_pk_name
	 */
	public function set_model_pk_name( $model_pk_name ) {
		$this->_model_pk_name = $model_pk_name;
	}




	/**
	 * This returns a html span string for the default scope icon.  Child classes can override.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $class_only used to indicate if we only want to return the icon class or the
	 * entire html string.
	 * @return string
	 */
	public function get_scope_icon( $class_only = FALSE ) {
		return $class_only ? 'dashicons dashicons-megaphone' : '<span class="dashicons dashicons-megaphone" title="' . __( 'Promotion', 'event_espresso' ) . '"></span>';
	}




	/**
	 * this returns a styled span for the promo count.
	 *
	 * @since 1.0.0
	 *
	 * @param int $count  the count to display.
	 *
	 * @return string
	 */
	protected function get_promo_count_display( $count = 0 ) {
		return empty( $count ) ? '' :  '<span class="promotion-count-bubble">' . $count . '</span>';
	}



	/**
	 * This just returns the related model instance as set via the $_slug property.
	 *
	 * @since 1.0.0
	 *
	 * @throws EE_Error
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
	 * @param  int     $OBJ_ID ID 	for the object to be retrieved.
	 * @param  bool  $reset_cache Optional. If client wants to reset cache then set to true.
	 * 			Default false.
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
					__( 'Unable to retrieve the model object related to the %s class with this id: %s.  Maybe it was deleted from the db and the promotion got orphaned.', 'event_espresso' ),
					get_class( $this ),
					$OBJ_ID
				)
			);
		}
		//set to cache
		/** @var $obj EE_Base_Class */
		$this->_set_model_object( $obj );
		//return
		return $obj;
	}



	/**
	 * Returns a total count of items matching the
	 * query_args.
	 *
	 * @since    1.0.0
	 * @return int  count of items.
	 */
	protected function _get_total_items() {
		$query_args = $this->get_query_args();
		//make sure for counts we ONLY include the $where_query.
		$_where = array_key_exists( 0, $query_args ) ? array( $query_args[0] ) : array();
		return $this->_model()->count( $_where, null, true );
	}





	/**
	 * This returns an array of EE_Base_Class items for the
	 * scope.
	 *
	 * @since 1.0.0
	 *
	 * @param  boolean   $paging     whether to include any paging parameters that might be in the _REQUEST or not.
	 * @return  EE_Base_Class[]
	 * @access protected
	 */
	public function get_scope_items( $paging = TRUE ) {
		$query_args = $this->get_query_args();
		if ( $paging ) {
			$current_page = !empty( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;
			$per_page = !empty( $_REQUEST['perpage'] ) ? $_REQUEST['perpage'] : $this->_per_page;
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
	 * This returns an array of EE_Base_Class items for the scope.
	 *
	 * @since 1.0.0
	 *
	 * @param  array   $query_args
	 * @return  EE_Base_Class[]
	 * @access protected
	 */
	public function get_items( $query_args = array() ) {
		return $this->_model()->get_all( $query_args );
	}


	/**
	 * Wrapper for the protected _get_applied_to_item_ids.
	 * Use to retrieve the ids of the promotion scope items.
	 *
	 * @param $PRO_ID
	 *
	 * @return array  array of IDs
	 */
	public function get_applied_to_item_ids( $PRO_ID ) {
		return $this->_get_applied_to_item_ids( $PRO_ID );
	}



	/**
	 * Wrapper for the protected _count_applied_to_items method.
	 * Use to retrieve a count of promotion scope items the promotion is applied to..
	 *
	 * @param $PRO_ID
	 *
	 * @return int
	 */
	public function count_applied_to_items( $PRO_ID ) {
		return $this->_count_applied_to_items( $PRO_ID );
	}





	/**
	 * Generates an array of obj_ids for the EE_Base_Class objects related to this scope that the promotion matching the given ID is applied to (or a count of the objects)
	 *
	 * @since 1.0.0
	 * @param  int 	$PRO_ID Promotion that is applied.
	 * @return  array	array of ids matching the items related to the scope.
	 */
	protected function _get_applied_to_item_ids( $PRO_ID ) {
		$query_args = array( array( 'PRO_ID' => $PRO_ID, 'POB_type' => $this->slug ) );
		//with the PRO_ID we can get the PRO_OBJ items related to this scope.
		return EEM_Promotion_Object::instance()->get_col( $query_args, 'OBJ_ID' );
	}



	/**
	 * Returns a count of the objects that the promotion applies to for this scope.
	 *
	 * @since 1.0.0
	 * @param  int 	$PRO_ID Promotion that is applied.
	 * @return  int
	 */
	protected function _count_applied_to_items( $PRO_ID ) {
		$query_args = array( array( 'PRO_ID' => $PRO_ID, 'POB_type' => $this->slug ) );
		return EEM_Promotion_Object::instance()->count( $query_args );
	}



	/**
	 * This is the ajax callback for modifying the applies_to_items_to_select results displayed
	 * in the dom.
	 * It may be called when new filters are requested for results or when paging is used.
	 *
	 * @since  1.0.0
	 * @return string json object with results.
	 */
	public function ajax_get_applies_to_items_to_select() {
		$selected_items = ! empty( $_REQUEST['selected_items'] ) ? explode( ',', $_REQUEST['selected_items'] ) : array();
		$requested_items = $this->get_scope_items();
		$PRO_ID = !empty( $_REQUEST['PRO_ID'] ) ? $_REQUEST['PRO_ID'] : 0;

		//scope items list
		$response['items_content'] = ! empty( $requested_items ) ? $this->_get_applies_to_items_to_select( $requested_items, $selected_items, $PRO_ID ) : sprintf( __( '%sNo results for the given query%s', 'event_espresso' ), '<ul class="promotion-applies-to-items-ul"><li>', '</li></ul>' );

		//paging list
		$total_items = $this->_get_total_items();
		$response['items_paging'] = $this->_get_applies_to_items_paging( $total_items );

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
	 * @param int        $PRO_ID                 EE_Promotion object id. Optional. Default 0.
	 * @return string                     		unordered list of checkboxes.
	 */
	protected function _get_applies_to_items_to_select( $items_to_select, $selected_items, $PRO_ID = 0 ) {
		$selected_items = (array) $selected_items;
		$disabled = '';
		//verification
		if ( empty( $items_to_select ) || ! is_array( $items_to_select) || ! $items_to_select[key($items_to_select)] instanceof EE_Base_Class ) {
			return sprintf( __('There are no active %s to assign to this scope.  You will need to create some first.', 'event_espresso'), $this->label->plural );
		}
		$checkboxes = '<ul class="promotion-applies-to-items-ul">';
		foreach( $items_to_select as $id => $obj ) {
			$checked = in_array($id, $selected_items) ? ' checked=checked' : '';
			//disabled check
			if ( !empty( $PRO_ID ) ) {
				$promo_obj = EEM_Promotion_Object::instance()->get_one( array( array( 'PRO_ID' => $PRO_ID, 'OBJ_ID' => $id ) ) );
				$disabled = $promo_obj instanceof EE_Promotion_Object && $promo_obj->used() > 0 ? ' disabled="disabled"' : '';
			}
			$checkboxes .= '<li><input type="checkbox" id="PRO_applied_to_selected['.$id.']" name="PRO_applied_to_selected['.$id.']" value="' . $id . '" '. $checked . $disabled . '>';
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
		$current_page = isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;
		$perpage = isset( $_REQUEST['perpage'] ) ? $_REQUEST['perpage'] : $this->_per_page;
		$url = isset( $_REQUEST['redirect_url'] ) ? $_REQUEST['redirect_url'] : $_SERVER['REQUEST_URI'];
		return '<span class="spinner"></span>&nbsp;' . EEH_Template::get_paging_html( $total_items, $current_page, $perpage, $url  );
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



	/**
	 * 	add_promotion_objects_for_global_promotions
	 *
	 * 	if a promotion is global but doesn't already have a corresponding EE_Promotion_Object record,
	 *	then this method will create one and add it to the supplied list of $promotion_objects
	 *
	 * @since   1.0.4
	 *
	 * @param \EE_Promotion_Object[] $promotion_objects
	 * @param \EE_Promotion $promotion
	 * @param \EE_Base_Class[] $objects
	 * @return \EE_Promotion_Object[]
	 */
	public function add_promotion_objects_for_global_promotions( $promotion_objects, EE_Promotion $promotion, $objects = array() ) {
		$objects = is_array( $objects ) ? $objects : array( $objects );
		if ( ! empty( $objects ) ) {
			foreach ( $objects as $object ) {
				if (
					$object instanceof EE_Base_Class
					&& $promotion instanceof EE_Promotion
					&& $promotion->is_global()
				) {
					if ( ! empty( $promotion_objects ) ) {
						foreach ( $promotion_objects as $promotion_object ) {
							if (
								$promotion_object instanceof EE_Promotion_Object
								&& $promotion_object->type() == $this->slug
								&& $promotion_object->OBJ_ID() == $object->ID()
							) {
								return $promotion_objects;
							}
						}
					}
					$promotion_obj = EE_Promotion_Object::new_instance(
						array(
							'PRO_ID'   => $promotion->ID(),
							'OBJ_ID'   => $object->ID(),
							'POB_type' => $this->slug,
							'POB_used' => 0
						)
					);
					if ( $promotion_obj->save() ) {
						$promotion_objects[ $promotion_obj->ID() ] = $promotion_obj;
					}
				}
			}
		}
		return $promotion_objects;
	}



	/**
	 * get_redeemable_scope_promos
	 * searches the cart for any items that the supplied promotion applies to.
	 * can be overridden by specific promotion scope classes for greater performance or specificity
	 *
	 * @since   1.0.0
	 *
	 * @param EE_Promotion $promotion
	 * @param bool         $IDs_only  - whether to return array of EE_Promotion_Object IDs or the actual EE_Promotion_Object objects
	 * @param \EE_Base_Class[] $objects
	 * @return array
	 */
	public function get_redeemable_scope_promos( EE_Promotion $promotion, $IDs_only = true, $objects = array() ) {
		$redeemable_scope_promos = array();
		// exceeded global use limit ?
		if ( ! $promotion->global_uses_left() ) {
			return $redeemable_scope_promos;
		}
		$promotion_objects = $this->get_promotion_objects( $promotion, $objects );
		if ( ! empty( $promotion_objects )) {
			foreach ( $promotion_objects as $promotion_object ) {
				if ( $promotion_object instanceof EE_Promotion_Object ) {
					// can the promotion still be be redeemed fro this scope object?
					if ( $promotion->uses_left_for_scope_object( $promotion_object ) > 0 ) {
						// make sure array exists for holding redeemable scope promos
						if ( ! isset( $redeemable_scope_promos[ $this->slug ] )) {
							$redeemable_scope_promos[ $this->slug ] = array();
						}
						$redeemable_scope_promos[ $this->slug ][] = $IDs_only ? $promotion_object->OBJ_ID() : $promotion_object;
					}
				}
			}
		}
		return $redeemable_scope_promos;
	}



	/**
	 * get_promotion_objects
	 * returns an array of EE_Promotion_Object's for the current scope type,
	 * and adds any new ones required for the passed array of objects
	 *
	 * @since   1.0.4
	 *
	 * @param \EE_Promotion   $promotion
	 * @param \EE_Base_Class[] $objects
	 * @return \EE_Promotion_Object[]
	 */
	protected function get_promotion_objects( EE_Promotion $promotion, $objects = array() ) {
		// retrieve promotion objects for this promotion type scope
		$promotion_objects = $promotion->promotion_objects( array( array( 'POB_type' => $this->slug ) ) );
		// check that global promotions cover all events
		return $this->add_promotion_objects_for_global_promotions( $promotion_objects, $promotion, $objects );
	}



	/**
	 * get_redeemable_scope_promos
	 * searches the database for any line items that this promotion applies to
	 *
	 * @since   1.0.0
	 *
	 * @param EE_Line_Item $total_line_item the EE_Cart grand total line item to be searched
	 * @param string $OBJ_type
	 * @param array $OBJ_IDs
	 * @return EE_Line_Item[]
	 */
	protected function get_object_line_items_for_transaction( EE_Line_Item $total_line_item, $OBJ_IDs = array(), $OBJ_type = '' ) {
		/** @type EEM_Line_Item $EEM_Line_Item */
		$EEM_Line_Item = EE_Registry::instance()->load_model( 'Line_Item' );
		$EEM_Line_Item->show_next_x_db_queries();
		return $EEM_Line_Item->get_object_line_items_for_transaction(
			$total_line_item->TXN_ID(),
			empty( $OBJ_type ) ? $this->slug : $OBJ_type,
			$OBJ_IDs
		);
	}



	/**
	 * This determines if there are any saved filters for the given Promotion ID and if needed will overload the
	 * $_REQUEST global for those filter values for use elsewhere in the promotion ui.
	 *
	 * @since 1.0.0
	 *
	 * @param int $PRO_ID
	 * @return bool true when there were saved filters, false when not.
	 */
	protected function _maybe_overload_request_with_saved_filters( $PRO_ID = 0 ) {
		//any saved filters (only on non-ajax requests)?
		if ( ! empty( $PRO_ID ) && ! defined( 'DOING_AJAX') ) {
			$set_filters = EEM_Extra_Meta::instance()->get_one(
				array(
					0 => array(
						'OBJ_ID' => $PRO_ID,
						'EXM_type' => 'Promotion',
						'EXM_key' => 'promo_saved_filters'
					)
				)
			);

			$set_filters = $set_filters instanceof EE_Extra_Meta ? $set_filters->get( 'EXM_value' ) : array();

			//overload $_REQUEST global
			foreach ( $set_filters as $filter_key => $filter_value ) {
				if ( $filter_value ) {
					$_REQUEST[$filter_key] = $filter_value;
				}
			}
			if ( ! empty( $set_filters ) ) {
				return true;
			}
		}
		return false;
	}



	/**
	 * get_object_line_items_from_cart
	 * searches the line items for any objects that this promotion applies to
	 *
	 * @since   1.0.0
	 *
	 * @param EE_Line_Item  $total_line_item the EE_Cart grand total line item to be searched
	 * @param array         $redeemable_scope_promos
	 * @param string        $OBJ_type
	 * @return \EE_Line_Item[]
	 */
	public function get_object_line_items_from_cart( EE_Line_Item $total_line_item, $redeemable_scope_promos = array(), $OBJ_type = '' ) {
		EE_Registry::instance()->load_helper( 'Line_Item' );
		$applicable_items = array();
		$OBJ_type = empty( $OBJ_type ) ? $this->slug : $OBJ_type;
		// check that redeemable scope promos for the requested type exist
		if ( isset( $redeemable_scope_promos[ $OBJ_type ] )) {
			$object_type_line_items = EEH_Line_Item::get_line_items_by_object_type_and_IDs(
				$total_line_item,
				$OBJ_type,
				$redeemable_scope_promos[ $OBJ_type ]
			);
			if ( is_array( $object_type_line_items ) ) {
				foreach ( $object_type_line_items as $object_type_line_item ) {
					if (
						apply_filters( 'FHEE__EE_Promotion_Scope__get_object_line_items_from_cart__is_applicable_item', true, $object_type_line_item )
					) {
						$applicable_items[ ] = $object_type_line_item;
					}
				}
			}
		}
		return $applicable_items;
	}



	/**
	 * get_redeemable_scope_promos
	 * searches the cart for any items that this promotion applies to
	 *
	 * @since   1.0.0
	 *
	 * @param EE_Line_Item $parent_line_item the line item to create the new promotion line item under
	 * @param EE_Promotion $promotion        the promotion object that the line item is being created for
	 * @param string       $promo_name
	 * @param bool         $affects_tax
	 * @return \EE_Line_Item
	 * @throws \EE_Error
	 */
	public function generate_promotion_line_item( EE_Line_Item $parent_line_item, EE_Promotion $promotion, $promo_name = '', $affects_tax = false ) {
		// verify EE_Line_Item
		if ( ! $parent_line_item instanceof EE_Line_Item ) {
			throw new EE_Error( __( 'A valid EE_Line_Item object is required to generate a promotion line item.', 'event_espresso' ));
		}
		// verify EE_Promotion
		if ( ! $promotion instanceof EE_Promotion ) {
			throw new EE_Error( __( 'A valid EE_Promotion object is required to generate a promotion line item.', 'event_espresso' ));
		}
		$promo_name = ! empty( $promo_name ) ? $promo_name : $promotion->name();
		$promo_desc = $promotion->price()->desc();
		$promo_desc .= $promotion->code() != '' ? ' ( ' . $promotion->code() . ' )' : '';
		// generate promotion line_item
		$line_item = EE_Line_Item::new_instance(
			array(
				'LIN_code' 			=> 'promotion-' . $promotion->ID(),
				'TXN_ID'				=> $parent_line_item->TXN_ID(),
				'LIN_name' 			=> apply_filters(
					'FHEE__EE_Promotion_Scope__generate_promotion_line_item__LIN_name',
					$promo_name,
					$promotion
				),
				'LIN_desc' 			=> $promo_desc,
				'LIN_unit_price' 	=> $promotion->is_percent() ? 0 : $promotion->amount(),
				'LIN_percent' 		=> $promotion->is_percent() ? $promotion->amount() : 0,
				'LIN_is_taxable' 	=> $affects_tax,
				'LIN_order' 			=> $promotion->price()->order() + EE_Promotion_Scope::$_counter, // we want promotions to be applied AFTER other line items
				'LIN_total' 			=> $promotion->calculated_amount_on_value( $parent_line_item->total() ),
				'LIN_quantity' 	=> 1,
				'LIN_parent' 		=> $parent_line_item->ID(), 		// Parent ID (this item goes towards that Line Item's total)
				'LIN_type'			=> $this->get_promotion_line_item_type(),
				'OBJ_ID' 				=> $promotion->ID(), 		// ID of Item purchased
				'OBJ_type'			=> 'Promotion' 	// Model Name this Line Item is for
			)
		);
		EE_Promotion_Scope::$_counter++;
		return $line_item;
	}



	/**
	 * get_redeemable_scope_promos
	 *
	 * @param EE_Promotion $promotion
	 * @param int          $OBJ_ID
	 * @throws \EE_Error
	 * @return bool
	 */
	public function increment_promotion_scope_uses( EE_Promotion $promotion, $OBJ_ID = 0 ) {
		$EEM_Promotion_Object = EE_Registry::instance()->load_model( 'Promotion_Object' );
		// retrieve promotion object having the given ID and type scope
		$promotion_object = $EEM_Promotion_Object->get_one( array( array( 'PRO_ID' => $promotion->ID(), 'OBJ_ID' => $OBJ_ID )));
		if ( $promotion_object instanceof EE_Promotion_Object ) {
			$promotion_object->increment_used();
			$promotion_object->save();
			return TRUE;
		}
		throw new EE_Error( __( 'A valid EE_Promotion_Object object could not be found.', 'event_espresso' ));
	}



	/**
	 * __wakeup
	 */
	public function __wakeup() {
		$this->init();
	}




}
// end of file: wp-content/plugins/espresso-promotions/lib/scopes/EE_Promotion_Scope.lib.php