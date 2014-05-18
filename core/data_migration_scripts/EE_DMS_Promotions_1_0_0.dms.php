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

	public function can_migrate_from_version($versions) {
		if(isset($versions['Promotions']) && version_compare('1.0.0',$versions['Promotions'])){
			return false;
		}else{
			global $wpdb;
			$table_name = $wpdb->prefix."events_discount_codes";
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				//ee3 category tables don't exist still
				$an_ee3_table_exists = false;
			}else{
				$an_ee3_table_exists = true;
			}
		}
		//check if the old 3.1 promotions tables are there.
		return true;
	}

	public function schema_changes_after_migration() {

	}

	public function schema_changes_before_migration() {
		$table_name = 'esp_promotion';
		$sql = "PRO_ID INT UNSIGNED NOT NULL AUTO_INCREMENT ,
					PRC_ID INT UNSIGNED NOT NULL ,
					PRO_scope VARCHAR(16) NOT NULL DEFAULT 'event' ,
					PRO_start DATETIME NULL DEFAULT NULL ,
					PRO_end DATETIME NULL DEFAULT NULL ,
					PRO_code VARCHAR(45) NULL DEFAULT NULL ,
					PRO_uses SMALLINT UNSIGNED NULL DEFAULT NULL ,
					PRO_global TINYINT(1) NOT NULL DEFAULT 0 ,
					PRO_global_uses SMALLINT UNSIGNED NOT NULL DEFAULT 0 ,
					PRO_exclusive TINYINT(1) NOT NULL DEFAULT 0 ,
					PRO_accept_msg TINYTEXT NULL DEFAULT NULL ,
					PRO_decline_msg TINYTEXT NULL DEFAULT NULL ,
					PRO_default TINYINT(1) NOT NULL DEFAULT 0 ,
					PRO_order TINYINT UNSIGNED NOT NULL DEFAULT 40 ,
					PRO_deleted TINYINT(1) NOT NULL DEFAULT 0,
					PRIMARY KEY  (PRO_ID) ,
					KEY PRC_ID (PRC_ID)";
		$this->_table_is_new_in_this_version($table_name, $sql, 'ENGINE=InnoDB ');

		$table_name = 'esp_promotion_object';
		$sql = "POB_ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
			PRO_ID INT UNSIGNED NOT NULL,
			OBJ_ID INT UNSIGNED NOT NULL,
			POB_type VARCHAR(45) NULL,
			POB_used INT NULL,
			PRIMARY KEY  (POB_ID),
			KEY OBJ_ID (OBJ_ID),
			KEY PRO_ID (PRO_ID)";
		$this->_table_is_new_in_this_version($table_name, $sql, 'ENGINE=InnoDB ');

		$table_name = 'esp_promotion_rule';
		$sql = "PRR_ID INT UNSIGNED NOT NULL AUTO_INCREMENT ,
					PRO_ID INT UNSIGNED NOT NULL ,
					RUL_ID INT UNSIGNED NOT NULL ,
					PRR_order TINYINT UNSIGNED NOT NULL DEFAULT 1,
					PRR_add_rule_comparison ENUM('AND','OR') NULL DEFAULT 'AND',
					PRIMARY KEY  (PRR_ID) ,
					KEY PRO_ID (PRO_ID),
					KEY RUL_ID (RUL_ID) ";
		$this->_table_is_new_in_this_version($table_name, $sql, 'ENGINE=InnoDB ');



		$table_name = 'esp_rule';
		$sql = "RUL_ID INT UNSIGNED NOT NULL AUTO_INCREMENT ,
					RUL_name VARCHAR(45) NOT NULL ,
					RUL_desc TEXT NULL ,
					RUL_trigger VARCHAR(45) NOT NULL ,
					RUL_trigger_type VARCHAR(45) NULL DEFAULT NULL ,
					RUL_comparison ENUM('=','!=','<','>') NOT NULL DEFAULT '=' ,
					RUL_value VARCHAR(45) NOT NULL ,
					RUL_value_type VARCHAR(45) NULL DEFAULT NULL ,
					RUL_is_active TINYINT(1) NOT NULL DEFAULT 1 ,
					RUL_archived TINYINT(1) NOT NULL DEFAULT 0 ,
					PRIMARY KEY  (RUL_ID)";
		$this->_table_is_new_in_this_version($table_name, $sql, 'ENGINE=InnoDB ');
	}
}

// End of file EE_DMS_Promotions_1_0_0.dms.php
