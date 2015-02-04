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
    /**
     * Get the basic select query for exhibit pages.
     *
     * @return Omeka_Db_Select
     */
    public function getSelect()
    {
        $select = parent::getSelect();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $db = $this->getDb();
        $select->join(array('exhibits' => $db->Exhibit), 'exhibits.id = exhibit_pages.exhibit_id', array());
        $permissions = new Omeka_Db_Select_PublicPermissions('ExhibitBuilder_Exhibits');
        $permissions->apply($select, 'exhibits');
        return $select;
    }

    /**
     * Apply filters for searching pages to an SQL select object.
     *
     * Valid filters are "parent", "exhibit", "order", and "topOnly".
     *
     * @param Omeka_Db_Select $select
     * @param array $params
     */
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

        if(isset($params['item'])) {
            $this->filterByItem($select, $params['item']);
        }
    }

    /**
     * Find the previous page.
     *
     * @param ExhibitPage $page
     * @return ExhibitPage
     */
    public function findPrevious($page)
    {
        return $this->findNearby($page, 'previous');
    }

    /**
     * Find the next page.
     *
     * @param ExhibitPage $page
     * @return ExhibitPage
     */
    public function findNext($page)
    {
        return $this->findNearby($page, 'next');
    }

    /**
     * Find a nearby page.
     *
     * @param ExhibitPage $page
     * @param string $position
     * @return ExhibitPage
     */
    protected function findNearby($page, $position = 'next')
    {
        $select = $this->getSelect();

        $select->where('exhibit_pages.exhibit_id = ? ', $page->exhibit_id);

        if($page->parent_id) {
            $select->where('exhibit_pages.parent_id = ? ', $page->parent_id);
        } else {
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

    /**
     * Find either the first or last child page of this page.
     *
     * @param ExhibitPage $page
     * @param string $position
     * @return ExhibitPage
     */
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

    /**
     * Find a page in an exhibit by slug.
     *
     * @param string $slug Slug of the page to find
     * @param Exhibit|integer $exhibit Exhibit (or ID) to search within
     * @param ExhibitPage|integer $parent Exhibit page (or ID) to search for
     *  pages under. If omitted, only top-level pages are found.
     * @return ExhibitPage|null
     */
    public function findBySlug($slug, $exhibit, $parent = null)
    {
        if ($exhibit instanceof Exhibit) {
            $exhibit = $exhibit->id;
        }

        if ($parent instanceof ExhibitPage) {
            $parent = $parent->id;
        }

        $select = $this->getSelect();
        $select->where('exhibit_pages.exhibit_id = ?', $exhibit);
        $select->where('exhibit_pages.slug = ?', $slug);
        if ($parent) {
            $select->where('exhibit_pages.parent_id = ?', $parent);
        } else {
            $select->where('exhibit_pages.parent_id IS NULL');
        }
        $select->limit(1);
        return $this->fetchObject($select);
    }

    /**
     * Filter a select by parent page ID.
     *
     * @param Zend_Db_Select $select Select object to filter
     * @param integer $parentId Parent page ID
     */
    protected function filterByParentId($select, $parentId)
    {
        $select->where('exhibit_pages.parent_id = ?', $parentId);
    }

    /**
     * Filter a select by parent page ID.
     *
     * @param Zend_Db_Select $select Select object to filter
     * @param integer $exhibitId Exhibit ID
     */
    protected function filterByExhibitId($select, $exhibitId)
    {
        $select->where('exhibit_pages.exhibit_id = ?', $exhibitId);
    }

    /**
     * Filter a select to find only top-level pages.
     *
     * @param Zend_Db_Select $select Select object to filter
     */
    protected function filterByTopOnly($select)
    {
        $select->where('exhibit_pages.parent_id IS NULL');
    }

    /**
     * Filter a select by order.
     *
     * @param Zend_Db_Select $select Select object to filter
     * @param integer $order Order to filter by
     */
    protected function filterByOrder($select, $order)
    {
        $select->where('exhibit_pages.order = ? ', $order);
    }

    /**
     * Filter by an item used on the exhibit page
     * @param Zend_Db_Select $select Select object to filter
     * @param integer $item_id Item id to filter by
     */
    protected function filterByItem($select, $item_id)
    {
        $db = $this->getDb();
        $select->join(
                array('exhibit_page_blocks' => $db->ExhibitPageBlocks),
                'exhibit_pages.id = exhibit_page_blocks.page_id',
                array()
        );
        $select->join(
                array('exhibit_block_attachments' => $db->ExhibitBlockAttachments),
                'exhibit_page_blocks.id = exhibit_block_attachments.block_id',
                array()
        );
        $select->where('exhibit_block_attachments.item_id = ?', $item_id);
    }
}
