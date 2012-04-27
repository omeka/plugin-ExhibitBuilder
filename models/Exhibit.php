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
    public $added;
    public $modified;
    public $owner_id;

    protected $_related = array('TopPages'=>'getTopPages', 'Tags'=>'getTags');

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
        $sections = $this->TopPages;
        foreach ($sections as $section) {
            $section->delete();
        }
        $this->deleteTaggings();
    }

    public function construct()
    {
        $this->_mixins[] = new Taggable($this);
        $this->_mixins[] = new Ownable($this);
        $this->_mixins[] = new Orderable($this, 'ExhibitPage', 'exhibit_id', 'ExhibitPages');
        $this->_mixins[] = new Sluggable($this, array(
            'slugEmptyErrorMessage' => __('Exhibits must be given a valid slug.'),
            'slugLengthErrorMessage' => __('A slug must be 30 characters or less.'),
            'slugUniqueErrorMessage' => __('Your URL slug is already in use by another exhibit.  Please choose another.')));
    }

    /**
     * Set added and modified timestamps for the exhibit.
     */
    protected function beforeInsert()
    {
        $now = Zend_Date::now()->toString(self::DATE_FORMAT);
        $this->added = $now;
        $this->modified = $now;
    }

    /**
     * Set modified timestamp for the exhibit.
     */
    protected function beforeUpdate()
    {
        $this->modified = Zend_Date::now()->toString(self::DATE_FORMAT);
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
        $this->applyTagString($post['tags']);
        $pages = $post['Pages'];
        $this->savePagesParentOrder(null, $pages);
    }


    /**
     * Updates page parents and orders
     */

    private function savePagesParentOrder($parentId, $pages)
    {
         foreach($pages as $pageId => $pageInfo) {
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
             //@TODO: figure out null for top level, but also work with arbitrary levels of nesting later
             $exhibitPage->parent_id = $parentId; // Change the parent page if necessary
             $exhibitPage->order = $pageOrdersByPageId[$pageId]; // Change the page order
             $exhibitPage->save();
         }
    }

    public function getTopPages()
    {
        $db = $this->getDb();
        return $this->getTable('ExhibitPage')->findBy(array('exhibit_id'=>$this->id, 'topOnly'=>true, 'sort_field'=>'order'));
    }

    public function countTopPages()
    {
        $db = $this->getDb();
        return $this->getTable('ExhibitPage')->count(array('exhibit_id'=>$this->id, 'topOnly'=>true));
    }


    public function getTopPageBySlug($slug)
    {

    }

    public function getFirstTopPage()
    {

    }


    public function getPagesCount($topOnly = true)
    {
        return $this->getTable('ExhibitPage')->count(array('exhibit'=>$this->id, 'topOnly'=>$topOnly));
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
