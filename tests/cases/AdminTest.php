<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * These are tests for the administrative UI for building exhibits / other ways
 * that ExhibitBuilder modifies the admin UI.
 * 
 * @internal If this test list appears incomplete, it's because tests are currently
 * only being written for behavior that breaks, when it breaks.
 *
 * @package ExhibitBuilder
 * @copyright Center for History and New Media, 2007-2010
 */
class ExhibitBuilder_AdminTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = true;
    
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }
    
    public function testCanBrowseExhibits()
    {
        $this->helper->createNewExhibits();
        $this->dispatch('exhibits/browse');
        $this->assertController('exhibits');
        $this->assertAction('browse');
    }
    
    public function testCanSeeEditLinkOnBrowsePage()
    {
        $this->helper->createNewExhibits();
        $this->_authenticateUser($this->_getDefaultUser());
        $this->dispatch('exhibits/browse');
        $this->assertQueryContentContains("a.edit", "Edit");
    }
}