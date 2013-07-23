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
    public function findAllForPage($page)
    {
        $select = $this->getSelect()
            ->joinInner(
                array('exhibit_page_blocks' => $this->getDb()->ExhibitPageBlock),
                'exhibit_page_blocks.id = exhibit_block_attachments.block_id',
                array()
                )
            ->where('exhibit_page_blocks.page_id = ?', $page->id)
            ->order('exhibit_page_blocks.order')
            ->order('exhibit_block_attachments.order');

        return $this->fetchObjects($select);
    }
}
