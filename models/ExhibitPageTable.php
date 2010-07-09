<?php
/**
 * ExhibitPage table class
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-20009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/
 
class ExhibitPageTable extends Omeka_Db_Table
{
    protected $_name = 'section_pages';
    
    public function findPrevious($page, $section = null)
    {
        return $this->findNearby($page, 'previous', $section);
    }
    
    public function findNext($page, $section = null)
    {
        return $this->findNearby($page, 'next', $section);
    }
    
    protected function findNearby($page, $position = 'next', $section = null)
    {
        $select = $this->getSelect();
        $select->limit(1);
        
        if (!$section) {
            $section = exhibit_builder_get_current_section();
        }
        
        switch ($position) {
            case 'next':
                $select->where('e.section_id = ?', (int) $section->id);
                $select->where('e.order > ?', (int) $page->order);
                $select->order('e.order ASC');
                break;
                
            case 'previous':
                $select->where('e.section_id = ?', (int) $section->id);
                $select->where('e.order < ?', (int) $page->order);
                $select->order('e.order DESC');
                break;
                
            default:
                throw new Exception( 'Invalid position provided to ExhibitPageTable::findNearby()!' );
                break;
        }

        return $this->fetchObject($select);
    }
    
    public function findBySlug($slug)
    {
        $db = $this->getDb();
        $select = new Omeka_Db_Select;
        $select->from(array('e'=>$db->ExhibitPage), array('e.*'));
        $select->where("e.slug = ?");
        $select->limit(1);
        return $this->fetchObject($select, array($slug));       
    }
}