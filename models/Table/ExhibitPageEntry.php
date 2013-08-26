<?php

class Table_ExhibitPageEntry extends Omeka_Db_Table
{
    public function getSelect()
    {
        $select = parent::getSelect();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $db = $this->getDb();
        $select->join(array('exhibit_pages' => $db->ExhibitPage), 'exhibit_page_entries.page_id = exhibit_pages.id', array());
        $select->join(array('exhibits' => $db->Exhibit), 'exhibits.id = exhibit_pages.exhibit_id', array());
        $permissions = new Omeka_Db_Select_PublicPermissions('ExhibitBuilder_Exhibits');
        $permissions->apply($select, 'exhibits');
        return $select;        
    }
}