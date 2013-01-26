<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * ExhibitPage table class.
 *
 * @package ExhibitBuilder
 */
class Table_ExhibitPage extends Omeka_Db_Table
{
    public function applySearchFilters($select, $params)
    {
        if (isset($params['parent'])) {
            if (is_numeric($params['parent'])) {
                $this->filterByParentId($select, $params['parent']);
            } else if ($params['parent'] instanceof ExhibitPage) {
                $parent = $params['parent'];
                $this->filterByParentId($select, $parent->id);
            }
        }
        if (isset($params['exhibit'])) {
            if (is_numeric($params['exhibit'])) {
                $this->filterByExhibitId($select, $params['exhibit']);
            } else if ($params['exhibit'] instanceof Exhibit) {
                $exhibit = $params['exhibit'];
                $this->filterByExhibitId($select, $exhibit->id);
            }
        }
        if (isset($params['order'])) {
            $this->filterByOrder($select, $params['order']);
        }
        if (isset($params['topOnly'])) {
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
        if ($page->parent_id) {
            $select->where('exhibit_pages.parent_id = ? ', $page->parent_id);
        } else {
            $select->where('exhibit_pages.exhibit_id = ? ', $page->exhibit_id);
            $select->where('exhibit_pages.parent_id IS NULL');
        }
        $select->limit(1);
        switch ($position) {
            case 'next':
                $select->where('exhibit_pages.order > ?', (int) $page->order);
                $select->order('exhibit_pages.order ASC');
                break;

            case 'previous':
                $select->where('exhibit_pages.order < ?', (int) $page->order);
                $select->order('exhibit_pages.order DESC');
                break;

            default:
                throw new Exception( 'Invalid position provided to ExhibitPageTable::findNearby()!' );
                break;
        }
        return $this->fetchObject($select);
    }

    public function findEndChild($page, $position = 'first')
    {
        $select = $this->getSelect();
        $select->where('exhibit_pages.parent_id = ? ', $page->id);
        $select->where('exhibit_pages.exhibit_id = ? ', $page->exhibit_id);
        $select->limit(1);
        switch ($position) {
            case 'first':
                $select->order('exhibit_pages.order ASC');
                break;

            case 'last':
                $select->order('exhibit_pages.order DESC');
                break;

            default:
                throw new Exception( 'Invalid position provided to ExhibitPageTable::findEndChild()!' );
                break;
        }
        return $this->fetchObject($select);
    }

    public function findBySlug($slug)
    {
        $select = $this->getSelectForFindBy();
        $select->where("exhibit_pages.slug = ?", $slug);
        $select->limit(1);
        return $this->fetchObject($select);
    }

    public function findSiblingsAfter($parent_id, $order)
    {
        $select = $this->getSelect();
        if ($parent_id) {
            $select->where('exhibit_pages.parent_id = ? ', $parent_id);
        } else {
            $select->where('exhibit_pages.parent_id IS NULL');
        }
        $select->where('exhibit_pages.order > ? ', $order);
        return $this->fetchObjects($select);
    }

    protected function filterByParentId($select, $parentId)
    {
        $select->where('exhibit_pages.parent_id = ?', $parentId);
    }

    protected function filterByExhibitId($select, $exhibitId)
    {
        $select->where('exhibit_pages.exhibit_id = ?', $exhibitId);
    }

    protected function filterByTopOnly($select)
    {
        $select->where('exhibit_pages.parent_id IS NULL');
    }

    protected function filterByOrder($select, $order)
    {
        $select->where('exhibit_pages.order = ? ', $order);
    }
}