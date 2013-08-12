<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * ExhibitPageBlock table class.
 *
 * @package ExhibitBuilder
 */
class Table_ExhibitPageBlock extends Omeka_Db_Table
{
    /**
     * Sort blocks by the order column by default.
     *
     * @return Omeka_Db_Select
     */
    public function getSelect()
    {
        $select = parent::getSelect();
        $select->order('exhibit_page_blocks.order');
        return $select;
    }

    /**
     * Find all blocks on a page.
     *
     * @param ExhibitPage $page
     * @return ExhibitPageBlock[]
     */
    public function findByPage($page)
    {
        if (!$page->exists()) {
            return array();
        }

        $select = $this->getSelect()
            ->where('exhibit_page_blocks.page_id = ?', $page->id);

        return $this->fetchObjects($select);
    }
}
