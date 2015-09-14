<?php
/**
 * This file contains the qtips configuration class for the promotion list table.
 *
 * @since 1.0.0
 * @package EE Promotions
 * @subpackage admin
 */
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }

 /**
 * Promotions_List_Table_Tips
 * Qtip configuration for the Promotions List Table.
 *
 * @since 1.0.0
 *
 * @package		EE Promotions
 * @subpackage 	admin
 * @author		Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class Promotions_List_Table_Tips extends EE_Qtip_Config {

	protected function _set_tips_array() {
		$this->_qtipsa = array(
			/**0 => array(
				'content_id' => 'promotion-status-' . EE_Promotion::active,
				'target' => '.pro-status-' . EE_Promotion::active,
				'content' => $this->_promotion_status_legend(EE_Promotion::active),
				'options' => array(
					'position' => array(
						'target' => 'mouse'
						)
					)
				),
			1 => array(
				'content_id' => 'promotion-status-' . EE_Promotion::upcoming,
				'target' => '.pro-status-' . EE_Promotion::upcoming,
				'content' => $this->_promotion_status_legend(EE_Promotion::upcoming),
				'options' => array(
					'position' => array(
						'target' => 'mouse'
						)
					)
				),
			2 => array(
				'content_id' => 'promotion-status-' . EE_Promotion::expired,
				'target' => '.pro-status-' . EE_Promotion::expired,
				'content' => $this->_promotion_status_legend(EE_Promotion::expired),
				'options' => array(
					'position' => array(
						'target' => 'mouse'
						)
					)
				),
			3 => array(
				'content_id' => 'promotion-status-' . EE_Promotion::unavailable,
				'target' => '.pro-status-' . EE_Promotion::unavailable,
				'content' => $this->_promotion_status_legend(EE_Promotion::unavailable),
				'options' => array(
					'position' => array(
						'target' => 'mouse'
						)
					)
				),/**/
			4 => array(
				'content_id' => 'promotions-delete-lock',
				'target' => '.ee-lock-icon',
				'content' => $this->_promotions_delete_message(),
				),
			);
	}



	/**
	 * output the relevant ee-status-legend with the designated status highlighted.
	 *
	 * @since 1.0.0
	 *
	 * @param  EE_Promotion constant $status What status is set (by class)
	 * @return string         The status legend with the related status highlighted
	 */
	private function _promotion_status_legend( $status ) {

		$status_array = array(
			'active_status' => EE_Promotion::active,
			'upcoming_status' => EE_Promotion::upcoming,
			'expired_status' => EE_Promotion::expired,
			'unavailable_status' => EE_Promotion::unavailable,
			);

		return EEH_Template::status_legend( $status_array, $status );
	}


	/**
	 * Message why deleting permanently a promotion is not possible.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function _promotions_delete_message() {
		return  __('This lock-icon means that this promotion cannot be permanently deleted.  Promotions that have been redeemed cannot be permanently deleted from your system. However, trashing (not deleting) them does deactivate them from future redemptions.', 'event_espresso');
	}
}
