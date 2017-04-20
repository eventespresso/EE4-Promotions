<?php
/**
 * Bootstrap for eea-people-addon tests
 */

use EETests\bootstrap\AddonLoader;

$core_tests_dir = dirname(dirname(dirname(__FILE__))) . '/event-espresso-core/tests/';
require $core_tests_dir . 'includes/CoreLoader.php';
require $core_tests_dir . 'includes/AddonLoader.php';

define('EEPRO_PLUGIN_DIR', dirname(dirname(__FILE__)) . '/');
define('EEPRO_TESTS_DIR', EEPRO_PLUGIN_DIR . 'tests/');


$addon_loader = new AddonLoader(
    EEPRO_TESTS_DIR,
    EEPRO_PLUGIN_DIR,
    'eea-promotions.php'
);
$addon_loader->init();
require EEPRO_TESTS_DIR . 'includes/EE_Promotions_UnitTestCase.class.php';
