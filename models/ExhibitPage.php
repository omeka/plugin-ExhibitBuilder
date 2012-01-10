<?php
/**
 * ExhibitPage class
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-20009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/

require_once 'ExhibitPageEntry.php';
require_once 'ExhibitPageTable.php';

class ExhibitPage extends Omeka_Record
{
	public $section_id;
	public $layout;
	public $slug;
	public $title;
	public $order;
	
	protected $_related = array('ExhibitPageEntry'=>'loadOrderedChildren', 'Section'=>'getSection');
	
	public function construct()
	{
		$this->_mixins[] = new Orderable($this, 'ExhibitPageEntry', 'page_id', 'ExhibitPageEntry');
		$this->_mixins[] = new Sluggable($this, array(
		    'parentIdFieldName' => 'section_id',
            'slugEmptyErrorMessage' => __('A slug must be given for each page of an exhibit.'),
            'slugLengthErrorMessage' => __('A slug must be 30 characters or less.'),
            'slugUniqueErrorMessage' => __('This page slug has already been used in this section.  Please modify the slug so that it is unique.')));
	}
	
	/**
	 * In order to validate:
	 * 1) must have a layout
     * 2) Must have a title
     * 3) must be properly ordered
	 * 4) Must be associated with a section
	 *
	 * @return void
	 **/
	protected function _validate()
	{
		if (empty($this->layout)) {
			$this->addError('layout', __('A layout must be provided for each exhibit page.'));
		}
		
		if (!strlen($this->title)) {
			$this->addError('title', __('Exhibit pages must be given a title.'));
		}
		
		if (empty($this->order) or !is_numeric($this->order)) {
			$this->addError('order', 'Exhibit page must be ordered within its section.');
		}
		
		if (empty($this->section_id) or !is_numeric($this->section_id)) {
			$this->addError('section_id', 'Exhibit page must be given a section');
		}
	}

	protected function beforeSaveForm($post)
	{					
		//Whether or not the exhibit is featured
		$this->featured = (bool) $post['featured'];	
	}
	
	protected function setFromPost($post)
	{
	    unset($post['featured']);
		return parent::setFromPost($post);
	}

    public function previous()
    {
        return $this->getDb()->getTable('ExhibitPage')->findPrevious($this);
    }
    
    public function next()
    {
        return $this->getDb()->getTable('ExhibitPage')->findNext($this);
    }

	public function getSection()
	{
		return $this->getTable('ExhibitSection')->find($this->section_id);
	}
	
	protected function _delete()
	{	
	    if ($this->ExhibitPageEntry) {
    		foreach ($this->ExhibitPageEntry as $ip) {
    			$ip->delete();
    		}	        
	    }
	}
		
	protected function afterDelete()
	{
		$section = $this->Section;
		$section->reorderChildren();		
	}
		
	/**
	 * Page Form POST will look like:
	 *
	 * Text[1] = 'Text inserted <a href="foobar.com">With HTML</a>'
	 * Item[2] = 35		(integer ID)
	 * Item[3] = 64
	 * Text[3] = 'This is commentary for the Item with ID # 64' 
	 * 
	 * @return void
	 **/
	public function afterSaveForm($post)
	{			
		$textCount = count($post['Text']);
		$itemCount = count($post['Item']);
		$highCount = ($textCount > $itemCount) ? $textCount : $itemCount;	

		$entries = $this->ExhibitPageEntry;
		for ($i=1; $i <= $highCount; $i++) { 
			$ip = $entries[$i];

			if (!$ip) {
				$ip = new ExhibitPageEntry;
				$ip->page_id = $this->id;
			}
			$text = $post['Text'][$i];
			$item_id = $post['Item'][$i];
			$caption = $post['Caption'][$i];
			$ip->text = (string) $text;
			$ip->caption = (string) $caption;
			$ip->item_id = (int) is_numeric($item_id) ? $item_id : null;
			$ip->order = (int) $i;
			$ip->forceSave();
		}
	}
	
	public function getPageEntries()
	{
	    return $this->ExhibitPageEntry;
	}
}
