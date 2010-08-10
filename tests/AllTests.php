<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 */

define('EXHIBIT_BUILDER_DIR', dirname(dirname(__FILE__)));
require_once 'ExhibitBuilder_ViewTestCase.php';
require_once 'ExhibitBuilder_IntegrationHelper.php';

/**
 * 
 *
 * @package Omeka
 * @copyright Center for History and New Media, 2007-2010
 */
class ExhibitBuilder_AllTests extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new ExhibitBuilder_AllTests('ExhibitBuilder Tests');
        $testCollector = new PHPUnit_Runner_IncludePathTestCollector(
          array(dirname(__FILE__) . '/cases')
        );
        $suite->addTestFiles($testCollector->collectTests());
        return $suite;
    }
}