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
class ExhibitBlockAttachment extends Omeka_Record_AbstractRecord
{
    public $block_id;
    public $item_id;
    public $file_id;
    public $text;
    public $caption;
    public $order;
    
    protected $_related = array(
        'Item' => 'getItem',
        'File' => 'getFile'
    );
    
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
        if (empty($this->block_id) || !is_numeric($this->block_id)) {
            $this->addError('page_id', "Must be associated with an exhibit block.");
        }
        
        if ($this->order === null || !is_numeric($this->order)) {
            $this->addError('order', "Must be ordered within the block.");
        }
        
        if (!empty($this->item_id) and !is_numeric($this->item_id)) {
            $this->addError(null, 'item_id field must be empty or a valid foreign key');
        }
    }
    
    protected function getBlock()
    {
        return $this->getTable('ExhibitPage')->find($this->block_id);
    }

    public function setData($data)
    {
        if (!empty($data['text'])) {
            $this->text = $data['text'];
        } else {
            $this->text = null;
        }

        if (!empty($data['item'])) {
            $this->item_id = (int) $data['item'];
        } else {
            $this->item_id = null;
        }

        if (!empty($data['file'])) {
            $this->file_id = (int) $data['file'];
        } else {
            $this->file_id = null;
        }

        if (!empty($data['caption'])) {
            $this->caption = $data['caption'];
        } else {
            $this->caption = null;
        }
    }
}
