<?php 
define(EXHIBIT_BUILDER_DIR, dirname(dirname(__FILE__)));
$baseBootstrap = dirname(dirname(dirname(dirname(__FILE__)))) . '/application/tests/bootstrap.php';
require_once $baseBootstrap;
require_once 'ExhibitBuilder_TestCase.php';