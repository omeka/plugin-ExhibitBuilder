<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 **/
 
/**
 * 
 *
 * @package Omeka
 * @author CHNM
 * @copyright Center for History and New Media, 2007-2008
 **/
class ExhibitPageTable extends Omeka_Db_Table
{
    protected $_name = 'section_pages';
    
    public function findPrevious($page)
    {
        return $this->findNearby($page, 'previous');
    }
    
    public function findNext($page)
    {
        return $this->findNearby($page, 'next');
    }
    
    protected function findNearby($page, $position = 'next')
    {
        //This will only pull the title and id for the item
        $select = $this->getSelect();
        
        $select->limit(1);
        
        switch ($position) {
            case 'next':
                $select->where('e.order > ?', (int) $page->order);
                $select->order('e.order ASC');
                break;
                
            case 'previous':
                $select->where('e.order < ?', (int) $page->order);
                $select->order('e.order DESC');
                break;
                
            default:
                throw new Exception( 'Invalid position provided to ExhibitPageTable::findNearby()!' );
                break;
        }
        
        return $this->fetchObject($select);
    }
}