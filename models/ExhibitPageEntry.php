<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * ExhibitPageEntry model.
 * 
 * @package ExhibitBuilder
 */
class ExhibitPageEntry extends Omeka_Record_AbstractRecord
{
    public $item_id;
    public $page_id;
    public $file_id;
    public $text;
    public $caption;
    public $order;
    
    protected $_related = array(
        'Item' => 'getItem',
        'File' => 'getFile'
    );
    
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
    
    protected function getItem()
    {
        if ($this->item_id) {
            return $this->getTable('Item')->find($this->item_id);
        }
    }

    protected function getFile()
    {
        if ($this->file_id) {
            return $this->getTable('File')->find($this->file_id);
        } else {
            return null;
        }
    }
    
    protected function _validate()
    {
        if (empty($this->page_id)) {
            $this->addError('page_id', "Must be associated with a page of an exhibit.");
        }
        
        if (empty($this->order)) {
            $this->addError('order', "Must be ordered on the exhibit page.");
        }
        
        if (!is_numeric($this->page_id) or !is_numeric($this->order)) {
            $this->addError(null, 'page_id and order fields must all have proper numeric input');
        }
        
        if (!empty($this->item_id) and !is_numeric($this->item_id)) {
            $this->addError(null, 'item_id field must be empty or a valid foreign key');
        }
    }
    
    protected function getPage()
    {
        return $this->getTable('ExhibitPage')->find($this->page_id);
    }   
}
