<?php
/**
 * Exhibit class
 * 
 * @version $Id$
 * @copyright Center for History and New Media, 2007-20009
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 * @author CHNM
 **/

require_once 'ExhibitSection.php';
require_once 'Tag.php';
require_once 'Taggings.php';
require_once 'Taggable.php';
require_once 'ExhibitTable.php';
require_once 'Orderable.php';
require_once 'ExhibitPermissions.php';
require_once 'Sluggable.php';

class Exhibit extends Omeka_Record
{
	public $title;
	public $description;
	public $credits;
    public $featured = 0;
    public $public = 1;
	
	public $theme;
	public $theme_options;
	public $slug;
	
	protected $_related = array('Sections'=>'loadOrderedChildren', 'Tags'=>'getTags');

	protected function _validate()
	{
		if (!strlen((string)$this->title)) {
			$this->addError('title', __('An exhibit must be given a title.'));
		}
		
		if (strlen((string)$this->title) > 255) {
			$this->addError('title', __('The title for an exhibit must be 255 characters or less.'));
		}
		
		if (strlen((string)$this->theme) > 30) {
			$this->addError('theme', __('The name of your theme must be 30 characters or less.'));
		}
	}
	
	protected function _delete()
	{
		//Just delete the sections and the cascade will take care of the rest
		$sections = $this->Sections;
		foreach ($sections as $section) {
			$section->delete();
		}
		$this->deleteTaggings();
	}
	
	public function construct()
	{
		$this->_mixins[] = new Taggable($this);
		$this->_mixins[] = new Relatable($this);
		$this->_mixins[] = new Orderable($this, 'ExhibitSection', 'exhibit_id', 'Sections');
		$this->_mixins[] = new Sluggable($this, array(
            'slugEmptyErrorMessage' => __('Exhibits must be given a valid slug.'),
            'slugLengthErrorMessage' => __('A slug must be 30 characters or less.'),
            'slugUniqueErrorMessage' => __('Your URL slug is already in use by another exhibit.  Please choose another.')));	
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
	
	protected function afterSaveForm($post)
	{
		//Add the tags after the form has been saved
		$current_user = Omeka_Context::getInstance()->getCurrentUser();		
		$this->applyTagString($post['tags'], $current_user->Entity, true);
		
		// Save the new page orderings for each section
		$pagesBySection = $post['Pages'];
        foreach($pagesBySection as $sectionId => $pagesInfos) {
             
             // Rewrite the page order data, so that pages are ordered 1,2,3, ... etc.
             $rawPageOrdersByPageId = array();
             foreach($pagesInfos as $pageId => $pageInfo) {
                 $rawPageOrdersByPageId[$pageId] = $pageInfo['order'];
             }
             asort($rawPageOrdersByPageId, SORT_NUMERIC);
             $pageOrder = 0;
             $pageOrdersByPageId = array();
             foreach($rawPageOrdersByPageId as $pageId => $rawPageOrder) {
                 $pageOrder++;
                 $pageOrdersByPageId[$pageId] = $pageOrder;
             }
                          
             // Save the new page orders
             foreach($pageOrdersByPageId as $pageId => $pageOrder) {
                 $exhibitPage = $this->getDb()->getTable('ExhibitPage')->find($pageId);                 
                 $exhibitPage->section_id = $sectionId; // Change the section if necessary
                 $exhibitPage->order = $pageOrder; // Change the page order
                 $exhibitPage->save();
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
	
	public function getSectionBySlug($slug)
	{
		$db = $this->getDb();
		$sql = "SELECT s.* FROM $db->ExhibitSection s WHERE s.slug = ? AND s.exhibit_id = ?";

        return $this->getTable('ExhibitSection')->fetchObject($sql, array(strtolower($slug), (int) $this->id));	
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
	 * Determine whether an exhibit uses a particular item on any of its pages.
	 * 
	 * @param Item $item
	 * @return boolean
	 */
	public function hasItem(Item $item)
	{
	    if (!$item->exists()) {
	       throw new InvalidArgumentException("Item does not exist (is not persisted).");
	    }
	    if (!$this->exists()) {
	       throw new RuntimeException("Cannot call hasItem() on a new (non-persisted) exhibit.");
	    }
	    return $this->getTable()->exhibitHasItem($this->id, $item->id);
	}
	
	public function setThemeOptions($themeOptions, $themeName = null)
	{
	    if ($themeName === null) {
	        $themeName = $this->theme;
	    }
	    if ($themeName !== null && $themeName != '') {
    	    $themeOptionsArray = unserialize($this->theme_options);
    	    $themeOptionsArray[$themeName] = $themeOptions;
        }
	    
	    $this->theme_options = serialize($themeOptionsArray);
	}
	
	public function getThemeOptions($themeName = null)
	{
	    if ($themeName === null) {
	        $themeName = $this->theme;
	    }
	    
	    $themeName = (string)$themeName;
	    if ($themeName == '' || empty($this->theme_options)) {
	        return array();
	    }
	    
	    $themeOptionsArray = unserialize($this->theme_options);
	    return $themeOptionsArray[$themeName];
	}
}
