<?php
/**
 * This file contains the  class for EE_Promotion Event scope
 *
 * @since 1.0.0
 * @package EE Promotions
 * @subpackage models
 */
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }

/**
 * Defines the Event Scope
 *
 * @since 1.0.0
 * @see  EE_Promotion_Scope for any phpdoc comments on classes defined there.
 *
 * @package EE4 Promotions
 * @subpackage models
 * @author Darren Ethier
 */
class EE_Promotion_Event_Scope extends EE_Promotion_Scope {


	protected function _set_main_properties_and_hooks() {
		$this->label->singular = __('Event', 'event_espresso');
		$this->label->plural = __('Events', 'event_espresso');
		$this->slug = 'Event';

		//filters
		//filter for get_events on admin list table to only show events attached to specific promotion.
		add_filter( 'FHEE__Events_Admin_Page__get_events__where', array( $this, 'event_list_query_params' ), 10, 2 );
		//filter to show a helpful title on events list table when displaying events filtered by promotion
		add_filter( 'FHEE__EE_Admin_Page___display_admin_list_table_page__before_list_table__template_arg', array( $this, 'before_events_list_table_content' ), 10, 4 );
	}



	/**
	 * Callback for FHEE__Events_Admin_Page__get_events__where.  Event Scope adds
	 * additional query params to the query retrieving the events in certain conditions.
	 *
	 * @since 1.0.0
	 *
	 * @param     	array $where   		current query where params for event query
	 * @req_data	array $req_data	incoming request data.
	 * @return array 			where query_args for get_events query.
	 */
	public function event_list_query_params( $where, $req_data ) {
		if ( !empty( $req_data['EVT_IDs'] ) ) {
			$evt_ids = explode( ',', $req_data['EVT_IDs'] );
			$where['EVT_ID'] = array('IN', $evt_ids);
		}
		return $where;
	}




	/**
	 * callback for the FHEE__EE_Admin_Page___display_admin_list_table_page__before_list_table__template_arg filter so if displaying events filtered by promotion we add helpful title for viewer.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content      Any current content.
	 * @param string $page_slug  Page slug of page
	 * @param array  $req_data    Incoming request data.
	 * @param string $req_action 'action' value for page
	 *
	 * @return string  If correct page then and conditions are met the new string. Otherwise existing.
	 */
	public function before_events_list_table_content( $content, $page_slug, $req_data, $req_action ) {
		if ( ( $page_slug !== 'espresso_events' && $req_action !== 'default' ) || empty( $req_data['PRO_ID'] ) )
			return $content;

		$promotion = EEM_Promotion::instance()->get_one_by_ID( $req_data['PRO_ID'] );

		if ( $promotion instanceof EE_Promotion ) {
			$query_args = array(
				'action' => 'edit',
				'PRO_ID' => $promotion->ID()
				);
			EE_Registry::instance()->load_helper('URL');
			$url = EEH_URL::add_query_args_and_nonce( $query_args, admin_url('admin.php?page=espresso_promotions'));
			$pro_linked = '<a href="' . $url . '" title="' . __('Click to view promotion details.', 'event_espresso') . '">' . $promotion->name() . '</a>';
			$content .= '<h3>' . sprintf( __('Viewing Events that the %s promotion applies to.', 'event_espresso' ), $pro_linked ) . '</h3>';
		}

		return $content;
	}



	/**
	 * Child scope classes indicate what gets returned when a "name" is requested.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed int|array|EE_Event    $EVT_ID (or array of ids) or EE_Event object for
	 *                                    		the EE_Event object being utilized.
	 * @return string
	 */
	public function name( $EVT_ID ) {
		if ( empty( $EVT_ID ) || ( is_array( $EVT_ID ) && count( $EVT_ID ) > 1 ) )
			return $this->label->plural;

		$EVT_ID = is_array( $EVT_ID ) ? $EVT_ID[0] : $EVT_ID;

		$evt = $EVT_ID instanceof EE_Event ? $EVT_ID : $this->_get_model_object( $EVT_ID );
		return $evt->name();



	/**
	 * This returns a html span string for the event scope icon.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_scope_icon() {
		return '<span class="dashicons dashicons-flag"></span>';
	}



	/**
	 * Child scope classes indicate what gets returned when a "description" is requested.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed int|array|EE_Event   $EVT_ID   (or array of IDS)  or EE_Event object
	 *                                    		for the EE_Event object being utilized
	 * @return string
	 */
	public function description( $EVT_ID ) {
		if ( empty( $EVT_ID ) || is_array( $EVT_ID ) )
			return __('Applied to all events.', 'event_espresso');

		$evt = $EVT_ID instanceof EE_Event ? $EVT_ID : $this->_get_model_object( $EVT_ID );
		return sprintf( __('Applied to %s', 'event_espresso'), $evt->name() );
	}



	/**
	 * Child scope classes indicate what gets returned when the admin_url is requested.
	 * Admin url usually points to the details page for the given id.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed int|array   $EVT_ID   ID or array of ids for the EE_Event object being utilized
	 * @return string
	 */
	public function get_admin_url( $EVT_ID ) {
		$base_url = admin_url('admin.php?page=espresso_events');
		if ( empty( $EVT_ID ) || is_array( $EVT_ID) )
			return $base_url;

		$query_args = array(
			'action' => 'edit',
			'post' => $EVT_ID
			);
		EE_Registry::instance()->load_helper('URL');
		return EEH_URL::add_query_args_and_nonce( $query_args, $base_url );
	}



	/**
	 * Child scope classes indicate what gets returned when the frontend_url is requested.
	 * Frontend url usually points to the single page view for the given id.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed int|array   $EVT_ID   ID or array of ids for the EE_Event object being utilized
	 * @return string
	 */
	public function get_frontend_url( $EVT_ID ) {
		EE_Registry::instance()->load_helper('Event_View');
		if ( empty( $EVT_ID ) || is_array( $EVT_ID ) )
			return EEH_Event_View::event_archive_link();

		return EEH_Event_View::event_link_url( $EVT_ID );
	}





	/**
	 * Generate the html for selecting events that a promotion applies to.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $PRO_ID The promotion ID for the applies to selector we are
	 *                        	        retreiving.
	 * @return string html
	 */
	public function get_admin_applies_to_selector( $PRO_ID ) {
		$template = EE_PROMOTIONS_PATH . '/lib/scopes/templates/promotion_applies_to_wrapper.template.php';
		$total_items = $this->_get_total_items();
		$items_to_select = $this->get_scope_items();
		$selected_items = $this->_get_applied_to_items( $PRO_ID );
		$template_args = array(
			'scope_slug' => $this->slug,
			'scope' => $this,
			'header_content' => __('<p>Check off the specific events that this promotion will be applied to.</p>', 'event_espresso'),
			'filters' => $this->_get_applies_to_filters(),
			'items_to_select' => $this->_get_applies_to_items_to_select( $items_to_select, $selected_items ),
			'items_paging' => $this->_get_applies_to_items_paging( $total_items ),
			'selected_items' => $selected_items,
			'display_selected_label' => __('Display only selected Events', 'event_espresso' ),
			'footer_content' => ''
			);
		return EEH_Template::display_template( $template, $template_args, TRUE );
	}



	/**
	 * Returns query args for use in model queries on the
	 * EEM model related to scope.  Note this also should
	 * consider any filters present
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_query_args() {
		EE_Registry::instance()->load_helper('DTT_Helper');
		$month_increment = apply_filters( 'FHEE__EE_Promotion_Event_Scope__get_query_args__month_increment', 1 );
		//check for any existing dtt queries
		$DTT_EVT_start = !empty( $_REQUEST['EVT_start_date_filter'] ) ? $_REQUEST['EVT_start_date_filter'] : current_time('mysql');
		$DTT_EVT_end = !empty( $_REQUEST['EVT_end_date_filter'] ) ? $_REQUEST['EVT_end_date_filter'] : EEH_DTT_Helper::calc_date(current_time('timestamp'), 'months', $month_increment );

		$_where = array(
			'status' => 'publish',
			'Datetime.DTT_EVT_end' => array( '<', $DTT_EVT_end ),
			'Datetime.DTT_EVT_start' => array( '>', $DTT_EVT_start )
			);

		//category filters?
		if ( !empty( $_REQUEST['EVT_CAT_ID'] ) ) {
			$_where['Term_Taxonomy.term_id'] = $_REQUEST['EVT_CAT_ID'];
		}

		//event title?
		if ( !empty( $_REQUEST['EVT_title_filter'] ) ) {
			$_where['EVT_name'] = array( 'LIKE', '%' . $_REQUEST['EVT_title_filter'] . '%' );
		}

		$orderby= !empty( $_REQUEST['PRO_scope_sort'] ) ? $_REQUEST['PRO_scope_sort'] : 'ASC';


		return array( '0' => $_where, 'order_by' => array( 'EVT_name' => $orderby ) );
	}



	/**
	 * sets up the filters for the promotions scope selector
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function _get_applies_to_filters() {
		EE_Registry::instance()->load_helper('Form_Fields');
		//categories
		$categories = get_terms( 'espresso_event_categories', array( 'hide_empty' => FALSE, 'fields' => 'id=>name' ) );
		$cat_values[] = array( 'text' => __('Include all categories', 'event_espresso'), 'id' => 0 );
		$default = ! empty( $_REQUEST['EVT_CAT_ID'] ) ? $_REQUEST['EVT_CAT_ID'] : '';
		foreach( $categories as $id => $name ) {
			$cat_values[] = array(
				'text' => $name,
				'id' => $id
				);
		}
		$cat_filter = EEH_Form_Fields::select_input( 'EVT_CAT_ID', $cat_values, $default);
		$month_increment = apply_filters( 'FHEE__EE_Promotion_Event_Scope__get_query_args__month_increment', 1 );

		//start date
		$existing_sdate = ! empty( $_REQUEST['EVT_start_date_filter'] ) ? $_REQUEST['EVT_start_date_filter'] : date( 'Y-m-d h:i a' , current_time('timestamp') );
		$start_date_filter = '<input data-context="start" data-container="scope" data-next-field="#EVT_end_date_filter" type="text" id="EVT_start_date_filter" name="EVT_start_date_filter" class="promotions-date-filter ee-text-inp ee-datepicker" value="' . $existing_sdate . '"><span class="dashicons dashicons-calendar"></span>';

		//end date
		$existing_edate = ! empty( $_REQUEST['EVT_end_date_filter'] ) ? $_REQUEST['EVT_end_date_filter'] : date( 'Y-m-d h:i a' , EEH_DTT_Helper::calc_date(current_time('timestamp'), 'months', $month_increment ) );
		$end_date_filter = '<input data-context="end" data-container="scope" data-next-field="#EVT_title_filter" type="text" id="EVT_end_date_filter" name="EVT_end_date_filter" class="promotions-date-filter ee-text-inp ee-datepicker" value="' . $existing_edate . '"><span class="dashicons dashicons-calendar"></span>';

		//event name
		$existing_name = ! empty( $_REQUEST['EVT_title_filter'] ) ? $_REQUEST['EVT_title_filter'] : '';
		$event_title_filter = '<input type="text" id="EVT_title_filter" name="EVT_title_filter" class="promotions-general-filter ee-text-inp" value="' . $existing_name . '" placeholder="' . __('Event Title Filter', 'event_espresso') . '">';

		return $cat_filter . '<br>' . $start_date_filter . '<br>' . $end_date_filter . '<br>' . $event_title_filter . '<div style="clear: both"></div>';
	}




	/**
	 * save event relation for the applies to promotion
	 *
	 * @since   1.0.0
	 *
	 * @param EE_Promotion $promotion
	 * @param array  	    $data the incoming form data
	 * @return void
	 */
	public function handle_promotion_update( EE_Promotion $promotion, $data ) {
		//first do we have any selected items?
		$selected_items = !empty( $data['ee_promotions_applied_selected_items_Event'] ) ? explode( ',', $data['ee_promotions_applied_selected_items_Event'] ) : array();
		$evt_ids = array();

		//existing pro_objects
		$pro_objects = $promotion->promotion_objects();

		//loop through existing and remove any that aren't present in the selected_items.
		foreach( $pro_objects as $pro_obj ) {
			if ( !in_array( $pro_obj->OBJ_ID(), $selected_items ) )
				$promotion->delete_related('Promotion_Object', array( array( 'POB_ID' => $pro_obj->ID() ) ) );
			$evt_ids[] = $pro_obj->OBJ_ID();
		}

		//k now let's make sure any that should be added are added.
		foreach( $selected_items as $EVT_ID ) {
			if( in_array( $EVT_ID, $evt_ids ) )
				continue;
			$pro_obj_values = array(
				'PRO_ID' => $promotion->ID(),
				'OBJ_ID' => $EVT_ID,
				'POB_type' => $this->slug,
				'POB_used' => 0
				);
			$promotion_obj = EE_Promotion_Object::new_instance( $pro_obj_values );
			$promotion_obj->save();
		}
	}

}
