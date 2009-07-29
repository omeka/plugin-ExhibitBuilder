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
class ExhibitSectionTable extends Omeka_Db_Table
{
    protected $_name = 'sections';
    
    public function findPrevious($section)
       {
           return $this->findNearby($section, 'previous');
       }

       public function findNext($section)
       {
           return $this->findNearby($section, 'next');
       }

       protected function findNearby($section, $position = 'next')
       {
           $select = $this->getSelect();
           $select->limit(1);

           $exhibit = Zend_Registry::get('exhibit');

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