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
        if(isset($params['parent'])) {
            if(is_numeric($params['parent'])) {
                $this->filterByParentId($select, $params['parent']);
            } else if($params['parent'] instanceof ExhibitPage) {
                $parent = $params['parent'];
                $this->filterByParentId($select, $parent->id);
            }
        }

        if(isset($params['exhibit'])) {
            if(is_numeric($params['exhibit'])) {
                $this->filterByExhibitId($select, $params['exhibit']);
            } else if($params['exhibit'] instanceof Exhibit) {
                $exhibit = $params['exhibit'];
                $this->filterByExhibitId($select, $exhibit->id);
            }
        }

        if(isset($params['order'])) {
            $this->filterByOrder($select, $params['order']);
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
        if($parent_id) {
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
        $select->where('exhibit_pages.parent_id IS NULL OR exhibit_pages.parent_id = 0');
    }

    protected function filterByOrder($select, $order)
    {
        $select->where('exhibit_pages.order = ? ', $order);
    }


    /**
     *  Returns an array of pages that could be a parent for the current page.
     *  This is used to populate a dropdown for selecting a new parent for the current page.
     *  In particluar, a page cannot be a parent of itself, and a page cannot have one of its descendents as a parent.
     *
     * @param integer $pageId The id of the page whose potential parent pages are returned.
     * @return array The potential parent pages.
     */
    public function findPotentialParentPages($pageId)
    {
        // create a page lookup table for all of the pages
        $idToPageLookup = $this->_createIdToPageLookup();

        // find all of the page's descendants
        $descendantPages = $this->findChildrenPages($pageId, true, $idToPageLookup);

        // filter out all of the descendant pages from the lookup table
        $allPages = array_values($idToPageLookup);
        foreach($descendantPages as $descendantPage) {
            unset($idToPageLookup[$descendantPage->id]);
        }

        // filter out the page itself from the lookup table
        unset($idToPageLookup[$pageId]);

        // return the values of the filtered page lookup table
        return array_values($idToPageLookup);
    }

    /**
     * Retrieve child pages from list of pages matching page ID.
     *
     * Matches against the pages parameter against the page ID. Also matches all
     * children for the same to retrieve all children of a page.
     *
     * @param int $parentId The id of the original parent
     * @param array $pages The array of all pages
     * @return array
     */
    public function findChildrenPages($parentId, $includeAllDescendants=false, $idToPageLookup = null, $parentToChildrenLookup = null)
    {
        if ((string)$parentId == '') {
            return array();
        }

        $descendantPages = array();

        if ($includeAllDescendants) {
            // create the id to page lookup if required
            if (!$idToPageLookup) {
                $idToPageLookup = $this->_createIdToPageLookup();
            }

            // create the parent to children lookup if required
            if (!$parentToChildrenLookup) {
                $parentToChildrenLookup = $this->_createParentToChildrenLookup($idToPageLookup);
            }

            // get all of the descendant pages of the parent page
            $childrenPages = $parentToChildrenLookup[$parentId];
            $descendantPages = array_merge($descendantPages, $childrenPages);
            foreach ( $childrenPages as $childPage ) {
                if ( $allGrandChildren = $this->findChildrenPages($childPage->id, true, $idToPageLookup, $parentToChildrenLookup) ) {
                    $descendantPages = array_merge($descendantPages, $allGrandChildren);
                }
            }
        } else {
            // only include the immediate children
            $descendantPages = $this->findBy(array('page_parent_id'=>$parentId, 'sort'=>'order'));
        }

        return $descendantPages;
    }

    protected function _createParentToChildrenLookup($idToPageLookup)
    {
        // create an associative array that maps parent ids to an array of any children's ids
        $parentToChildrenLookup = array();
        $allPages = array_values($idToPageLookup);

        // initialize the children array for all potential parents
        foreach($allPages as $page) {
            $parentToChildrenLookup[$page->id] = array();
        }

        // add each child to his parent's array
        foreach($allPages as $page) {
            $parentToChildrenLookup[$page->parent_id][] = $page;
        }

        return $parentToChildrenLookup;
    }

    protected function _createIdToPageLookup()
    {
        // get all of the pages
        // this should eventually be just the id/parent_id pairs for all pages
        $allPages = $this->findAll();

        // create the page lookup
        $idToPageLookup = array();
        foreach($allPages as $page) {
            $idToPageLookup[$page->id] = $page;
        }

        return $idToPageLookup;
    }

}
