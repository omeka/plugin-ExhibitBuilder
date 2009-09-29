<?php
/**
 * ExhibitSection table class
 * 
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 * @copyright Center for History and New Media, 2007-2009
 **/
 
class ExhibitSectionTable extends Omeka_Db_Table
{
    protected $_name = 'sections';
    
    public function findPrevious($section, $exhibit=null)
    {
        return $this->findNearby($section, 'previous', $exhibit);
    }

    public function findNext($section, $exhibit=null)
    {
        return $this->findNearby($section, 'next', $exhibit);
    }

    protected function findNearby($section, $position = 'next', $exhibit=null)
    {
       $select = $this->getSelect();
       $select->limit(1);

       if (!$exhibit) {
           $exhibit = Zend_Registry::get('exhibit_builder_exhibit');
       }

       switch ($position) {
           case 'next':
               $select->where('e.exhibit_id = ?', (int) $exhibit->id);
               $select->where('e.order > ?', (int) $section->order);
               $select->order('e.order ASC');
               break;

           case 'previous':
               $select->where('e.exhibit_id = ?', (int) $exhibit->id);
               $select->where('e.order < ?', (int) $section->order);
               $select->order('e.order DESC');
               break;

           default:
               throw new Exception( 'Invalid position provided to ExhibitPageTable::findNearby()!' );
               break;
       }

       return $this->fetchObject($select);
    }
}