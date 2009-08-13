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
		$this->_mixins[] = new Sluggable($this, array(
		    'parentIdFieldName'=>'section_id',
            'slugEmptyErrorMessage'=>'Slug must be given for each page of an exhibit.',
            'slugLengthErrorMessage'=>'The slug for your exhibit page must be 30 characters or less.',
            'slugUniqueErrorMessage'=>'Slugs for pages of an exhibit must be unique within a given section of an exhibit.  Please modify the slug so that it is unique.'));
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
	
	public function getPageEntries()
	{
	    return $this->ExhibitPageEntry;
	}
	
	/**
     * Creates and returns a Zend_Search_Lucene_Document for the ExhibitPage
     *
     * @param Zend_Search_Lucene_Document $doc The Zend_Search_Lucene_Document from the subclass of Omeka_Record.
     * @param string $contentFieldValue The value for the content field.
     * @return Zend_Search_Lucene_Document
     **/
    public function createLuceneDocument($doc=null, $contentFieldValue='') 
    {   
        // If no document, lets start a new Zend Lucene Document
        if (!$doc) {
            $doc = new Zend_Search_Lucene_Document(); 
        }  
        
        if ($search = Omeka_Search::getInstance()) {
        
            // Add the fields for public and private       
            $isPublic = $this->getSection()->getExhibit()->public;
            $search->addLuceneField($doc, 'Keyword', Omeka_Search::FIELD_NAME_IS_PUBLIC, $isPublic == '1' ? Omeka_Search::FIELD_VALUE_TRUE : Omeka_Search::FIELD_VALUE_FALSE, true);

            // Add fields for title and text
            $search->addLuceneField($doc, 'UnStored', array('ExhibitPage', 'title'), $this->title);
            $contentFieldValue .= $this->title . "\n";

            // Add the section id of the section that contains the page
            if ($this->section_id) {
                $search->addLuceneField($doc, 'Keyword', array('ExhibitPage','section_id'), $this->section_id, true);                        
            }
            
            // add the exhibit id of the exhibit that contains the page
            if ($this->section_id) {
                $search->addLuceneField($doc, 'Keyword', array('ExhibitPage','exhibit_id'), $this->getSection()->getExhibit()->id, true);                        
            }

            // Add field for page entry texts.
            $entries = $this->getPageEntries();
            $entryTexts = array();
            foreach ($entries as $entry) {
                $entryTexts[] = $entry->text;
            }
            if(count($entryTexts) > 0) {
                $search->addLuceneField($doc, 'UnStored', array('ExhibitPage', 'entry_texts'), $entryTexts);    
                $contentFieldValue .= implode(' ', $entryTexts) . "\n";            
            }    
        }
                
        return parent::createLuceneDocument($doc, $contentFieldValue);
    }
}
?>
