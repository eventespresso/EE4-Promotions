<?php
/**
 * This file contains the Promotions_Admin_List_Table class
 *
 * @since 1.0.0
 * @package EE Promotions
 * @subpackage admin
 */
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }

/**
 * Defines the list table for the EE promotions system.
 *
 * @since 1.0.0
 * @see EE_Admin_List_Table for code documentation
 *
 * @package EE4 Promotions
 * @subpackage admin
 * @author Darren Ethier
 */
class Promotions_Admin_List_Table extends EE_Admin_List_Table {



	protected function _setup_data() {
		$this->_data = $this->_get_promotions( $this->_per_page );
		$this->_all_data_count = $this->_get_promotions( $this->_per_page, TRUE );
	}



	protected function _set_properties() {
		$this->_wp_list_args = array(
			'singular' => __('Promotion', 'event_espresso'),
			'plural' => __('Promotion', 'event_espresso'),
			'ajax' => TRUE,
			'screen' => $this->_admin_page->get_current_screen()->id
			);

		$this->_columns = array(
			'cb' => '<input type="checkbox" />',
			'id' => __('ID', 'event_espresso'),
			'name' => __('Name', 'event_espresso'),
			'code' => __('Code', 'event_espresso'),
			'applies_to' => __('Applies To', 'event_espresso'),
			'valid_from' => __('Valid From', 'event_espresso'),
			'valid_until' => __('Valid Until', 'event_espresso'),
			'amount' => __('Discount', 'event_espresso'),
			'redeemed' => __('Redeemed', 'event_espresso'),
			'actions' => __('Actions', 'event_espresso')
			);

		$this->_sortable_columns = array(
			'id' => array( 'id' => TRUE ),
			'name' => array( 'name' => false ),
			'code' => array( 'code' => false ),
			'valid_from' => array( 'code' => false ),
			'valid_until' => array( 'valid_until' => false ),
			'redeemed' => array( 'redeemed' => false )
			);

		$this->_hidden_columns = array();
	}



	protected function _get_table_filters() {
		return array();
	}



	protected function _add_view_counts() {
		$this->_views['all']['count'] = $this->_all_data_count;
		//$this->_views['trash']['count'] = $this->_trashed_count();
	}



	public function column_cb( EE_Promotion $item ) {
		printf( '<input type="checkbox" name="PRO_ID[]" value="%s" />', $item->ID() );
	}



	public function column_id( EE_Promotion $item ) {
		echo $item->ID();
	}


	public function column_name( EE_Promotion $item ) {
		echo $item->name();
	}


	public function column_code( EE_Promotion $item ) {
		echo $item->code();
	}



	public function column_applies_to( EE_Promotion $item ) {
		echo $item->applied_to_name();
	}



	public function column_valid_from( EE_Promotion $item ) {
		echo $item->get_date('PRO_start', 'M d/y');
	}




	public function column_valid_until( EE_Promotion $item ) {
		echo $item->get_date('PRO_end', 'M d/y', '');
	}





	public function column_amount( EE_Promotion $item ) {
		echo $item->pretty_amount();
	}





	public function column_redeemed( EE_Promotion $item ) {
		echo $item->redeemed();
	}




	public function column_actions( EE_Promotion $item ) {
		$actionlinks = array();
		EE_Registry::instance()->load_helper('URL');

		$edit_query_args = array(
			'action' => 'edit',
			'PRO_ID' => $item->ID()
			);

		$dupe_query_args = array(
			'action' => 'duplicate',
			'PRO_ID' => $item->ID()
			);

		$edit_link = EEH_URL::add_query_args_and_nonce( $edit_query_args, EE_PROMOTIONS_ADMIN_URL );
		$dupe_link = EEH_URL::add_query_args_and_nonce( $dupe_query_args, EE_PROMOTIONS_ADMIN_URL );

		$actionlinks[] = '<a href="' . $edit_link . '" title="' . __('Edit Promotion', 'event_espresso') . '"><div class="dashicons dashicons-edit clickable"></div></a>';
		$actionlinks[] = '<a href="' . $dupe_link. '" title="' . __('Duplicate Promotion', 'event_espresso') . '"><div class="ee-icon ee-icon-clone clickable"></div></a>';
		$content = '<div style="width:100%;">' . "\n\t";
		$content .= implode( "\n\t", $actionlinks );
		$content .= "\n" . '</div>' . "\n";
		echo $content;
	}



	protected function _get_promotions( $per_page = 10, $count = FALSE ) {
		$_where = array();
		$_orderby = ! empty( $this->_req_data['orderby'] ) ? $this->_req_data['orderby'] : '';
		switch( $_orderby ) {
			case 'name' :
				$orderby = 'Price.PRC_name';
				break;

			case 'code' :
				$orderby = 'PRO_code';
				break;

			case 'valid_from' :
				$orderby = 'PRO_start';
				break;

			case 'valid_until' :
				$orderby = 'PRO_end';
				break;

			case 'redeemed' :
				$orderby = 'Promotion_Object.POB_used';
				break;
			default :
				$orderby = 'PRO_ID';
				break;
		}

		//search query params?
		if ( !empty( $this->_req_data['s'] ) ) {
			$s = $this->_req_data['s'];
			$_where = array(
				'OR' => array(
					'Price.PRC_name' => array( 'LIKE', '%' . $s . '%' ),
					'Price.PRC_desc' => array( 'LIKE', '%' . $s . '%' ),
					'PRO_code' => array( 'LIKE',  '%' . $s . '%' )
				) );
		}

		$sort = ( ! empty( $this->_req_data['order'] ) ) ? $this->_req_data['order'] : 'ASC';
		$current_page = ! empty( $this->_req_data['paged'] ) ? $this->_req_data['paged'] : 1;
		$per_page = ! empty( $per_page ) ? $per_page : 10;
		$per_page =  ! empty( $this->_req_data['perpage'] ) ? $this->_req_data['perpage'] : $per_page;

		$offset = ( $current_page - 1 ) * $per_page;
		$limit = array( $offset, $per_page );

		$promotions = $count ? EEM_Promotion::instance()->count(array( $_where ) ) : EEM_Promotion::instance()->get_all( array( $_where,  'limit' => $limit, 'order_by' => $orderby, 'order' => $sort ) );
		return $promotions;
	}



	//not in use because promotions isn't a soft delete model currently.
	protected function _trashed_count() {
		return 0;
	}
}
