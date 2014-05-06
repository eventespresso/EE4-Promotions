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
	 * @param mixed int|array    $EVT_ID (or array of ids) for the EE_Event object being utilized.
	 * @return string
	 */
	public function name( $EVT_ID ) {
		if ( empty( $EVT_ID ) || is_array( $EVT_ID ) )
			return $this->label->plural;

		$evt = $this->_get_model_object( $EVT_ID );
		return $evt->name();
	}



	/**
	 * Child scope classes indicate what gets returned when a "description" is requested.
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed int|array   $EVT_ID   (or array of IDS) for the EE_Event object being utilized
	 * @return string
	 */
	public function description( $EVT_ID ) {
		if ( empty( $EVT_ID ) || is_array( $EVT_ID ) )
			return __('Applied to all events.', 'event_espresso');

		$evt = $this->_get_model_object( $EVT_ID );
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
}
