<?php
require_once 'ExhibitPageEntry.php';
require_once 'ExhibitPageTable.php';

/**
 * Exhibit Page
 * @package: Omeka
 */
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
		if(empty($this->layout)) {
			$this->addError('layout', 'Layout must be provided for each exhibit page.');
		}
		
		if(empty($this->title)) {
			$this->addError('title', 'Pages of an exhibit must be given a title.');
		}
		
		if(empty($this->order)) {
			$this->addError('order', 'Exhibit page must be ordered within its section.');
		}
		
		if(empty($this->section_id)) {
			$this->addError('section_id', 'Exhibit page must be given a section');
		}
	}

	protected function beforeSaveForm(&$post)
	{					
		//Whether or not the exhibit is featured
		$this->featured = (bool) $post['featured'];
		unset($post['featured']);
		
		//Make an exhibit slug if the posted slug is empty
		//This is duplicated exactly in the Section class
		$slugFodder = !empty($post['slug']) ? $post['slug'] : $post['title'];
		$post['slug'] = generate_slug($slugFodder);
	}
	
	/**
	 * Check to see whether the slug field is empty, then provide one
	 *
	 * @return void
	 **/
	protected function beforeValidate()
	{
		if(empty($this->slug)) {
			$this->slug = generate_slug($this->title);
		}
	}

    public function previous()
    {
        return $this->getDb()->getTable('ExhibitPage')->findPrevious($this);
    }
    
    public function next()
    {
        return $this->getDb()->getTable('ExhibitPage')->findNext($this);
    }

	protected function getSection()
	{
		return $this->getTable('ExhibitSection')->find($this->section_id);
	}
	
	protected function _delete()
	{	
	    if($this->ExhibitPageEntry) {
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

			if(!$ip) {
				$ip = new ExhibitPageEntry;
				$ip->page_id = $this->id;
			}
			$text = $post['Text'][$i];
			$item_id = $post['Item'][$i];
			$ip->text = (string) $text;
			$ip->item_id = (int) is_numeric($item_id) ? $item_id : null;
			$ip->order = (int) $i;
			$ip->forceSave();
		}
	}
}
?>
