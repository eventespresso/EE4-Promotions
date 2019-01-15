<?php

/**
 * EE_DMS_Promotions_1_0_0
 *
 * @package               Event Espresso
 * @subpackage
 * @author                Mike Nelson
 */
class EE_DMS_Promotions_1_0_0 extends EE_Data_Migration_Script_Base
{

    const TABLE_NAME_PROMOTION = 'esp_promotion';

    const TABLE_NAME_PROMOTION_OBJECT = 'esp_promotion_object';


    public function __construct()
    {
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
    public function can_migrate_from_version($versions)
    {
        return false;
    }


    public function schema_changes_after_migration()
    {
    }


    public function schema_changes_before_migration()
    {
        // set promotions_exclusive_default as either 0 or 1
        $exclusive = absint(
            filter_var(
                apply_filters('FHEE__EEM_Promotion__promotions_exclusive_default', 0),
                FILTER_VALIDATE_BOOLEAN
            )
        );

        // delete old tables (if empty)
        $this->_delete_table_if_empty('esp_promotion_applied');
        $this->_delete_table_if_empty('esp_promotion_rule');

        $this->_table_should_exist_previously(
            self::TABLE_NAME_PROMOTION,
            "PRO_ID int(10) unsigned NOT NULL AUTO_INCREMENT,
                PRC_ID int(10) unsigned NOT NULL,
                PRO_scope varchar(16) NOT NULL DEFAULT 'Event',
                PRO_start datetime NULL DEFAULT NULL,
                PRO_end datetime NULL DEFAULT NULL,
                PRO_code varchar(45) NULL DEFAULT NULL,
                PRO_uses smallint(6) NULL DEFAULT 1,
                PRO_global tinyint(1) NOT NULL DEFAULT 0,
                PRO_global_uses smallint(6) NOT NULL DEFAULT -1,
                PRO_exclusive tinyint(1) NOT NULL DEFAULT $exclusive,
                PRO_accept_msg tinytext NULL DEFAULT NULL,
                PRO_decline_msg tinytext NULL DEFAULT NULL,
                PRO_default tinyint(1) NOT NULL DEFAULT 0,
                PRO_order tinyint unsigned NOT NULL DEFAULT 40,
                PRO_deleted tinyint(1) NOT NULL DEFAULT 0,
                PRO_wp_user bigint(20) unsigned NOT NULL DEFAULT 1,
                PRIMARY KEY  (PRO_ID),
                KEY PRC_ID (PRC_ID),
                KEY PRO_code (PRO_code),
                KEY PRO_start (PRO_start),
                KEY PRO_end (PRO_end)",
            'ENGINE=InnoDB'
        );

        $this->_table_should_exist_previously(
            self::TABLE_NAME_PROMOTION_OBJECT,
            'POB_ID int(10) unsigned NOT NULL AUTO_INCREMENT,
			PRO_ID int(10) unsigned NOT NULL,
			OBJ_ID bigint(20) unsigned NOT NULL,
			POB_type varchar(45) NULL,
			POB_used smallint(6) NULL,
			PRIMARY KEY  (POB_ID),
			KEY PRO_ID (PRO_ID),
			KEY OBJ_ID (OBJ_ID)',
            'ENGINE=InnoDB'
        );
    }
}
