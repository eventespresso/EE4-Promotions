<?php

if (!defined('EVENT_ESPRESSO_VERSION'))
	exit('No direct script access allowed');

/**
 *
 * EE_DMS_Promotions_1_0_0
 *
 * @package			Event Espresso
 * @subpackage
 * @author				Mike Nelson
 *
 */
class EE_DMS_Promotions_1_0_0 extends EE_Data_Migration_Script_Base{
	public function __construct() {
		$this->_pretty_name = __('Create Promotions Addon table', 'event_espresso');
		$this->_migration_stages = array();
		parent::__construct();
	}



	/**
	 * Returns whether or not this data migration script can operate on the given version of the database.
	 *
	 * @param array $versions
	 * @return boolean
	 */
	public function can_migrate_from_version($versions) {
		return false;
	}

	public function schema_changes_after_migration() {

	}

	public function schema_changes_before_migration() {
		// set promotions_exclusive_default as either 0 or 1
		$exclusive = absint( filter_var( apply_filters( 'FHEE__EEM_Promotion__promotions_exclusive_default', 0 ), FILTER_VALIDATE_BOOLEAN ) );

		// delete old tables (if empty)
		$this->_delete_table_if_empty( 'esp_promotion' );
		$this->_delete_table_if_empty( 'esp_promotion_object' );
		$this->_delete_table_if_empty( 'esp_promotion_applied' );
		$this->_delete_table_if_empty( 'esp_promotion_rule' );

		$table_name = 'esp_promotion';
		$sql = "PRO_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
					PRC_ID INT UNSIGNED NOT NULL,
					PRO_scope VARCHAR(16) NOT NULL DEFAULT 'event',
					PRO_start DATETIME NULL DEFAULT NULL,
					PRO_end DATETIME NULL DEFAULT NULL,
					PRO_code VARCHAR(45) NULL DEFAULT NULL,
					PRO_uses SMALLINT NULL DEFAULT 1,
					PRO_global TINYINT(1) NOT NULL DEFAULT 0,
					PRO_global_uses SMALLINT NOT NULL DEFAULT -1,
					PRO_exclusive TINYINT(1) NOT NULL DEFAULT $exclusive,
					PRO_accept_msg TINYTEXT NULL DEFAULT NULL,
					PRO_decline_msg TINYTEXT NULL DEFAULT NULL,
					PRO_default TINYINT(1) NOT NULL DEFAULT 0,
					PRO_order TINYINT UNSIGNED NOT NULL DEFAULT 40,
					PRO_deleted TINYINT(1) NOT NULL DEFAULT 0,
					PRO_wp_user BIGINT UNSIGNED NOT NULL DEFAULT 1,
					PRIMARY KEY  (PRO_ID),
					KEY PRC_ID (PRC_ID)";
		$this->_table_should_exist_previously($table_name, $sql, 'ENGINE=InnoDB ');

		$table_name = 'esp_promotion_object';
		$sql = "POB_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
			PRO_ID INT UNSIGNED NOT NULL,
			OBJ_ID INT UNSIGNED NOT NULL,
			POB_type VARCHAR(45) NULL,
			POB_used INT NULL,
			PRIMARY KEY  (POB_ID),
			KEY OBJ_ID (OBJ_ID),
			KEY PRO_ID (PRO_ID)";
		$this->_table_should_exist_previously($table_name, $sql, 'ENGINE=InnoDB ');
	}
}

// End of file EE_DMS_Promotions_1_0_0.dms.php
