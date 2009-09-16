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
        $acl = Omeka_Context::getInstance()->getAcl();
        $db = Omeka_Context::getInstance()->getDb();
        
        $has_permission = $acl->checkUserPermission('ExhibitBuilder_Exhibits', 'showNotPublic');
        
        if(!$has_permission)
        {
            $sql->where('e.public = 1');
        }
    }
}