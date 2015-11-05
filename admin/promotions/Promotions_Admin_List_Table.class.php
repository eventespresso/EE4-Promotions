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
		$this->_data = $this->_view == 'trash' ? $this->_get_promotions( $this->_per_page, FALSE, TRUE) : $this->_get_promotions( $this->_per_page );
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
			'redeemed' => __('Uses', 'event_espresso'),
			'attributes' => '<span class="small-text">' . __( 'Attributes', 'event_espresso' ) . '</span >',
			'actions' => __('Actions', 'event_espresso')
			);

		$this->_primary_column = 'ID';
		$this->_has_checkbox_column = true;

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
		if ( EE_Registry::instance()->CAP->current_user_can( 'ee_delete_promotions', 'espresso_promotions_delete_promotions' ) ) {
			$this->_views['trash']['count'] = $this->_trashed_count();
		}
	}


	protected function _get_row_class( $item ) {
		$class = parent::_get_row_class( $item );
		//add status class
		$class .= ' ee-status-strip pro-status-' . $item->status();

		return $class;
	}


	public function column_cb( $item ) {
		if ( ! $item instanceof EE_Promotion ) {
			return '';
		}
		$checkbox = sprintf( '<input type="checkbox" name="PRO_ID[]" value="%s" />', $item->ID() );
		echo $item->redeemed() > 0 && $this->_view == 'trash' ?  '<span class="ee-lock-icon"></span>' .  $checkbox : $checkbox;
	}



	public function column_id( EE_Promotion $item ) {
		$content = $item->ID();
		$content .= '<div class="show-on-mobile-view-only">';
		$content .= ' - ' . $item->name();
		return $content;
	}


	public function column_name( EE_Promotion $item ) {
		$edit_link = EEH_URL::add_query_args_and_nonce( array( 'action' => 'edit', 'PRO_ID' => $item->ID() ), EE_PROMOTIONS_ADMIN_URL );
		$content = EE_Registry::instance()->CAP->current_user_can( 'ee_edit_promotion', 'espresso_promotions_edit_promotion', $item->ID() ) ? '<a href="' . $edit_link . '" title="' . __('Edit Promotion', 'event_espresso') . '">' . $item->name() . '</a>' : $item->name();
		$content .= '<br><span class="ee-status-text-small">' . EEH_Template::pretty_status( $item->status(), false, 'sentence' ) . '</span>';
		return $content;
	}


	public function column_code( EE_Promotion $item ) {
		echo $item->code();
	}



	public function column_applies_to( EE_Promotion $item ) {
		echo $item->applied_to_name( 'admin' );
	}



	public function column_attributes( EE_Promotion $item ) {
		echo $item->is_exclusive() ? '<span class="dashicons dashicons-awards" title="' . __('Exclusive Promotion - can NOT be combined with others', 'event_espresso') . '"></span>' : '';
		echo $item->is_global() ? '<span class="dashicons dashicons-admin-site" title="' . __('Global Promotion - applies to ALL scope items', 'event_espresso') . '"></span>' : '';
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
		echo $item->uses() === EE_INF_IN_DB ? $item->redeemed() . ' /<span class="ee-infinity-sign">&#8734;</span>' : $item->redeemed() . ' / ' . $item->uses_available();
	}




	public function column_actions( EE_Promotion $item ) {
		$action_links = array();
		EE_Registry::instance()->load_helper('URL');

		if ( $this->_view != 'trash' ) {
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
			if ( EE_Registry::instance()->CAP->current_user_can( 'ee_edit_promotion', 'espresso_promotions_edit_promotion', $item->ID() ) ) {
				$action_links[] = '<a href="' . $edit_link . '" title="' . __('Edit Promotion', 'event_espresso') . '"><div class="dashicons dashicons-edit clickable ee-icon-size-20"></div></a>';
			}

			if ( EE_Registry::instance()->CAP->current_user_can( 'ee_edit_promotion', 'espresso_promotions_edit_promotion', $item->ID() ) ) {
				$action_links[] = '<a href="' . $dupe_link. '" title="' . __('Duplicate Promotion', 'event_espresso') . '"><div class="ee-icon ee-icon-clone clickable ee-icon-size-16"></div></a>';
			}
		} else {
			$restore_query_args = array(
				'action' => 'restore_promotion',
				'PRO_ID' => $item->ID()
			);
			$restore_link = EEH_URL::add_query_args_and_nonce( $restore_query_args, EE_PROMOTIONS_ADMIN_URL );
			if ( EE_Registry::instance()->CAP->current_user_can( 'ee_delete_promotion', 'espresso_promotions_delete_promotion', $item->ID() ) ) {
				$action_links[] = '<a href="' . $restore_link. '" title="' . __('Restore Promotion', 'event_espresso') . '"><div class="dashicons dashicons-backup ee-icon-size-18"></div></a>';
			}
		}

		$trash_query_args = array(
			'action' => $this->_view == 'trash' && $item->redeemed() ? 'delete_promotion' : 'trash_promotion',
			'PRO_ID' => $item->ID()
			);
		$trash_link = EEH_URL::add_query_args_and_nonce( $trash_query_args, EE_PROMOTIONS_ADMIN_URL );
		$trash_text = $this->_view == 'trash' ? __('Delete Promotion permanently', 'event_espresso') : __('Trash Promotion', 'event_espresso');
		$trash_class = $this->_view == 'trash' ? ' red-icon' : '';
		if ( EE_Registry::instance()->CAP->current_user_can( 'ee_delete_promotion', 'espresso_promotions_delete_promotion', $item->ID() ) ) {
			$action_links[] = $this->_view == 'trash' && $item->redeemed() > 0 ? '' : '<a href="' . $trash_link . '" title="' . $trash_text . '"><div class="dashicons dashicons-trash clickable ee-icon-size-18' . $trash_class . '"></div></a>';
		}

		$content = '<div style="width:100%;">' . "\n\t";
		$content .= implode( "\n\t", $action_links );
		$content .= "\n" . '</div>' . "\n";
		echo $content;
	}



	protected function _get_promotions( $per_page = 10, $count = FALSE, $trash = FALSE ) {
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

		$sort = ( ! empty( $this->_req_data['order'] ) ) ? $this->_req_data['order'] : 'DESC';
		$current_page = ! empty( $this->_req_data['paged'] ) ? $this->_req_data['paged'] : 1;
		$per_page = ! empty( $per_page ) ? $per_page : 10;
		$per_page =  ! empty( $this->_req_data['perpage'] ) ? $this->_req_data['perpage'] : $per_page;

		$offset = ( $current_page - 1 ) * $per_page;
		$limit = array( $offset, $per_page );

		$query_args = array( $_where,  'limit' => $limit, 'order_by' => $orderby, 'order' => $sort );

		//possibly modify for caps
		if ( ! EE_Registry::instance()->CAP->current_user_can( 'ee_read_others_promotions', 'get_others_promotions' ) ) {
			$query_args = EEM_Promotion::instance()->alter_query_params_to_only_include_mine( $query_args );
		}

		if ( $trash ) {
			$promotions = $count ? EEM_Promotion::instance()->count_deleted(array( $_where ) ) : EEM_Promotion::instance()->get_all_deleted( $query_args );
		} else {
			$promotions = $count ? EEM_Promotion::instance()->count(array( $_where ) ) : EEM_Promotion::instance()->get_all( $query_args );
		}
		return $promotions;
	}



	//not in use because promotions isn't a soft delete model currently.
	protected function _trashed_count() {
		return $this->_get_promotions( $this->_per_page, TRUE, TRUE );
	}
}
