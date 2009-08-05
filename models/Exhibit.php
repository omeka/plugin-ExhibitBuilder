<?php
require_once 'ExhibitSection.php';
require_once 'Tag.php';
require_once 'Taggings.php';
require_once 'Taggable.php';
require_once 'ExhibitTable.php';
require_once 'Orderable.php';
require_once 'ExhibitPermissions.php';
require_once 'Sluggable.php';
/**
 * Exhibit
 * @package: Omeka
 */
class Exhibit extends Omeka_Record
{
	public $title;
	public $description;
	public $credits;
    public $featured = 0;
    public $public = 1;
	
	public $theme;
	public $slug;
	
	protected $_related = array('Sections'=>'loadOrderedChildren', 'Tags'=>'getTags');

	protected function _validate()
	{
		if(empty($this->title)) {
			$this->addError('title', 'Exhibit must be given a title.');
		}
		
		if(strlen($this->title) > 255) {
			$this->addError('title', 'Title for an exhibit must be 255 characters or less.');
		}
		
		if(strlen($this->theme) > 30) {
			$this->addError('theme', 'The name of your theme must be 30 characters or less.');
		}
	}
	
	protected function _delete()
	{
		//Just delete the sections and the cascade will take care of the rest
		
		$sections = $this->Sections;
		
		foreach ($sections as $section) {
			$section->delete();
		}
				
/*
		//This query will delete everything from the exhibits tables when an exhibit is deleted
		//This is semi-duplicated in the Section, ExhibitPage, ExhibitPageEntry models as necessary
		$exhibit_id = $this->id;
		
		$delete = "DELETE items_section_pages, section_pages, sections, exhibits FROM exhibits 
		LEFT JOIN sections ON sections.exhibit_id = exhibits.id
		LEFT JOIN section_pages ON section_pages.section_id = sections.id
		LEFT JOIN items_section_pages ON items_section_pages.page_id = section_pages.id
		WHERE exhibits.id = $exhibit_id;";
		
		$db->exec($delete);
*/	
		
		
	}
	
	public function construct()
	{
		$this->_mixins[] = new Taggable($this);
		$this->_mixins[] = new Relatable($this);
		$this->_mixins[] = new Orderable($this, 'ExhibitSection', 'exhibit_id', 'Sections');	
		$this->_mixins[] = new Sluggable($this, array(
            'slugEmptyErrorMessage'=>'Exhibit must be given a valid slug.',
            'slugLengthErrorMessage'=>'The slug for your exhibit must be 30 characters or less.',
            'slugUniqueErrorMessage'=>'Your URL slug is already in use by another exhibit.  Please choose another.'));	
	}
		
	protected function beforeSaveForm(&$post)
	{					
		//Whether or not the exhibit is featured
		$this->featured = (bool) $post['featured'];
		unset($post['featured']);
	}
	
	protected function afterSaveForm($post)
	{
		//Add the tags after the form has been saved
		$current_user = Omeka_Context::getInstance()->getCurrentUser();		
		$this->applyTagString($post['tags'], $current_user->Entity, true);	
	}
	
	protected function afterSave()
	{
        // update the lucene index with the record
        if ($search = Omeka_Search::getInstance()) {
            $sections = $this->getSections();            
            foreach($sections as $section) {
                $search->updateLuceneByRecord($section);
                $pages = $section->getPages();
                foreach($pages as $page) {
                    $search->updateLuceneByRecord($page);
                }
            }
        }
	}
	
    public function getSections() 
    {
        //return $this->Sections;
        $db = $this->getDb();
        $sql = "SELECT s.* FROM $db->ExhibitSection s WHERE s.exhibit_id = ?";
        return $this->getTable('ExhibitSection')->fetchObjects($sql, array((int) $this->id));
    }
	
	public function getSection($slug)
	{
		$db = $this->getDb();
		$sql = "SELECT s.* FROM $db->ExhibitSection s WHERE s.slug = ? AND s.exhibit_id = ?";

        return $this->getTable('ExhibitSection')->fetchObject($sql, array( strtolower($slug), (int) $this->id));	
	}
	
	public function getFirstSection()
	{
	    $table = $this->getTable('ExhibitSection');
	    $select = $table->getSelect()->where("e.exhibit_id = ?", $this->id)->where("e.`order` = ?", 1)->limit(1);
	    return $table->fetchObject($select);
	}
	
	/**
	 * The number of sections in the exhibit
	 *
	 * @return int
	 **/
	public function getSectionCount()
	{
		return $this->getChildCount();
	}
	
    /**
     * Creates and returns a Zend_Search_Lucene_Document for the SimplePagesPage
     *
     * @param Zend_Search_Lucene_Document $doc The Zend_Search_Lucene_Document from the subclass of Omeka_Record.
     * @return Zend_Search_Lucene_Document
     **/
    public function createLuceneDocument($doc=null) 
    {   
        // If no document, lets create a new Zend Lucene Document
        if (!$doc) {
            $doc = new Zend_Search_Lucene_Document(); 
        }
        
        if ($search = Omeka_Search::getInstance()) {
            
            // adds the fields for public and private       
            $search->addLuceneField($doc, 'Keyword', Omeka_Search::FIELD_NAME_IS_PUBLIC, $this->public == '1' ? Omeka_Search::FIELD_VALUE_TRUE : Omeka_Search::FIELD_VALUE_FALSE, true);         
            
            // adds the fields for public and private       
            $search->addLuceneField($doc, 'Keyword', Omeka_Search::FIELD_NAME_IS_FEATURED, $this->featured == '1' ? Omeka_Search::FIELD_VALUE_TRUE : Omeka_Search::FIELD_VALUE_FALSE, true);   

            // Adds fields for title, description, and slug
            $search->addLuceneField($doc, 'UnStored', array('Exhibit', 'title'), $this->title);
            $search->addLuceneField($doc, 'UnStored', array('Exhibit', 'description'), $this->description);
            $search->addLuceneField($doc, 'UnStored', array('Exhibit', 'slug'), $this->slug);

            //add the tags under the 'tag' field
            $tags = $this->getTags();
            $tagNames = array();
            foreach($tags as $tag) {
                $tagNames[] = $tag->name;
            }

            if (count($tagNames) > 0) {
                $search->addLuceneField($doc, 'UnStored', Omeka_Search::FIELD_NAME_TAG, $tagNames);            
            }
        }
        
        return parent::createLuceneDocument($doc);
    }
}