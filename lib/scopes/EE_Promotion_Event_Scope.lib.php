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


	protected function _set_main_properties() {
		$this->label->singular = __('Event', 'event_espresso');
		$this->label->plural = __('Events', 'event_espresso');
		$this->slug = 'Event';
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
		if ( empty( $EVT_ID ) || is_array( $EVT_ID ) )
			return $this->label->plural;

		$evt = $EVT_ID instanceof EE_Event ? $EVT_ID : $this->_get_model_object( $EVT_ID );
		return $evt->name();
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
		$base_url = admin_url('admin.php?page="espresso_events"');
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
			'scope_slug' => $this->name,
			'header_content' => __('<p>Check off the specific events that this promotion will be applied to.</p>', 'event_espresso'),
			'filters' => $this->_get_applies_to_filters(),
			'items_to_select' => $this->_get_applies_to_items_to_select( $items_to_select, $selected_items ),
			'items_paging' => $this->_get_applies_to_items_paging( $total_items ),
			'selected_items' => $selected_items,
			'display_selected_label' => __('Display only selected Events', 'event_espresso' )
			);
		$content = EEH_Template::display_template( $template, $template_args, TRUE );
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
		//todo have to check for any filtered queries here.
		$_where = array(
			'status' => 'publish',
			'Datetime.DTT_EVT_end' => array( '<', date('Y-m-d g:i:s', time() ) )
			);
		return array( '0' => $_where );
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
		$cat_values = array( 'text' => __('Include all', 'event_espresso'), 'id' => 0 );
		$default = ! empty( $_REQUEST['EVT_CAT_ID'] ) ? $_REQUEST['EVT_CAT_ID'] : '';
		foreach( $categories as $id => $name ) {
			$cat_values[] = array(
				'text' => $name,
				'id' => $id
				);
		}
		$cat_filter = EEH_Form_Fields::select_input( 'EVT_CAT_ID', $cat_values);

		//start date
		$existing_sdate = ! empty( $_REQUEST['EVT_start_date_filter'] ) ? $_REQUEST['EVT_start_date_filter'] : '';
		$start_date_filter = '<input type="text" id="EVT_start_date_filter" name="EVT_start_date_filter" class="promotions-date-filter ee-text-inp ee-datepicker" value="' . $existing_sdate . '">';

		//end date
		$existing_edate = ! empty( $_REQUEST['EVT_end_date_filter'] ) ? $_REQUEST['EVT_end_date_filter'] : '';
		$end_date_filter = '<input type="text" id="EVT_end_date_filter" name="EVT_end_date_filter" class="promotions-date-filter ee-text-inp ee-datepicker" value="' . $existing_edate . '">';

		return $cat_filter . '<br>' . $start_date_filter . '<br>' . $end_date_filter;
	}

}
