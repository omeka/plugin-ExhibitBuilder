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
//@TODO: update the table name. part of the upgrade script! was section_pages
    protected $_name = 'exhibit_pages';

    public function applySearchFilters($select, $params)
    {
        if(isset($params['parent'])) {
            if(is_numeric($params['parent'])) {
                $this->filterByParentId($select, $params['parent']);
            } else if(get_class($params['parent'] == 'ExhibitPage')) {
                $parent = $params['parent'];
                $this->filterByParentId($select, $parent->id);
            }
        }

        if(isset($params['exhibit'])) {
            if(is_numeric($params['exhibit'])) {
                $this->filterByExhibitId($params['exhibit']);
            } else if(get_class($params['exhibit'] == 'Exhibit')) {
                $exhibit = $params['exhibit'];
                $this->filterByExhibitId($select, $exhibit->id);
            }
        }

        if(isset($params['topOnly'])) {
            $this->filterByTopOnly($select);
        }
    }

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
        $select = $this->getSelect();
        if($page->parent_id) {
            _log($page->parent_id);
            $select->where('e.parent_id = ? ', $page->parent_id);
        } else {
            _log($page->title);
            $select->where('e.exhibit_id = ? ', $page->exhibit_id);
            $select->where('e.parent_id IS NULL');
        }

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
        _log($select);
        return $this->fetchObject($select);
    }

    public function findEndChild($page, $position = 'first')
    {
        $select = $this->getSelect();
        $select->where('e.parent_id = ? ', $page->id);
        $select->where('e.exhibit_id = ? ', $page->exhibit_id);


        $select->limit(1);

        switch ($position) {
            case 'first':
                $select->where('e.order = 1');
                $select->order('e.order ASC');
                break;

            case 'last':
                $select->order('e.order DESC');
                break;

            default:
                throw new Exception( 'Invalid position provided to ExhibitPageTable::findEndChild()!' );
                break;
        }

        return $this->fetchObject($select);
    }

    public function findBySlug($slug)
    {
        $db = $this->getDb();
        $select = new Omeka_Db_Select;
        $select->from(array('e'=>$db->ExhibitPage), array('e.*'));
        $select->where("e.slug = ?", $slug);
        $select->limit(1);
        return $this->fetchObject($select);
    }

    protected function filterByParentId($select, $parentId)
    {
        $select->where('e.parent_id = ?', $parentId);
    }

    protected function filterByExhibitId($select, $exhibitId)
    {
        $select->where('e.exhibit_id = ?', $exhibitId);

    }

    protected function filterByTopOnly($select)
    {
        $select->where('e.parent_id IS NULL');
    }

}