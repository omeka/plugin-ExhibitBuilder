<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */

/**
 * 
 *
 * @package ExhibitBuilder
 * @copyright Center for History and New Media, 2007-2010
 */
class ExhibitBuilder_AclTest extends Omeka_Test_AppTestCase
{
    const EXHIBIT_RESOURCE = 'ExhibitBuilder_Exhibits';
    
    public function setUp()
    {
        parent::setUp();
        $this->helper = new ExhibitBuilder_IntegrationHelper;
        $this->helper->setUpPlugin();
    }
    
    public function assertPreConditions()
    {
        $this->assertTrue($this->acl->has(self::EXHIBIT_RESOURCE));
    }
    
    public function testUnauthenticatedUsersCannotAddOrEditExhibits()
    {
        $this->assertFalse($this->acl->isAllowed(null, self::EXHIBIT_RESOURCE, 'add'));
        $this->assertFalse($this->acl->isAllowed(null, self::EXHIBIT_RESOURCE, 'edit'));
    }
    
    public function testContributorUsersCanAddAndEditOwnExhibits()
    {
        $this->assertTrue($this->acl->isAllowed('contributor', self::EXHIBIT_RESOURCE, 'add'));
        $this->assertFalse($this->acl->isAllowed('contributor', self::EXHIBIT_RESOURCE, 'editAll'));
        $this->assertTrue($this->acl->isAllowed('contributor', self::EXHIBIT_RESOURCE, 'editSelf'));
    }
    
}
