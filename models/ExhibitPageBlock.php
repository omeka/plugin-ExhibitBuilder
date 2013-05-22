<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * ExhibitPageBlock model.
 * 
 * @package ExhibitBuilder
 */
class ExhibitPageBlock extends Omeka_Record_AbstractRecord
{
    public $page_id;
    public $layout;
    public $options;
    public $order;
    
    public function afterSave($args)
    {
        // Build the page's search text.
        $page = $this->getPage();
        $text = "{$page->title} ";
        foreach ($page->ExhibitPageEntry as $entry) {
            $text .= "{$entry->text} {$entry->caption} ";
        }
        Mixin_Search::saveSearchText('ExhibitPage', $page->id, $text, $page->title, $page->getExhibit()->public);
    }
    
    protected function getPage()
    {
        return $this->getTable('ExhibitPage')->find($this->page_id);
    }   
}
