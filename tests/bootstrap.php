<?php

use EventEspresso\tests\bootstrap\AddonLoader;

$core_tests_dir = dirname(dirname(dirname(__FILE__))) . '/event-espresso-core/tests/';
//if still don't have $core_tests_dir, then let's check tmp folder.
if (! is_dir($core_tests_dir)) {
    $core_tests_dir = '/tmp/event-espresso-core/tests/';
}
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
