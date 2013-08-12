<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * ExhibitBlockAttachment table class.
 *
 * @package ExhibitBuilder
 */
class Table_ExhibitBlockAttachment extends Omeka_Db_Table
{
    /**
     * Order by the order column by default.
     *
     * @return Omeka_Db_Select
     */
    public function getSelect()
    {
        $select = parent::getSelect();
        $select->order('exhibit_block_attachments.order');
        return $select;
    }

    /**
     * Find the attachments for a block.
     *
     * @param ExhibitPageBlock $block
     * @return ExhibitBlockAttachment[]
     */
    public function findByBlock($block)
    {
        if (!$block->exists()) {
            return array();
        }

        $select = $this->getSelect()
            ->where('exhibit_block_attachments.block_id = ?', $block->id);

        return $this->fetchObjects($select);
    }

    /**
     * Find all the attachments for all blocks on a page.
     *
     * @param ExhibitPage $page
     * @return ExhibitBlockAttachment[]
     */
    public function findByPage($page)
    {
        $select = $this->getSelect()
            ->joinInner(
                array('exhibit_page_blocks' => $this->getDb()->ExhibitPageBlock),
                'exhibit_page_blocks.id = exhibit_block_attachments.block_id',
                array()
                )
            ->where('exhibit_page_blocks.page_id = ?', $page->id)
            ->reset('order')
            ->order('exhibit_page_blocks.order')
            ->order('exhibit_block_attachments.order');

        return $this->fetchObjects($select);
    }
}
