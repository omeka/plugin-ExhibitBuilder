<?php 
/**
 * This encapsulates the permissions check for an exhibit.
 * @todo Find a way to hook this into the plugins
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/

class ExhibitPermissions
{
    /**
     * Right now SQL must be an instance of Omeka_Db_Select b/c that is the only way to add conditional SQL
     *
     * @return Omeka_Db_Select
     **/
    public function __construct(Omeka_Db_Select $sql)
    {
        if (version_compare(OMEKA_VERSION, '2.0-dev', '>=')) {
            $oc = Omeka_Context::getInstance();
            $acl = $oc->getAcl();
            $currentUser = $oc->getCurrentUser();        
            $hasPermission = $acl->isAllowed($currentUser, 'ExhibitBuilder_Exhibits', 'showNotPublic');
        } else {
            $acl = Omeka_Context::getInstance()->getAcl();
            $hasPermission = $acl->checkUserPermission('ExhibitBuilder_Exhibits', 'showNotPublic');            
        }

        if(!$hasPermission)
        {
            $sql->where('e.public = 1');
        }
    }
}