<?php
/**
 * ExhibitSection class
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/

require_once 'ExhibitPage.php';
require_once 'ExhibitSectionTable.php';

class ExhibitSection extends Omeka_Record
{
    
    //Make sure $section_order is processed correctly even when aliased to $order
    public $title;
    public $description;
    public $exhibit_id;
    public $order;
    public $slug;
    
    protected $_related = array('Pages'=>'loadOrderedChildren', 'Exhibit'=>'getExhibit');
        
    public function construct()
    {
        $this->_mixins[] = new Orderable($this, 'ExhibitPage', 'section_id', 'Pages');
        $this->_mixins[] = new Sluggable($this, array(
            'parentIdFieldName'=>'exhibit_id',
            'slugEmptyErrorMessage' => __('A slug must be given for each section of an exhibit.'),
            'slugLengthErrorMessage' => __('A slug must be 30 characters or less.'),
            'slugUniqueErrorMessage' => __('This section slug has already been used in this exhibit.  Please modify the slug so that it is unique.')));
    }

    protected function _validate()
    {
        if (!strlen($this->title)) {
            $this->addError('title', __('Sections of an exhibit must be given a title.'));
        }
        
        if (empty($this->exhibit_id) or !is_numeric($this->exhibit_id)) {
            $this->addError('exhibit_id', 'Exhibit sections must be associated with an exhibit.');
        }
        
        if (empty($this->order) or !is_numeric($this->order)) {
            $this->addError('order', 'Exhibit sections must be properly ordered within an exhibit.');
        }
    }

    protected function _delete()
    {           
        foreach ($this->Pages as $page) {
            $page->delete();
        }
    }
        
    //Deleting a section must re-order the other sections
    protected function afterDelete()
    {
        $exhibit = $this->Exhibit;
        $exhibit->reorderChildren();
    }
    
    public function getExhibit()
    {
        return $this->getTable('Exhibit')->find($this->exhibit_id);
    }
    
    public function previous()
    {
        return $this->getTable('ExhibitSection')->findPrevious($this);
    }
    
    public function next()
    {
        return $this->getTable('ExhibitSection')->findNext($this);
    }
    
    public function getPages()
    {
        $db = $this->getDb();
        $sql = "SELECT p.* FROM $db->ExhibitPage p WHERE p.section_id = ?";
        return $this->getTable('ExhibitPage')->fetchObjects($sql, array($this->id));
    }
    
    public function getPageCount()
    {
        return $this->getChildCount();
    }
    
    public function getPageBySlug($slug)
    {
        $db = $this->getDb();
        $sql = "SELECT p.* FROM $db->ExhibitPage p WHERE p.slug = ? AND p.section_id = ?";

        return $this->getTable('ExhibitPage')->fetchObject($sql, array($slug,$this->id));
    }
    
    public function getPageByOrder($order)
    {
        $db = $this->getDb();
        $sql = "SELECT p.* FROM $db->ExhibitPage p WHERE p.order = ? AND p.section_id = ?";

        return $this->getTable('ExhibitPage')->fetchObject($sql, array($order,$this->id));
    }
    
    public function hasPages()
    {
        $count = $this->getPageCount();
        return $count > 0;
    } 
}
