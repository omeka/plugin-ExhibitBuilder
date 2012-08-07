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

class ExhibitPage extends Omeka_Record_AbstractRecord
{
    public $id;
    public $parent_id; //@TODO: change this in database, and add to update scripts
    public $exhibit_id; //@TODO: change this in database, and add to update scripts
    public $layout;
    public $slug;
    public $title;
    public $order;

    protected $_related = array('ExhibitPageEntry'=>'loadOrderedChildren');

    public function construct()
    {
        $this->_mixins[] = new Mixin_Order($this, 'ExhibitPageEntry', 'page_id', 'ExhibitPageEntry');
        $this->_mixins[] = new Mixin_Slug($this, array(
            'parentIdFieldName' => 'parent_id',
            'slugEmptyErrorMessage' => __('A slug must be given for each page of an exhibit.'),
            'slugLengthErrorMessage' => __('A slug must be 30 characters or less.'),
            'slugUniqueErrorMessage' => __('This page slug has already been used.  Please modify the slug so that it is unique.')));
    }

    /**
     * In order to validate:
     * 1) must have a layout
     * 2) Must have a title
     * 3) must be properly ordered

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

    }

    /**
     * Check if we're trying to save a page on top of a page with the same order and parent.
     * If so, bump later siblings up in order
     */

    protected function beforeSave()
    {
        $table = $this->getTable();
        if($table->count(array('order'=>$this->order, 'parent'=>$this->parent_id)) != 0) {
            $laterSiblings = $table->findSiblingsAfter($this->parent_id, $this->order - 1 );
            foreach($laterSiblings as $sibling) {
                $sibling->order = $sibling->order + 1;
                $sibling->save();
            }
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

    public function firstChildOrNext()
    {
        if($firstChild = $this->getFirstChildPage()) {
            return $firstChild;
        } else {
            //see if there's a next page on the same level
            $next = $this->next();
            if($next) {
                return $next;
            }
            //no next on same level, so bump up one level and go to next page
            $parent = $this->getParent();
            if($parent) {
                $parentNext = $parent->next();
                return $parentNext;
            }
        }
    }

    public function previousOrParent()
    {
        $previous = $this->previous();
        if($previous) {
            if($previousLastChildPage = $previous->getLastChildPage()) {
                return $previousLastChildPage;
            }
            return $previous;
        } else {
            $parent = $this->getParent();
            if($parent) {
                return $parent;
            }
        }
    }

    public function getParent()
    {
        return $this->getTable()->find($this->parent_id);
    }

    public function getChildPages()
    {
        return $this->getTable()->findBy(array('parent'=>$this->id, 'sort_field'=>'order'));
    }

    public function getFirstChildPage()
    {
        return $this->getTable()->findEndChild($this, 'first');
    }

    public function getLastChildPage()
    {
        return $this->getTable()->findEndChild($this, 'last');
    }

    public function countChildPages()
    {
        return $this->getTable()->count(array('parent'=>$this->id));
    }

    /**
     * Get the ancestors of the page
     *
     * @return array
     */

    public function getAncestors()
    {
        $ancestors = array();
        $page = $this;
        while ($page->parent_id) {
            $page = $page->getParent();
            $ancestors[] = $page;
        }
        $ancestors = array_reverse($ancestors);
        return $ancestors;

    }

    public function getExhibit()
    {
        return $this->getTable('Exhibit')->find($this->exhibit_id);
    }

    protected function _delete()
    {
        if ($this->ExhibitPageEntry) {
            foreach ($this->ExhibitPageEntry as $ip) {
                $ip->delete();
            }
        }

        //bump all child pages up to being children of the parent
        $childPages = $this->getChildPages();
        foreach($childPages as $child) {
            if($this->parent_id) {
                $child->parent_id = $this->parent_id;
            } else {
                $child->parent_id = NULL;
            }
            $child->save();
        }
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


    /**
     * Creates the JSON for use by tree.jquery.js http://mbraak.github.com/jqTree/#tutorial
     * @param bool $returnEncoded
     * @return mixed string JSON or StdClass Object
     */

    public function toTreeJson($returnEncoded = true)
    {
        $node = new StdClass();
        $node->label = $this->title;
        $node->id = $this->id;
        $childPages = $this->getChildPages();
        $node->children = array();
        foreach($childPages as $childPage) {
            $node->children[] = $childPage->toTreeJson(false);
        }
        if($returnEncoded) {
            return json_encode($node);
        } else {
            return $node;
        }



    }

}
